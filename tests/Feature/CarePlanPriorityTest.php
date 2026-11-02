<?php

namespace Tests\Feature;

use App\Actions\CarePlans\UpdateCarePlanPriority;
use App\Enums\UserRole;
use App\Filament\Manager\Resources\CarePlans\Pages\CreateCarePlan as CreateCarePlanPage;
use App\Filament\Manager\Resources\CarePlans\Pages\ListCarePlans;
use App\Filament\Manager\Widgets\OverdueCarePlansWidget;
use App\Models\CarePlan;
use App\Models\CareRecord;
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

class CarePlanPriorityTest extends TestCase
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

    private function createSite(string $name = 'Taman Kota'): Site
    {
        $program = Program::query()->firstOrCreate([
            'name' => 'Program Hijauku',
        ], [
            'objective' => 'Konservasi lingkungan',
            'starts_on' => '2026-01-01',
            'ends_on' => '2026-12-31',
            'status' => 'active',
        ]);

        return Site::query()->create([
            'program_id' => $program->id,
            'name' => $name,
            'address' => 'Jl. Konservasi No. '.rand(1, 999),
        ]);
    }

    private function createTree(string $code = 'PH-001', ?Site $site = null): Tree
    {
        $site = $site ?? $this->createSite();

        $plot = Plot::query()->create([
            'site_id' => $site->id,
            'code' => 'PL-'.rand(100, 999),
            'capacity' => 50,
        ]);

        $species = TreeSpecies::query()->firstOrCreate([
            'name' => 'Trembesi',
        ], [
            'care_notes' => 'Penyiraman teratur',
        ]);

        return Tree::query()->create([
            'species_id' => $species->id,
            'plot_id' => $plot->id,
            'code' => $code,
            'planted_on' => '2026-02-01',
            'initial_condition' => 'good',
        ]);
    }

    private function openManagerPanelAs(User $manager): void
    {
        $this->actingAs($manager);
        Filament::setCurrentPanel('app');
    }

    public function test_overdue_scope_identifies_overdue_unverified_plans(): void
    {
        $tree = $this->createTree('PH-OD-1');
        $staff = $this->fieldStaff();
        $manager = $this->manager();

        // 1. Lewat target, tanpa realisasi -> Terlambat
        $overdueNoRecord = CarePlan::query()->create([
            'tree_id' => $tree->id,
            'planned_on' => now()->subDays(3)->toDateString(),
            'action' => 'Penyiraman intensif',
            'status' => 'published',
            'priority' => 'normal',
        ]);

        // 2. Lewat target draf, tanpa realisasi -> Terlambat
        $overdueDraft = CarePlan::query()->create([
            'tree_id' => $tree->id,
            'planned_on' => now()->subDays(2)->toDateString(),
            'action' => 'Pemangkasan dahan',
            'status' => 'draft',
            'priority' => 'normal',
        ]);

        // 3. Lewat target, ada realisasi tapi status draft (belum terverifikasi) -> Terlambat
        $overdueUnverified = CarePlan::query()->create([
            'tree_id' => $tree->id,
            'planned_on' => now()->subDays(4)->toDateString(),
            'action' => 'Pemberian pupuk kandang',
            'status' => 'published',
            'priority' => 'normal',
        ]);
        CareRecord::query()->create([
            'tree_id' => $tree->id,
            'plan_id' => $overdueUnverified->id,
            'performed_by' => $staff->id,
            'performed_on' => now()->subDays(3)->toDateString(),
            'action' => 'Pupuk telah disebar',
            'status' => 'draft',
            'verified_at' => null,
        ]);

        // 4. Lewat target, ada realisasi sudah diverifikasi -> Tidak Terlambat
        $notOverdueVerified = CarePlan::query()->create([
            'tree_id' => $tree->id,
            'planned_on' => now()->subDays(5)->toDateString(),
            'action' => 'Pemeriksaan hama daun',
            'status' => 'published',
            'priority' => 'normal',
        ]);
        CareRecord::query()->create([
            'tree_id' => $tree->id,
            'plan_id' => $notOverdueVerified->id,
            'performed_by' => $staff->id,
            'performed_on' => now()->subDays(4)->toDateString(),
            'action' => 'Hama dibersihkan',
            'status' => 'verified',
            'verified_by' => $manager->id,
            'verified_at' => now()->subDays(3),
        ]);

        // 5. Belum lewat target (masa depan) -> Tidak Terlambat
        $futurePlan = CarePlan::query()->create([
            'tree_id' => $tree->id,
            'planned_on' => now()->addDays(5)->toDateString(),
            'action' => 'Penyiraman masa depan',
            'status' => 'published',
            'priority' => 'normal',
        ]);

        // 6. Dibatalkan -> Tidak Terlambat
        $cancelledPlan = CarePlan::query()->create([
            'tree_id' => $tree->id,
            'planned_on' => now()->subDays(3)->toDateString(),
            'action' => 'Rencana dibatalkan',
            'status' => 'cancelled',
            'priority' => 'normal',
        ]);

        $overdueIds = CarePlan::query()->overdue()->pluck('id')->all();

        $this->assertContains($overdueNoRecord->id, $overdueIds);
        $this->assertContains($overdueDraft->id, $overdueIds);
        $this->assertContains($overdueUnverified->id, $overdueIds);
        $this->assertNotContains($notOverdueVerified->id, $overdueIds);
        $this->assertNotContains($futurePlan->id, $overdueIds);
        $this->assertNotContains($cancelledPlan->id, $overdueIds);

        $this->assertTrue($overdueNoRecord->isOverdue());
        $this->assertTrue($overdueDraft->isOverdue());
        $this->assertTrue($overdueUnverified->isOverdue());
        $this->assertFalse($notOverdueVerified->isOverdue());
        $this->assertFalse($futurePlan->isOverdue());
        $this->assertFalse($cancelledPlan->isOverdue());
    }

    public function test_dashboard_renders_overdue_care_plans_widget_for_manager(): void
    {
        $manager = $this->manager();
        $this->openManagerPanelAs($manager);

        $this->assertTrue(OverdueCarePlansWidget::canView());

        $this->get('/app')
            ->assertOk()
            ->assertSeeLivewire(OverdueCarePlansWidget::class);
    }

    public function test_non_manager_cannot_view_overdue_widget(): void
    {
        $fieldStaff = $this->fieldStaff();
        $this->actingAs($fieldStaff);
        $this->assertFalse(OverdueCarePlansWidget::canView());

        $citizen = $this->citizen();
        $this->actingAs($citizen);
        $this->assertFalse(OverdueCarePlansWidget::canView());
    }

    public function test_overdue_widget_displays_overdue_plans(): void
    {
        $manager = $this->manager();
        $this->openManagerPanelAs($manager);
        $tree = $this->createTree('PH-WIDGET-1');

        $overduePlan = CarePlan::query()->create([
            'tree_id' => $tree->id,
            'planned_on' => now()->subDays(2)->toDateString(),
            'action' => 'Perawatan pohon terlambat di widget',
            'status' => 'published',
            'priority' => 'normal',
        ]);

        $futurePlan = CarePlan::query()->create([
            'tree_id' => $tree->id,
            'planned_on' => now()->addDays(3)->toDateString(),
            'action' => 'Perawatan pohon masa depan',
            'status' => 'published',
            'priority' => 'normal',
        ]);

        Livewire::test(OverdueCarePlansWidget::class)
            ->assertCanSeeTableRecords([$overduePlan])
            ->assertCanNotSeeTableRecords([$futurePlan]);
    }

    public function test_manager_can_update_care_plan_priority_with_reason_on_table(): void
    {
        $manager = $this->manager();
        $this->openManagerPanelAs($manager);
        $tree = $this->createTree('PH-PRIO-1');

        $plan = CarePlan::query()->create([
            'tree_id' => $tree->id,
            'planned_on' => now()->addDays(2)->toDateString(),
            'action' => 'Inspeksi kondisi kanopi',
            'status' => 'published',
            'priority' => 'normal',
        ]);

        Livewire::test(ListCarePlans::class)
            ->callTableAction('change_priority', $plan, data: [
                'priority' => 'urgent',
                'reason' => 'Ditemukan retakan cabang pasca angin kencang.',
            ]);

        $plan->refresh();

        $this->assertSame('urgent', $plan->priority);

        $this->assertDatabaseHas('activity_logs', [
            'actor_id' => $manager->getKey(),
            'action' => 'care_plan.priority_updated',
            'subject_type' => CarePlan::class,
            'subject_id' => $plan->getKey(),
        ]);

        $log = $plan->activityLogs()->where('action', 'care_plan.priority_updated')->first();
        $this->assertNotNull($log);
        $this->assertSame('normal', $log->metadata['old_priority']);
        $this->assertSame('urgent', $log->metadata['new_priority']);
        $this->assertSame('Ditemukan retakan cabang pasca angin kencang.', $log->metadata['reason']);
    }

    public function test_manager_can_update_priority_from_overdue_widget(): void
    {
        $manager = $this->manager();
        $this->openManagerPanelAs($manager);
        $tree = $this->createTree('PH-PRIO-2');

        $plan = CarePlan::query()->create([
            'tree_id' => $tree->id,
            'planned_on' => now()->subDays(3)->toDateString(),
            'action' => 'Penyiraman darurat',
            'status' => 'published',
            'priority' => 'low',
        ]);

        Livewire::test(OverdueCarePlansWidget::class)
            ->callTableAction('change_priority', $plan, data: [
                'priority' => 'high',
                'reason' => 'Perawatan telah lewat batas target dan membutuhkan eskalasi.',
            ]);

        $plan->refresh();

        $this->assertSame('high', $plan->priority);

        $this->assertDatabaseHas('activity_logs', [
            'actor_id' => $manager->getKey(),
            'action' => 'care_plan.priority_updated',
            'subject_type' => CarePlan::class,
            'subject_id' => $plan->getKey(),
        ]);
    }

    public function test_priority_update_requires_valid_priority_and_reason(): void
    {
        $manager = $this->manager();
        $tree = $this->createTree('PH-VAL-1');

        $plan = CarePlan::query()->create([
            'tree_id' => $tree->id,
            'planned_on' => now()->toDateString(),
            'action' => 'Pemupukan berkala',
            'status' => 'published',
            'priority' => 'normal',
        ]);

        // Alasan kosong
        try {
            app(UpdateCarePlanPriority::class)->handle($manager, $plan, [
                'priority' => 'high',
                'reason' => '   ',
            ]);
            $this->fail('Harus gagal jika alasan hanya spasi.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('reason', $e->errors());
        }

        // Prioritas tidak valid
        try {
            app(UpdateCarePlanPriority::class)->handle($manager, $plan, [
                'priority' => 'invalid_priority',
                'reason' => 'Alasan yang sah dan cukup panjang.',
            ]);
            $this->fail('Harus gagal jika prioritas tidak valid.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('priority', $e->errors());
        }

        // Rencana yang dibatalkan tidak boleh diubah prioritasnya
        $plan->status = 'cancelled';
        $plan->save();

        try {
            app(UpdateCarePlanPriority::class)->handle($manager, $plan, [
                'priority' => 'high',
                'reason' => 'Alasan yang sah dan cukup panjang.',
            ]);
            $this->fail('Harus gagal jika rencana berstatus dibatalkan.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('priority', $e->errors());
        }
    }

    public function test_non_manager_cannot_update_priority(): void
    {
        $fieldStaff = $this->fieldStaff();
        $tree = $this->createTree('PH-AUTH-1');

        $plan = CarePlan::query()->create([
            'tree_id' => $tree->id,
            'planned_on' => now()->toDateString(),
            'action' => 'Aksi pengujian otorisasi',
            'status' => 'published',
            'priority' => 'normal',
        ]);

        $this->expectException(AuthorizationException::class);

        app(UpdateCarePlanPriority::class)->handle($fieldStaff, $plan, [
            'priority' => 'urgent',
            'reason' => 'Diperbarui oleh staf yang tidak berwenang.',
        ]);
    }

    public function test_care_plans_table_can_filter_by_tree_and_location(): void
    {
        $manager = $this->manager();
        $this->openManagerPanelAs($manager);

        $siteA = $this->createSite('Hutan Kota Timur');
        $siteB = $this->createSite('Taman Kota Barat');

        $treeA = $this->createTree('PH-FLT-A', $siteA);
        $treeB = $this->createTree('PH-FLT-B', $siteB);

        $planA = CarePlan::query()->create([
            'tree_id' => $treeA->id,
            'planned_on' => now()->addDays(2)->toDateString(),
            'action' => 'Perawatan Pohon A',
            'status' => 'published',
            'priority' => 'high',
        ]);

        $planB = CarePlan::query()->create([
            'tree_id' => $treeB->id,
            'planned_on' => now()->addDays(4)->toDateString(),
            'action' => 'Perawatan Pohon B',
            'status' => 'published',
            'priority' => 'low',
        ]);

        // 1. Filter per Pohon
        Livewire::test(ListCarePlans::class)
            ->filterTable('tree_id', $treeA->id)
            ->assertCanSeeTableRecords([$planA])
            ->assertCanNotSeeTableRecords([$planB]);

        // 2. Filter per Lokasi
        Livewire::test(ListCarePlans::class)
            ->filterTable('site_id', $siteB->id)
            ->assertCanSeeTableRecords([$planB])
            ->assertCanNotSeeTableRecords([$planA]);

        // 3. Filter per Prioritas
        Livewire::test(ListCarePlans::class)
            ->filterTable('priority', 'high')
            ->assertCanSeeTableRecords([$planA])
            ->assertCanNotSeeTableRecords([$planB]);
    }

    public function test_overdue_widget_can_filter_by_tree_and_location(): void
    {
        $manager = $this->manager();
        $this->openManagerPanelAs($manager);

        $siteA = $this->createSite('Kawasan Hijau Sentral');
        $siteB = $this->createSite('Kawasan Hijau Selatan');

        $treeA = $this->createTree('PH-WFLT-A', $siteA);
        $treeB = $this->createTree('PH-WFLT-B', $siteB);

        $planA = CarePlan::query()->create([
            'tree_id' => $treeA->id,
            'planned_on' => now()->subDays(3)->toDateString(),
            'action' => 'Perawatan Terlambat Pohon A',
            'status' => 'published',
            'priority' => 'urgent',
        ]);

        $planB = CarePlan::query()->create([
            'tree_id' => $treeB->id,
            'planned_on' => now()->subDays(2)->toDateString(),
            'action' => 'Perawatan Terlambat Pohon B',
            'status' => 'published',
            'priority' => 'normal',
        ]);

        // Filter per pohon pada widget
        Livewire::test(OverdueCarePlansWidget::class)
            ->filterTable('tree_id', $treeA->id)
            ->assertCanSeeTableRecords([$planA])
            ->assertCanNotSeeTableRecords([$planB]);

        // Filter per lokasi pada widget
        Livewire::test(OverdueCarePlansWidget::class)
            ->filterTable('site_id', $siteB->id)
            ->assertCanSeeTableRecords([$planB])
            ->assertCanNotSeeTableRecords([$planA]);
    }

    public function test_create_care_plan_can_set_initial_priority(): void
    {
        $manager = $this->manager();
        $this->openManagerPanelAs($manager);
        $tree = $this->createTree('PH-NEW-1');

        Livewire::test(CreateCarePlanPage::class)
            ->fillForm([
                'tree_id' => $tree->id,
                'planned_on' => '2026-11-20',
                'priority' => 'high',
                'action' => 'Pemulihan pasca cuaca panas ekstrem.',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $plan = CarePlan::query()->where('tree_id', $tree->id)->sole();

        $this->assertSame('high', $plan->priority);
        $this->assertSame('draft', $plan->status);

        $this->assertDatabaseHas('activity_logs', [
            'actor_id' => $manager->getKey(),
            'action' => 'care_plan.created',
            'subject_type' => CarePlan::class,
            'subject_id' => $plan->getKey(),
        ]);
    }
}
