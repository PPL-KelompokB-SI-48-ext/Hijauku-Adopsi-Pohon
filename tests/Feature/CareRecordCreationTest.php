<?php

namespace Tests\Feature;

use App\Actions\CareRecords\CreateCareRecord;
use App\Enums\UserRole;
use App\Filament\FieldStaff\Resources\CareRecords\Pages\CreateCareRecord as CreateCareRecordPage;
use App\Filament\FieldStaff\Resources\CareRecords\Pages\ListCareRecords;
use App\Models\Activity;
use App\Models\CarePlan;
use App\Models\CareRecord;
use App\Models\FieldAssignment;
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

class CareRecordCreationTest extends TestCase
{
    use RefreshDatabase;

    // ------------------------------------------------------------------ helpers

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

    private function createTree(string $code = 'PH-001'): Tree
    {
        $program = Program::query()->firstOrCreate(
            ['name' => 'Program Hijau'],
            [
                'objective' => 'Konservasi lingkungan',
                'starts_on' => '2026-01-01',
                'ends_on' => '2026-12-31',
                'status' => 'active',
            ],
        );

        $site = Site::query()->firstOrCreate(
            ['program_id' => $program->id, 'name' => 'Taman Kota'],
            ['address' => 'Jl. Hijau No. 1'],
        );

        $plot = Plot::query()->firstOrCreate(
            ['site_id' => $site->id, 'code' => 'A-01'],
            ['capacity' => 50],
        );

        $species = TreeSpecies::query()->firstOrCreate(
            ['name' => 'Trembesi'],
            ['care_notes' => 'Penyiraman teratur'],
        );

        return Tree::query()->create([
            'species_id' => $species->id,
            'plot_id' => $plot->id,
            'code' => $code,
            'planted_on' => '2026-02-01',
            'initial_condition' => 'good',
        ]);
    }

    private function createAssignment(User $staff, Tree $tree): FieldAssignment
    {
        $activity = Activity::query()->create([
            'site_id' => $tree->plot->site_id,
            'title' => 'Perawatan Rutin',
            'kind' => 'care',
            'starts_at' => '2026-11-08 08:00:00',
            'ends_at' => '2026-11-08 12:00:00',
            'status' => 'scheduled',
            'quota' => 10,
        ]);

        return FieldAssignment::query()->create([
            'activity_id' => $activity->id,
            'tree_id' => $tree->id,
            'assignee_id' => $staff->id,
            'due_at' => '2026-11-15 17:00:00',
            'status' => 'assigned',
        ]);
    }

    private function openFieldStaffPanelAs(User $staff): void
    {
        $this->actingAs($staff);
        Filament::setCurrentPanel('petugas');
    }

    // ------------------------------------------------------------------ tests

    public function test_field_staff_can_create_care_record_for_assigned_tree(): void
    {
        Storage::fake('private');

        $staff = $this->fieldStaff();
        $tree = $this->createTree('PH-001');
        $this->createAssignment($staff, $tree);

        $record = app(CreateCareRecord::class)->handle($staff, [
            'tree_id' => $tree->id,
            'plan_id' => null,
            'performed_on' => '2026-11-10',
            'action' => 'Pemupukan organik dan penggemburan tanah.',
            'photo' => null,
        ]);

        $this->assertDatabaseHas('care_records', [
            'id' => $record->id,
            'tree_id' => $tree->id,
            'performed_by' => $staff->id,
            'status' => 'draft',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'actor_id' => $staff->getKey(),
            'action' => 'care_record.created',
            'subject_type' => CareRecord::class,
            'subject_id' => $record->getKey(),
            'to_status' => 'draft',
        ]);
    }

    public function test_field_staff_can_create_care_record_with_plan_reference(): void
    {
        Storage::fake('private');

        $staff = $this->fieldStaff();
        $tree = $this->createTree('PH-002');
        $this->createAssignment($staff, $tree);

        $plan = CarePlan::query()->create([
            'tree_id' => $tree->id,
            'planned_on' => '2026-11-10',
            'action' => 'Pemangkasan sanitasi',
            'status' => 'published',
        ]);

        $record = app(CreateCareRecord::class)->handle($staff, [
            'tree_id' => $tree->id,
            'plan_id' => $plan->id,
            'performed_on' => '2026-11-10',
            'action' => 'Pemangkasan ranting kering selesai.',
            'photo' => null,
        ]);

        $this->assertSame($plan->id, $record->plan_id);
        $this->assertSame('draft', $record->status);
    }

    public function test_field_staff_cannot_create_record_for_unassigned_tree(): void
    {
        $staff = $this->fieldStaff();
        $tree = $this->createTree('PH-003');
        // Sengaja tidak buat assignment untuk staff ini

        $this->expectException(ValidationException::class);

        app(CreateCareRecord::class)->handle($staff, [
            'tree_id' => $tree->id,
            'plan_id' => null,
            'performed_on' => '2026-11-10',
            'action' => 'Penyiraman tidak diizinkan.',
            'photo' => null,
        ]);
    }

    public function test_care_record_requires_tree_action_and_date(): void
    {
        $staff = $this->fieldStaff();
        $this->openFieldStaffPanelAs($staff);

        Livewire::test(CreateCareRecordPage::class)
            ->call('create')
            ->assertHasFormErrors([
                'tree_id' => 'required',
                'performed_on' => 'required',
                'action' => 'required',
            ]);

        $this->assertDatabaseCount('care_records', 0);
    }

    public function test_plan_id_must_match_tree(): void
    {
        $staff = $this->fieldStaff();
        $treeA = $this->createTree('PH-010');
        $treeB = $this->createTree('PH-011');
        $this->createAssignment($staff, $treeA);

        $planForB = CarePlan::query()->create([
            'tree_id' => $treeB->id,
            'planned_on' => '2026-11-10',
            'action' => 'Perawatan pohon B',
            'status' => 'published',
        ]);

        $this->expectException(ValidationException::class);

        app(CreateCareRecord::class)->handle($staff, [
            'tree_id' => $treeA->id,
            'plan_id' => $planForB->id,   // plan milik treeB bukan treeA
            'performed_on' => '2026-11-10',
            'action' => 'Realisasi salah rencana.',
            'photo' => null,
        ]);
    }

    public function test_photo_size_limit_enforced(): void
    {
        $staff = $this->fieldStaff();
        $tree = $this->createTree('PH-020');
        $this->createAssignment($staff, $tree);

        $this->expectException(ValidationException::class);

        // Buat file fake 6 MB (> 5 MB limit) tanpa butuh ekstensi GD
        $bigPhoto = UploadedFile::fake()->create('besar.jpg', 6000, 'image/jpeg');

        app(CreateCareRecord::class)->handle($staff, [
            'tree_id' => $tree->id,
            'plan_id' => null,
            'performed_on' => '2026-11-10',
            'action' => 'Foto terlalu besar.',
            'photo' => $bigPhoto,
        ]);
    }

    public function test_photo_type_validation(): void
    {
        $staff = $this->fieldStaff();
        $tree = $this->createTree('PH-021');
        $this->createAssignment($staff, $tree);

        $this->expectException(ValidationException::class);

        $invalidFile = UploadedFile::fake()->create('dokumen.pdf', 100, 'application/pdf');

        app(CreateCareRecord::class)->handle($staff, [
            'tree_id' => $tree->id,
            'plan_id' => null,
            'performed_on' => '2026-11-10',
            'action' => 'Upload file bukan gambar.',
            'photo' => $invalidFile,
        ]);
    }

    public function test_photo_is_stored_on_private_disk(): void
    {
        Storage::fake('private');

        $staff = $this->fieldStaff();
        $tree = $this->createTree('PH-022');
        $this->createAssignment($staff, $tree);

        // Simulasi foto JPEG 100 KB tanpa ekstensi GD
        $photo = UploadedFile::fake()->create('bukti.jpg', 100, 'image/jpeg');

        $record = app(CreateCareRecord::class)->handle($staff, [
            'tree_id' => $tree->id,
            'plan_id' => null,
            'performed_on' => '2026-11-10',
            'action' => 'Pemangkasan berkala.',
            'photo' => $photo,
        ]);

        $this->assertNotNull($record->photo_path);
        Storage::disk('private')->assertExists($record->photo_path);
    }

    public function test_non_field_staff_cannot_create_care_record(): void
    {
        $mgr = $this->manager();
        $citizen = $this->citizen();
        $tree = $this->createTree('PH-030');

        $this->assertFalse($mgr->can('create', CareRecord::class));
        $this->assertFalse($citizen->can('create', CareRecord::class));

        $this->expectException(AuthorizationException::class);

        app(CreateCareRecord::class)->handle($mgr, [
            'tree_id' => $tree->id,
            'plan_id' => null,
            'performed_on' => '2026-11-10',
            'action' => 'Manager tidak bisa catat realisasi.',
            'photo' => null,
        ]);
    }

    public function test_created_record_is_scoped_to_performer(): void
    {
        Storage::fake('private');

        $staffA = $this->fieldStaff();
        $staffB = $this->fieldStaff();
        $tree = $this->createTree('PH-040');
        $this->createAssignment($staffA, $tree);

        // staffA buat record
        $record = app(CreateCareRecord::class)->handle($staffA, [
            'tree_id' => $tree->id,
            'plan_id' => null,
            'performed_on' => '2026-11-10',
            'action' => 'Realisasi milik staffA.',
            'photo' => null,
        ]);

        // Scope forPerformer: staffA lihat recordnya, staffB tidak
        $visibleToA = CareRecord::query()->forPerformer($staffA)->pluck('id');
        $visibleToB = CareRecord::query()->forPerformer($staffB)->pluck('id');

        $this->assertContains($record->id, $visibleToA->all());
        $this->assertNotContains($record->id, $visibleToB->all());
    }

    public function test_care_record_list_scoped_to_authenticated_field_staff(): void
    {
        Storage::fake('private');

        $staffA = $this->fieldStaff();
        $staffB = $this->fieldStaff();
        $tree = $this->createTree('PH-050');
        $this->createAssignment($staffA, $tree);

        // Buat record oleh staffA langsung via model (bypass action untuk isolasi test)
        $record = CareRecord::query()->create([
            'tree_id' => $tree->id,
            'performed_by' => $staffA->id,
            'performed_on' => '2026-11-10',
            'action' => 'Penyiraman intensif.',
            'status' => 'draft',
        ]);

        // staffB login ke panel petugas; harus tidak melihat record staffA
        $this->openFieldStaffPanelAs($staffB);

        Livewire::test(ListCareRecords::class)
            ->assertCanNotSeeTableRecords([$record]);
    }

    public function test_manager_cannot_see_field_staff_records_on_petugas_panel(): void
    {
        Storage::fake('private');

        $staff = $this->fieldStaff();
        $manager = $this->manager();
        $tree = $this->createTree('PH-060');
        $this->createAssignment($staff, $tree);

        $record = CareRecord::query()->create([
            'tree_id' => $tree->id,
            'performed_by' => $staff->id,
            'performed_on' => '2026-11-10',
            'action' => 'Pemangkasan.',
            'status' => 'draft',
        ]);

        // Manager tidak boleh akses panel /petugas
        $this->actingAs($manager)
            ->get('/petugas/care-records')
            ->assertForbidden();
    }

    public function test_field_staff_cannot_access_manager_panel(): void
    {
        $staff = $this->fieldStaff();

        $this->actingAs($staff)
            ->get('/app/care-plans')
            ->assertForbidden();
    }
}
