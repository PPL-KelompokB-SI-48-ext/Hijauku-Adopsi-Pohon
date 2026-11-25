<?php

namespace Tests\Feature;

use App\Actions\GrowthObservations\ReturnGrowthObservation;
use App\Actions\GrowthObservations\VerifyGrowthObservation;
use App\Enums\UserRole;
use App\Filament\FieldStaff\Resources\GrowthObservations\Pages\ListGrowthObservations as PetugasListGrowthObservations;
use App\Filament\Manager\Resources\GrowthObservations\Pages\ListGrowthObservations as ManagerListGrowthObservations;
use App\Filament\Manager\Widgets\GrowthConditionChartWidget;
use App\Filament\Manager\Widgets\GrowthSummaryStatsWidget;
use App\Filament\Manager\Widgets\UnmonitoredTreesWidget;
use App\Models\GrowthObservation;
use App\Models\Plot;
use App\Models\Program;
use App\Models\Site;
use App\Models\Tree;
use App\Models\TreeSpecies;
use App\Models\User;
use App\Services\GrowthObservations\GrowthSummaryService;
use Filament\Facades\Filament;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class GrowthObservationVerificationTest extends TestCase
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

    private function createTree(string $code): Tree
    {
        $program = Program::query()->firstOrCreate(
            ['name' => 'Program Verifikasi Pertumbuhan'],
            [
                'objective' => 'Pemantauan pertumbuhan pohon',
                'starts_on' => '2026-01-01',
                'ends_on' => '2026-12-31',
                'status' => 'active',
            ],
        );

        $site = Site::query()->firstOrCreate(
            ['program_id' => $program->id, 'name' => 'Taman Pemantauan'],
            ['address' => 'Jl. Pemantauan No. 1'],
        );

        $plot = Plot::query()->firstOrCreate(
            ['site_id' => $site->id, 'code' => 'MNT-01'],
            ['capacity' => 100],
        );

        $species = TreeSpecies::query()->firstOrCreate(
            ['name' => 'Ketapang Kencana'],
            ['care_notes' => 'Pemantauan tajuk berkala'],
        );

        return Tree::query()->create([
            'species_id' => $species->id,
            'plot_id' => $plot->id,
            'code' => $code,
            'planted_on' => '2026-02-01',
            'initial_condition' => 'good',
        ]);
    }

    private function createObservation(
        User $observer,
        Tree $tree,
        string $observedOn,
        string $status = 'submitted',
        string $condition = GrowthObservation::CONDITION_HEALTHY,
        float $height = 180.0,
        float $diameter = 12.0,
    ): GrowthObservation {
        return GrowthObservation::query()->create([
            'tree_id' => $tree->id,
            'observed_by' => $observer->id,
            'observed_on' => $observedOn,
            'height_cm' => $height,
            'diameter_cm' => $diameter,
            'condition' => $condition,
            'status' => $status,
        ]);
    }

    private function openManagerPanelAs(User $manager): void
    {
        $this->actingAs($manager);
        Filament::setCurrentPanel('app');
    }

    public function test_manager_can_verify_submitted_growth_observation(): void
    {
        $manager = $this->manager();
        $staff = $this->fieldStaff();
        $tree = $this->createTree('PH-V50-01');
        $observation = $this->createObservation($staff, $tree, now()->toDateString());

        $verified = app(VerifyGrowthObservation::class)->handle($manager, $observation, 'Pengukuran valid sesuai foto.');

        $this->assertSame('verified', $verified->status);
        $this->assertSame($manager->getKey(), $verified->verified_by);
        $this->assertNotNull($verified->verified_at);

        $this->assertDatabaseHas('activity_logs', [
            'actor_id' => $manager->getKey(),
            'action' => 'growth_observation.verified',
            'subject_type' => GrowthObservation::class,
            'subject_id' => $observation->getKey(),
            'from_status' => 'submitted',
            'to_status' => 'verified',
        ]);
    }

    public function test_verifier_cannot_be_same_as_observer(): void
    {
        $manager = $this->manager();
        $tree = $this->createTree('PH-V50-02');
        $ownObservation = $this->createObservation($manager, $tree, now()->toDateString());

        $this->assertFalse($manager->can('verify', $ownObservation));

        $this->expectException(AuthorizationException::class);

        app(VerifyGrowthObservation::class)->handle($manager, $ownObservation);
    }

    public function test_non_manager_cannot_verify_or_return_or_view_manager_widgets(): void
    {
        $staffA = $this->fieldStaff();
        $staffB = $this->fieldStaff();
        $citizen = $this->citizen();
        $tree = $this->createTree('PH-V50-03');
        $observation = $this->createObservation($staffA, $tree, now()->toDateString());

        $this->actingAs($staffB);
        $this->assertFalse(GrowthSummaryStatsWidget::canView());
        $this->assertFalse(GrowthConditionChartWidget::canView());
        $this->assertFalse(UnmonitoredTreesWidget::canView());
        $this->get('/app/growth-observations')->assertForbidden();

        $this->actingAs($citizen);
        $this->assertFalse(GrowthSummaryStatsWidget::canView());
        $this->get('/app/growth-observations')->assertForbidden();

        try {
            app(VerifyGrowthObservation::class)->handle($staffB, $observation);
            $this->fail('Petugas tidak boleh memverifikasi pengamatan.');
        } catch (AuthorizationException) {
            $this->assertTrue(true);
        }

        $this->expectException(AuthorizationException::class);
        app(ReturnGrowthObservation::class)->handle($citizen, $observation, ['review_note' => 'Alasan tidak sah']);
    }

    public function test_cannot_verify_or_return_non_submitted_observation(): void
    {
        $manager = $this->manager();
        $staff = $this->fieldStaff();

        foreach (['draft', 'verified', 'returned'] as $idx => $status) {
            $tree = $this->createTree("PH-V50-ST-{$idx}");
            $obs = $this->createObservation($staff, $tree, now()->toDateString(), $status);

            try {
                app(VerifyGrowthObservation::class)->handle($manager, $obs);
                $this->fail("Seharusnya menolak verifikasi untuk status {$status}.");
            } catch (ValidationException $e) {
                $this->assertArrayHasKey('status', $e->errors());
            }

            try {
                app(ReturnGrowthObservation::class)->handle($manager, $obs, ['review_note' => 'Alasan penolakan valid.']);
                $this->fail("Seharusnya menolak pengembalian untuk status {$status}.");
            } catch (ValidationException $e) {
                $this->assertArrayHasKey('status', $e->errors());
            }
        }
    }

    public function test_manager_can_return_submitted_observation_with_mandatory_reason(): void
    {
        $manager = $this->manager();
        $staff = $this->fieldStaff();
        $tree = $this->createTree('PH-V50-04');
        $observation = $this->createObservation($staff, $tree, now()->toDateString());

        try {
            app(ReturnGrowthObservation::class)->handle($manager, $observation, ['review_note' => 'ok']);
            $this->fail('Seharusnya menolak alasan terlalu singkat.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('review_note', $e->errors());
        }

        $returned = app(ReturnGrowthObservation::class)->handle($manager, $observation, [
            'review_note' => 'Diameter tercatat tidak wajar, harap ukur ulang batang setinggi dada.',
        ]);

        $this->assertSame('returned', $returned->status);
        $this->assertSame('Diameter tercatat tidak wajar, harap ukur ulang batang setinggi dada.', $returned->review_note);

        $this->assertDatabaseHas('activity_logs', [
            'actor_id' => $manager->getKey(),
            'action' => 'growth_observation.returned',
            'subject_id' => $observation->getKey(),
            'to_status' => 'returned',
        ]);

        // Pastikan catatan pengembalian terlihat oleh petugas di panel /petugas
        $this->actingAs($staff);
        Filament::setCurrentPanel('petugas');

        Livewire::test(PetugasListGrowthObservations::class)
            ->assertCanSeeTableRecords([$returned])
            ->assertSee('Dikembalikan');
    }

    public function test_only_verified_observations_enter_summary_and_condition_chart(): void
    {
        $manager = $this->manager();
        $staff = $this->fieldStaff();
        $today = now()->toDateString();

        $treeVerified = $this->createTree('PH-V50-SUM-1');
        $treeSubmitted = $this->createTree('PH-V50-SUM-2');
        $treeReturned = $this->createTree('PH-V50-SUM-3');

        $this->createObservation($staff, $treeVerified, $today, 'verified', GrowthObservation::CONDITION_HEALTHY, 200.0, 12.0);
        $this->createObservation($staff, $treeSubmitted, $today, 'submitted', GrowthObservation::CONDITION_FAIR, 999.0, 99.0);
        $this->createObservation($staff, $treeReturned, $today, 'returned', GrowthObservation::CONDITION_NEEDS_RECOVERY, 888.0, 88.0);

        $summary = app(GrowthSummaryService::class)->summarize();

        $this->assertSame(1, $summary['verified_observations_count']);
        $this->assertSame(1, $summary['monitored_trees_count']);
        $this->assertSame(2, $summary['unmonitored_trees_count']);
        $this->assertSame(200.0, $summary['average_height_cm']);
        $this->assertSame(12.0, $summary['average_diameter_cm']);
        $this->assertSame([
            GrowthObservation::CONDITION_HEALTHY => 1,
            GrowthObservation::CONDITION_FAIR => 0,
            GrowthObservation::CONDITION_NEEDS_RECOVERY => 0,
            'unmonitored' => 2,
        ], $summary['condition_counts']);

        $chartData = app(GrowthSummaryService::class)->chartData();
        $this->assertSame([1, 0, 0, 2], $chartData['datasets'][0]['data']);

        $this->openManagerPanelAs($manager);

        Livewire::test(GrowthSummaryStatsWidget::class)
            ->assertSee('200,0 cm / 12,0 cm')
            ->assertDontSee('999,0')
            ->assertDontSee('888,0');
    }

    public function test_trees_more_than_30_days_without_verified_observation_are_marked_unmonitored(): void
    {
        $manager = $this->manager();
        $staff = $this->fieldStaff();

        $treeMonitored = $this->createTree('PH-V50-MON-30D');
        $treeExpired = $this->createTree('PH-V50-MON-31D');
        $treeNever = $this->createTree('PH-V50-MON-NONE');
        $treeSubmittedOnly = $this->createTree('PH-V50-MON-SUB');

        // Tepat 30 hari lalu (terverified) -> masih terpantau
        $this->createObservation($staff, $treeMonitored, now()->subDays(30)->toDateString(), 'verified');

        // 31 hari lalu (terverified) -> >30 hari tanpa pengamatan terverifikasi -> Belum Terpantau
        $this->createObservation($staff, $treeExpired, now()->subDays(31)->toDateString(), 'verified');

        // Pengamatan submitted hari ini tapi verified lama 35 hari lalu -> Belum Terpantau
        $this->createObservation($staff, $treeSubmittedOnly, now()->subDays(35)->toDateString(), 'verified');
        $this->createObservation($staff, $treeSubmittedOnly, now()->toDateString(), 'submitted');

        $unmonitoredIds = Tree::query()->unmonitored(30)->pluck('id')->all();

        $this->assertNotContains($treeMonitored->id, $unmonitoredIds);
        $this->assertContains($treeExpired->id, $unmonitoredIds);
        $this->assertContains($treeNever->id, $unmonitoredIds);
        $this->assertContains($treeSubmittedOnly->id, $unmonitoredIds);

        $this->openManagerPanelAs($manager);

        Livewire::test(UnmonitoredTreesWidget::class)
            ->assertCanSeeTableRecords([$treeExpired, $treeNever, $treeSubmittedOnly])
            ->assertCanNotSeeTableRecords([$treeMonitored])
            ->assertSee('Belum Terpantau');
    }

    public function test_condition_chart_widget_and_summary_do_not_claim_sdg_figures(): void
    {
        $manager = $this->manager();
        $this->openManagerPanelAs($manager);

        $summary = app(GrowthSummaryService::class)->summarize();
        $this->assertStringContainsString('tidak mengklaim angka atau capaian SDG', $summary['disclaimer']);

        Livewire::test(GrowthConditionChartWidget::class)
            ->assertSee('Grafik Kondisi Fisik Pohon (Non-SDG)')
            ->assertSee('tidak mengklaim angka atau capaian SDG');
    }

    public function test_manager_can_verify_and_return_via_filament_table_actions(): void
    {
        $manager = $this->manager();
        $staff = $this->fieldStaff();
        $treeA = $this->createTree('PH-V50-UI-1');
        $treeB = $this->createTree('PH-V50-UI-2');

        $obsToVerify = $this->createObservation($staff, $treeA, now()->toDateString(), 'submitted');
        $obsToReturn = $this->createObservation($staff, $treeB, now()->toDateString(), 'submitted');

        $this->openManagerPanelAs($manager);

        Livewire::test(ManagerListGrowthObservations::class)
            ->assertCanSeeTableRecords([$obsToVerify, $obsToReturn])
            ->callTableAction('verify', $obsToVerify)
            ->assertHasNoTableActionErrors()
            ->callTableAction('return', $obsToReturn, data: [
                'review_note' => 'Foto bukti kurang fokus pada bagian tajuk dan pangkal pohon.',
            ])
            ->assertHasNoTableActionErrors();

        $this->assertSame('verified', $obsToVerify->fresh()->status);
        $this->assertSame('returned', $obsToReturn->fresh()->status);
        $this->assertSame('Foto bukti kurang fokus pada bagian tajuk dan pangkal pohon.', $obsToReturn->fresh()->review_note);
    }
}
