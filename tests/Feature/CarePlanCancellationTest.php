<?php

namespace Tests\Feature;

use App\Actions\CarePlans\CancelCarePlan;
use App\Actions\CarePlans\CorrectCarePlan;
use App\Enums\UserRole;
use App\Filament\Manager\Resources\CarePlans\Pages\ListCarePlans;
use App\Models\CarePlan;
use App\Models\Plot;
use App\Models\Program;
use App\Models\Site;
use App\Models\Tree;
use App\Models\TreeSpecies;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class CarePlanCancellationTest extends TestCase
{
    use RefreshDatabase;

    private function manager(): User
    {
        return User::factory()->create([
            'role' => UserRole::Manager,
            'is_active' => true,
        ]);
    }

    private function fieldStaff(): User
    {
        return User::factory()->create([
            'role' => UserRole::FieldStaff,
            'is_active' => true,
        ]);
    }

    private function citizen(): User
    {
        return User::factory()->create([
            'role' => UserRole::Citizen,
            'is_active' => true,
        ]);
    }

    private function createTree(string $code = 'PH-C-001'): Tree
    {
        $program = Program::query()->firstOrCreate(
            ['name' => 'Program Hijauku'],
            [
                'objective' => 'Konservasi lingkungan',
                'starts_on' => '2026-01-01',
                'ends_on' => '2026-12-31',
                'status' => 'active',
            ]
        );

        $site = Site::query()->create([
            'program_id' => $program->id,
            'name' => 'Taman Kota '.rand(1, 999),
            'address' => 'Jl. Konservasi No. '.rand(1, 999),
        ]);

        $plot = Plot::query()->create([
            'site_id' => $site->id,
            'code' => 'PL-'.rand(100, 999),
            'capacity' => 50,
        ]);

        $species = TreeSpecies::query()->firstOrCreate(
            ['name' => 'Trembesi'],
            ['care_notes' => 'Penyiraman teratur']
        );

        return Tree::query()->create([
            'species_id' => $species->id,
            'plot_id' => $plot->id,
            'code' => $code,
            'planted_on' => '2026-02-01',
            'initial_condition' => 'good',
        ]);
    }

    private function publishedPlan(Tree $tree): CarePlan
    {
        return CarePlan::query()->create([
            'tree_id' => $tree->id,
            'planned_on' => now()->addDays(5)->toDateString(),
            'action' => 'Penyiraman intensif terjadwal',
            'status' => 'published',
            'priority' => 'normal',
        ]);
    }

    private function draftPlan(Tree $tree): CarePlan
    {
        return CarePlan::query()->create([
            'tree_id' => $tree->id,
            'planned_on' => now()->addDays(5)->toDateString(),
            'action' => 'Pemangkasan dahan rencana awal',
            'status' => 'draft',
            'priority' => 'normal',
        ]);
    }

    private function openManagerPanelAs(User $manager): void
    {
        $this->actingAs($manager);
        Filament::setCurrentPanel('app');
    }

    // --- PEMBATALAN ---

    public function test_manager_can_cancel_published_care_plan_with_reason(): void
    {
        $manager = $this->manager();
        $tree = $this->createTree('PH-CAN-01');
        $plan = $this->publishedPlan($tree);

        app(CancelCarePlan::class)->handle($manager, $plan, [
            'reason' => 'Perawatan sudah dilakukan secara spontan oleh warga setempat.',
        ]);

        $plan->refresh();

        $this->assertSame('cancelled', $plan->status);

        $this->assertDatabaseHas('activity_logs', [
            'actor_id' => $manager->getKey(),
            'action' => 'care_plan.cancelled',
            'subject_type' => CarePlan::class,
            'subject_id' => $plan->getKey(),
            'from_status' => 'published',
            'to_status' => 'cancelled',
        ]);

        $log = $plan->activityLogs()->where('action', 'care_plan.cancelled')->first();
        $this->assertNotNull($log);
        $this->assertSame(
            'Perawatan sudah dilakukan secara spontan oleh warga setempat.',
            $log->metadata['reason']
        );
    }

    public function test_manager_can_cancel_draft_care_plan_with_reason(): void
    {
        $manager = $this->manager();
        $tree = $this->createTree('PH-CAN-02');
        $plan = $this->draftPlan($tree);

        app(CancelCarePlan::class)->handle($manager, $plan, [
            'reason' => 'Draf ini tidak relevan lagi karena jadwal berubah.',
        ]);

        $plan->refresh();

        $this->assertSame('cancelled', $plan->status);

        $log = $plan->activityLogs()->where('action', 'care_plan.cancelled')->first();
        $this->assertNotNull($log);
        $this->assertSame('draft', $log->from_status);
        $this->assertSame('cancelled', $log->to_status);
    }

    public function test_cancellation_requires_reason(): void
    {
        $manager = $this->manager();
        $tree = $this->createTree('PH-CAN-03');
        $plan = $this->publishedPlan($tree);

        // Alasan kosong
        try {
            app(CancelCarePlan::class)->handle($manager, $plan, ['reason' => '']);
            $this->fail('Harus gagal jika alasan kosong.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('reason', $e->errors());
        }

        // Alasan hanya spasi
        try {
            app(CancelCarePlan::class)->handle($manager, $plan, ['reason' => '   ']);
            $this->fail('Harus gagal jika alasan hanya spasi.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('reason', $e->errors());
        }

        // Rencana tidak berubah
        $plan->refresh();
        $this->assertSame('published', $plan->status);
    }

    public function test_cancellation_reason_must_meet_minimum_length(): void
    {
        $manager = $this->manager();
        $tree = $this->createTree('PH-CAN-04');
        $plan = $this->publishedPlan($tree);

        try {
            app(CancelCarePlan::class)->handle($manager, $plan, ['reason' => 'ab']);
            $this->fail('Harus gagal jika alasan kurang dari 3 karakter.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('reason', $e->errors());
        }
    }

    public function test_cannot_cancel_already_cancelled_plan(): void
    {
        $manager = $this->manager();
        $tree = $this->createTree('PH-CAN-05');
        $plan = CarePlan::query()->create([
            'tree_id' => $tree->id,
            'planned_on' => now()->addDays(5)->toDateString(),
            'action' => 'Rencana yang sudah dibatalkan',
            'status' => 'cancelled',
            'priority' => 'normal',
        ]);

        try {
            app(CancelCarePlan::class)->handle($manager, $plan, [
                'reason' => 'Mencoba membatalkan yang sudah batal.',
            ]);
            $this->fail('Harus gagal jika rencana sudah berstatus cancelled.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('status', $e->errors());
        }
    }

    public function test_cannot_cancel_completed_plan(): void
    {
        $manager = $this->manager();
        $tree = $this->createTree('PH-CAN-06');
        $plan = CarePlan::query()->create([
            'tree_id' => $tree->id,
            'planned_on' => now()->subDays(2)->toDateString(),
            'action' => 'Rencana yang sudah selesai',
            'status' => 'completed',
            'priority' => 'normal',
        ]);

        try {
            app(CancelCarePlan::class)->handle($manager, $plan, [
                'reason' => 'Mencoba membatalkan rencana selesai.',
            ]);
            $this->fail('Harus gagal jika rencana berstatus completed.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('status', $e->errors());
        }
    }

    public function test_non_manager_cannot_cancel_care_plan(): void
    {
        $fieldStaff = $this->fieldStaff();
        $tree = $this->createTree('PH-CAN-07');
        $plan = $this->publishedPlan($tree);

        $this->expectException(AuthorizationException::class);

        app(CancelCarePlan::class)->handle($fieldStaff, $plan, [
            'reason' => 'Staf lapangan mencoba membatalkan.',
        ]);
    }

    public function test_citizen_cannot_cancel_care_plan(): void
    {
        $citizen = $this->citizen();
        $tree = $this->createTree('PH-CAN-08');
        $plan = $this->publishedPlan($tree);

        $this->expectException(AuthorizationException::class);

        app(CancelCarePlan::class)->handle($citizen, $plan, [
            'reason' => 'Warga mencoba membatalkan rencana.',
        ]);
    }

    public function test_cancelled_plan_is_not_deleted_from_database(): void
    {
        $manager = $this->manager();
        $tree = $this->createTree('PH-CAN-09');
        $plan = $this->publishedPlan($tree);
        $planId = $plan->getKey();

        app(CancelCarePlan::class)->handle($manager, $plan, [
            'reason' => 'Pembatalan terencana setelah evaluasi manajer lapangan.',
        ]);

        $this->assertDatabaseHas('care_plans', [
            'id' => $planId,
            'status' => 'cancelled',
        ]);
    }

    // --- KOREKSI ---

    public function test_manager_can_correct_published_care_plan_and_audit_is_recorded(): void
    {
        $manager = $this->manager();
        $tree = $this->createTree('PH-COR-01');
        $plan = $this->publishedPlan($tree);

        app(CorrectCarePlan::class)->handle($manager, $plan, [
            'action' => 'Penyiraman ditambah pupuk organik cair.',
            'planned_on' => now()->addDays(7)->toDateString(),
        ]);

        $plan->refresh();

        $this->assertSame('Penyiraman ditambah pupuk organik cair.', $plan->action);
        $this->assertSame('published', $plan->status, 'Status tidak boleh berubah setelah koreksi.');

        $log = $plan->activityLogs()->where('action', 'care_plan.corrected')->first();
        $this->assertNotNull($log);
        $this->assertArrayHasKey('changes', $log->metadata);
        $this->assertArrayHasKey('action', $log->metadata['changes']);
        $this->assertSame(
            'Penyiraman intensif terjadwal',
            $log->metadata['changes']['action']['from']
        );
        $this->assertSame(
            'Penyiraman ditambah pupuk organik cair.',
            $log->metadata['changes']['action']['to']
        );
    }

    public function test_manager_can_correct_draft_care_plan_and_audit_is_recorded(): void
    {
        $manager = $this->manager();
        $tree = $this->createTree('PH-COR-02');
        $plan = $this->draftPlan($tree);

        app(CorrectCarePlan::class)->handle($manager, $plan, [
            'priority' => 'high',
        ]);

        $plan->refresh();

        $this->assertSame('high', $plan->priority);

        $log = $plan->activityLogs()->where('action', 'care_plan.corrected')->first();
        $this->assertNotNull($log);
        $this->assertArrayHasKey('priority', $log->metadata['changes']);
    }

    public function test_cannot_correct_cancelled_care_plan(): void
    {
        $manager = $this->manager();
        $tree = $this->createTree('PH-COR-03');
        $plan = CarePlan::query()->create([
            'tree_id' => $tree->id,
            'planned_on' => now()->addDays(5)->toDateString(),
            'action' => 'Rencana dibatalkan',
            'status' => 'cancelled',
            'priority' => 'normal',
        ]);

        try {
            app(CorrectCarePlan::class)->handle($manager, $plan, [
                'action' => 'Mencoba mengedit rencana yang dibatalkan.',
            ]);
            $this->fail('Harus gagal jika rencana sudah dibatalkan.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('status', $e->errors());
        }
    }

    public function test_cannot_correct_completed_care_plan(): void
    {
        $manager = $this->manager();
        $tree = $this->createTree('PH-COR-04');
        $plan = CarePlan::query()->create([
            'tree_id' => $tree->id,
            'planned_on' => now()->subDays(2)->toDateString(),
            'action' => 'Rencana selesai',
            'status' => 'completed',
            'priority' => 'normal',
        ]);

        try {
            app(CorrectCarePlan::class)->handle($manager, $plan, [
                'action' => 'Mencoba mengedit rencana selesai.',
            ]);
            $this->fail('Harus gagal jika rencana sudah selesai.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('status', $e->errors());
        }
    }

    public function test_no_audit_log_created_when_nothing_changes(): void
    {
        $manager = $this->manager();
        $tree = $this->createTree('PH-COR-05');
        $plan = $this->publishedPlan($tree);

        // Data yang dikirim identik dengan nilai yang ada
        app(CorrectCarePlan::class)->handle($manager, $plan, [
            'action' => 'Penyiraman intensif terjadwal',
        ]);

        $log = $plan->activityLogs()->where('action', 'care_plan.corrected')->first();
        $this->assertNull($log, 'Audit log tidak boleh dibuat jika tidak ada perubahan.');
    }

    // --- AKSI TABEL (UI via Livewire) ---

    public function test_cancel_action_visible_for_draft_and_published_plans(): void
    {
        $manager = $this->manager();
        $this->openManagerPanelAs($manager);
        $tree = $this->createTree('PH-UI-01');

        $published = $this->publishedPlan($tree);
        $draft = $this->draftPlan($tree);

        Livewire::test(ListCarePlans::class)
            ->assertTableActionVisible('cancel', $published)
            ->assertTableActionVisible('cancel', $draft);
    }

    public function test_cancel_action_hidden_for_cancelled_and_completed_plans(): void
    {
        $manager = $this->manager();
        $this->openManagerPanelAs($manager);
        $tree = $this->createTree('PH-UI-02');

        $cancelled = CarePlan::query()->create([
            'tree_id' => $tree->id,
            'planned_on' => now()->addDays(3)->toDateString(),
            'action' => 'Rencana yang sudah batal',
            'status' => 'cancelled',
            'priority' => 'normal',
        ]);

        $completed = CarePlan::query()->create([
            'tree_id' => $tree->id,
            'planned_on' => now()->subDays(2)->toDateString(),
            'action' => 'Rencana yang sudah selesai',
            'status' => 'completed',
            'priority' => 'normal',
        ]);

        Livewire::test(ListCarePlans::class)
            ->assertTableActionHidden('cancel', $cancelled)
            ->assertTableActionHidden('cancel', $completed);
    }

    public function test_cancel_action_via_table_changes_status_and_records_audit_log(): void
    {
        $manager = $this->manager();
        $this->openManagerPanelAs($manager);
        $tree = $this->createTree('PH-UI-03');
        $plan = $this->publishedPlan($tree);

        Livewire::test(ListCarePlans::class)
            ->callTableAction('cancel', $plan, data: [
                'reason' => 'Rencana tidak relevan setelah evaluasi lapangan bulanan.',
            ]);

        $plan->refresh();

        $this->assertSame('cancelled', $plan->status);

        $this->assertDatabaseHas('activity_logs', [
            'actor_id' => $manager->getKey(),
            'action' => 'care_plan.cancelled',
            'subject_type' => CarePlan::class,
            'subject_id' => $plan->getKey(),
        ]);
    }

    public function test_cancelled_tab_shows_only_cancelled_plans(): void
    {
        $manager = $this->manager();
        $this->openManagerPanelAs($manager);
        $tree = $this->createTree('PH-TAB-01');

        $published = $this->publishedPlan($tree);
        $cancelled = CarePlan::query()->create([
            'tree_id' => $tree->id,
            'planned_on' => now()->addDays(2)->toDateString(),
            'action' => 'Rencana yang sudah dibatalkan',
            'status' => 'cancelled',
            'priority' => 'low',
        ]);

        Livewire::test(ListCarePlans::class)
            ->set('activeTab', 'cancelled')
            ->assertCanSeeTableRecords([$cancelled])
            ->assertCanNotSeeTableRecords([$published]);
    }
}
