<?php

namespace Tests\Feature;

use App\Actions\GrowthObservations\CreateGrowthObservation;
use App\Enums\UserRole;
use App\Filament\FieldStaff\Resources\GrowthObservations\Pages\CreateGrowthObservation as CreateGrowthObservationPage;
use App\Filament\FieldStaff\Resources\GrowthObservations\Pages\ListGrowthObservations;
use App\Models\GrowthObservation;
use App\Models\Plot;
use App\Models\Program;
use App\Models\Site;
use App\Models\Tree;
use App\Models\TreeSpecies;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class GrowthObservationCreationTest extends TestCase
{
    use RefreshDatabase;

    private function fieldStaff(): User
    {
        return User::factory()->create([
            'role' => UserRole::FieldStaff,
            'is_active' => true,
        ]);
    }

    private function manager(): User
    {
        return User::factory()->create([
            'role' => UserRole::Manager,
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

    private function createTree(string $code = 'PH-OBS-001'): Tree
    {
        $program = Program::query()->firstOrCreate(
            ['name' => 'Program Observasi'],
            [
                'objective' => 'Pemantauan pertumbuhan pohon',
                'starts_on' => '2026-01-01',
                'ends_on' => '2026-12-31',
                'status' => 'active',
            ],
        );

        $site = Site::query()->firstOrCreate(
            ['program_id' => $program->id, 'name' => 'Taman Observasi'],
            ['address' => 'Jl. Konservasi No. 10'],
        );

        $plot = Plot::query()->firstOrCreate(
            ['site_id' => $site->id, 'code' => 'OBS-A1'],
            ['capacity' => 50],
        );

        $species = TreeSpecies::query()->firstOrCreate(
            ['name' => 'Mahoni'],
            ['care_notes' => 'Pengukuran rutin bulanan'],
        );

        return Tree::query()->create([
            'species_id' => $species->id,
            'plot_id' => $plot->id,
            'code' => $code,
            'planted_on' => '2026-02-01',
            'initial_condition' => 'good',
        ]);
    }

    private function openFieldStaffPanelAs(User $staff): void
    {
        $this->actingAs($staff);
        Filament::setCurrentPanel('petugas');
    }

    public function test_field_staff_can_create_growth_observation_without_photo(): void
    {
        $staff = $this->fieldStaff();
        $tree = $this->createTree('PH-OBS-001');

        $observation = app(CreateGrowthObservation::class)->handle($staff, [
            'tree_id' => $tree->id,
            'observed_on' => '2026-11-23',
            'height_cm' => 185.5,
            'diameter_cm' => 12.4,
            'condition' => GrowthObservation::CONDITION_HEALTHY,
            'photo' => null,
        ]);

        $this->assertDatabaseHas('growth_observations', [
            'id' => $observation->id,
            'tree_id' => $tree->id,
            'observed_by' => $staff->id,
            'height_cm' => 185.50,
            'diameter_cm' => 12.40,
            'condition' => GrowthObservation::CONDITION_HEALTHY,
            'photo_path' => null,
            'status' => 'submitted',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'actor_id' => $staff->getKey(),
            'action' => 'growth_observation.created',
            'subject_type' => GrowthObservation::class,
            'subject_id' => $observation->getKey(),
            'to_status' => 'submitted',
        ]);
    }

    public function test_field_staff_can_create_growth_observation_with_optional_photo(): void
    {
        Storage::fake('private');

        $staff = $this->fieldStaff();
        $tree = $this->createTree('PH-OBS-002');
        $photo = UploadedFile::fake()->create('pengamatan.jpg', 250, 'image/jpeg');

        $observation = app(CreateGrowthObservation::class)->handle($staff, [
            'tree_id' => $tree->id,
            'observed_on' => '2026-11-23',
            'height_cm' => 210,
            'diameter_cm' => 14.8,
            'condition' => GrowthObservation::CONDITION_FAIR,
            'photo' => $photo,
        ]);

        $this->assertNotNull($observation->photo_path);
        Storage::disk('private')->assertExists($observation->photo_path);
    }

    public function test_height_must_be_positive_number(): void
    {
        $staff = $this->fieldStaff();
        $tree = $this->createTree('PH-OBS-003');

        foreach ([0, -10.5, 'tidak-valid'] as $invalidHeight) {
            try {
                app(CreateGrowthObservation::class)->handle($staff, [
                    'tree_id' => $tree->id,
                    'observed_on' => '2026-11-23',
                    'height_cm' => $invalidHeight,
                    'diameter_cm' => 10.5,
                    'condition' => GrowthObservation::CONDITION_HEALTHY,
                ]);
                $this->fail('Seharusnya menolak tinggi tidak positif.');
            } catch (ValidationException $e) {
                $this->assertArrayHasKey('height_cm', $e->errors());
            }
        }

        $this->assertDatabaseCount('growth_observations', 0);
    }

    public function test_diameter_must_be_positive_number(): void
    {
        $staff = $this->fieldStaff();
        $tree = $this->createTree('PH-OBS-004');

        foreach ([0, -2.5, 'tidak-valid'] as $invalidDiameter) {
            try {
                app(CreateGrowthObservation::class)->handle($staff, [
                    'tree_id' => $tree->id,
                    'observed_on' => '2026-11-23',
                    'height_cm' => 150,
                    'diameter_cm' => $invalidDiameter,
                    'condition' => GrowthObservation::CONDITION_HEALTHY,
                ]);
                $this->fail('Seharusnya menolak diameter tidak positif.');
            } catch (ValidationException $e) {
                $this->assertArrayHasKey('diameter_cm', $e->errors());
            }
        }

        $this->assertDatabaseCount('growth_observations', 0);
    }

    public function test_unique_observation_per_tree_per_date_is_enforced(): void
    {
        $staffA = $this->fieldStaff();
        $staffB = $this->fieldStaff();
        $tree = $this->createTree('PH-OBS-005');

        app(CreateGrowthObservation::class)->handle($staffA, [
            'tree_id' => $tree->id,
            'observed_on' => '2026-11-23',
            'height_cm' => 160,
            'diameter_cm' => 11.2,
            'condition' => GrowthObservation::CONDITION_HEALTHY,
        ]);

        try {
            app(CreateGrowthObservation::class)->handle($staffB, [
                'tree_id' => $tree->id,
                'observed_on' => '2026-11-23',
                'height_cm' => 162,
                'diameter_cm' => 11.4,
                'condition' => GrowthObservation::CONDITION_HEALTHY,
            ]);
            $this->fail('Seharusnya menolak pengamatan ganda pada pohon dan tanggal yang sama.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('observed_on', $e->errors());
        }

        $this->assertDatabaseCount('growth_observations', 1);
    }

    public function test_same_tree_different_date_and_different_tree_same_date_are_allowed(): void
    {
        $staff = $this->fieldStaff();
        $treeA = $this->createTree('PH-OBS-006A');
        $treeB = $this->createTree('PH-OBS-006B');

        app(CreateGrowthObservation::class)->handle($staff, [
            'tree_id' => $treeA->id,
            'observed_on' => '2026-11-22',
            'height_cm' => 140,
            'diameter_cm' => 9.5,
            'condition' => GrowthObservation::CONDITION_HEALTHY,
        ]);

        app(CreateGrowthObservation::class)->handle($staff, [
            'tree_id' => $treeA->id,
            'observed_on' => '2026-11-23',
            'height_cm' => 141,
            'diameter_cm' => 9.6,
            'condition' => GrowthObservation::CONDITION_HEALTHY,
        ]);

        app(CreateGrowthObservation::class)->handle($staff, [
            'tree_id' => $treeB->id,
            'observed_on' => '2026-11-23',
            'height_cm' => 180,
            'diameter_cm' => 13.0,
            'condition' => GrowthObservation::CONDITION_FAIR,
        ]);

        $this->assertDatabaseCount('growth_observations', 3);
    }

    public function test_photo_size_limit_and_type_are_enforced(): void
    {
        $staff = $this->fieldStaff();
        $tree = $this->createTree('PH-OBS-007');

        $oversizedPhoto = UploadedFile::fake()->create('besar.jpg', 6000, 'image/jpeg');
        try {
            app(CreateGrowthObservation::class)->handle($staff, [
                'tree_id' => $tree->id,
                'observed_on' => '2026-11-23',
                'height_cm' => 170,
                'diameter_cm' => 10.5,
                'condition' => GrowthObservation::CONDITION_HEALTHY,
                'photo' => $oversizedPhoto,
            ]);
            $this->fail('Seharusnya menolak foto melebihi 5 MB.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('photo', $e->errors());
        }

        $invalidFile = UploadedFile::fake()->create('laporan.pdf', 100, 'application/pdf');
        try {
            app(CreateGrowthObservation::class)->handle($staff, [
                'tree_id' => $tree->id,
                'observed_on' => '2026-11-23',
                'height_cm' => 170,
                'diameter_cm' => 10.5,
                'condition' => GrowthObservation::CONDITION_HEALTHY,
                'photo' => $invalidFile,
            ]);
            $this->fail('Seharusnya menolak berkas selain gambar JPG/PNG/WebP.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('photo', $e->errors());
        }
    }

    public function test_private_photo_download_enforces_authorization(): void
    {
        Storage::fake('private');

        $owner = $this->fieldStaff();
        $otherStaff = $this->fieldStaff();
        $manager = $this->manager();
        $citizen = $this->citizen();
        $tree = $this->createTree('PH-OBS-008');

        $observation = app(CreateGrowthObservation::class)->handle($owner, [
            'tree_id' => $tree->id,
            'observed_on' => '2026-11-23',
            'height_cm' => 190,
            'diameter_cm' => 12.0,
            'condition' => GrowthObservation::CONDITION_HEALTHY,
            'photo' => UploadedFile::fake()->create('bukti.png', 150, 'image/png'),
        ]);

        $url = route('growth-observations.photo', $observation);

        $this->get($url)->assertRedirect('/login');
        $this->actingAs($otherStaff)->get($url)->assertForbidden();
        $this->actingAs($citizen)->get($url)->assertForbidden();
        $this->actingAs($owner)->get($url)->assertOk();
        $this->actingAs($manager)->get($url)->assertOk();
    }

    public function test_status_cannot_be_overridden_via_input(): void
    {
        $staff = $this->fieldStaff();

        foreach (['verified', 'returned', 'draft'] as $index => $fakeStatus) {
            $tree = $this->createTree("PH-OBS-STATUS-{$index}");
            $observation = app(CreateGrowthObservation::class)->handle($staff, [
                'tree_id' => $tree->id,
                'observed_on' => '2026-11-23',
                'height_cm' => 175,
                'diameter_cm' => 11.5,
                'condition' => GrowthObservation::CONDITION_HEALTHY,
                'status' => $fakeStatus,
            ]);

            $this->assertSame('submitted', $observation->status);
            $this->assertNull($observation->verified_by);
            $this->assertNull($observation->verified_at);
        }
    }

    public function test_non_field_staff_cannot_create_growth_observation(): void
    {
        $manager = $this->manager();
        $citizen = $this->citizen();
        $tree = $this->createTree('PH-OBS-009');

        $this->assertFalse($manager->can('create', GrowthObservation::class));
        $this->assertFalse($citizen->can('create', GrowthObservation::class));

        $this->expectException(AuthorizationException::class);

        app(CreateGrowthObservation::class)->handle($manager, [
            'tree_id' => $tree->id,
            'observed_on' => '2026-11-23',
            'height_cm' => 180,
            'diameter_cm' => 12.0,
            'condition' => GrowthObservation::CONDITION_HEALTHY,
        ]);
    }

    public function test_observation_list_is_scoped_to_authenticated_field_staff(): void
    {
        $staffA = $this->fieldStaff();
        $staffB = $this->fieldStaff();
        $tree = $this->createTree('PH-OBS-010');

        $observationA = app(CreateGrowthObservation::class)->handle($staffA, [
            'tree_id' => $tree->id,
            'observed_on' => '2026-11-23',
            'height_cm' => 185,
            'diameter_cm' => 12.8,
            'condition' => GrowthObservation::CONDITION_HEALTHY,
        ]);

        $this->assertTrue(GrowthObservation::query()->forObserver($staffA)->whereKey($observationA->id)->exists());
        $this->assertFalse(GrowthObservation::query()->forObserver($staffB)->whereKey($observationA->id)->exists());

        $this->openFieldStaffPanelAs($staffB);

        Livewire::test(ListGrowthObservations::class)
            ->assertCanNotSeeTableRecords([$observationA]);
    }

    public function test_manager_and_citizen_cannot_access_field_staff_growth_observations_panel(): void
    {
        $manager = $this->manager();
        $citizen = $this->citizen();

        $this->actingAs($manager)
            ->get('/petugas/growth-observations')
            ->assertForbidden();

        $this->actingAs($citizen)
            ->get('/petugas/growth-observations')
            ->assertForbidden();
    }

    public function test_livewire_form_creates_observation_and_blocks_duplicate_tree_date(): void
    {
        $staff = $this->fieldStaff();
        $tree = $this->createTree('PH-OBS-011');
        $this->openFieldStaffPanelAs($staff);

        Livewire::test(CreateGrowthObservationPage::class)
            ->fillForm([
                'tree_id' => $tree->id,
                'observed_on' => '2026-11-23',
                'height_cm' => 195.25,
                'diameter_cm' => 13.4,
                'condition' => GrowthObservation::CONDITION_NEEDS_RECOVERY,
            ])
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertRedirect(route('filament.petugas.resources.growth-observations.index'));

        $this->assertDatabaseHas('growth_observations', [
            'tree_id' => $tree->id,
            'observed_by' => $staff->id,
            'height_cm' => 195.25,
            'diameter_cm' => 13.40,
            'condition' => GrowthObservation::CONDITION_NEEDS_RECOVERY,
            'status' => 'submitted',
        ]);

        Livewire::test(CreateGrowthObservationPage::class)
            ->fillForm([
                'tree_id' => $tree->id,
                'observed_on' => '2026-11-23',
                'height_cm' => 196,
                'diameter_cm' => 13.5,
                'condition' => GrowthObservation::CONDITION_HEALTHY,
            ])
            ->call('create')
            ->assertHasFormErrors(['observed_on']);
    }
}
