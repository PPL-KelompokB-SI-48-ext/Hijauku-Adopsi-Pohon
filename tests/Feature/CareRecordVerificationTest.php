<?php

namespace Tests\Feature;

use App\Actions\CareRecords\ReturnCareRecord;
use App\Actions\CareRecords\VerifyCareRecord;
use App\Enums\UserRole;
use App\Filament\FieldStaff\Resources\CareRecords\Pages\ListCareRecords as PetugasListCareRecords;
use App\Filament\Manager\Resources\CareRecords\Pages\ListCareRecords as ManagerListCareRecords;
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

class CareRecordVerificationTest extends TestCase
{
    use RefreshDatabase;

    // ------------------------------------------------------------------ helpers

    private function manager(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'role' => UserRole::Manager,
            'is_active' => true,
        ], $attributes));
    }

    private function fieldStaff(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'role' => UserRole::FieldStaff,
            'is_active' => true,
        ], $attributes));
    }

    private function citizen(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'role' => UserRole::Citizen,
            'is_active' => true,
        ], $attributes));
    }

    private function createTree(string $code = 'PH-VRF-01'): Tree
    {
        $program = Program::query()->firstOrCreate(
            ['name' => 'Program Konservasi Hutan'],
            [
                'objective' => 'Penghijauan kota',
                'starts_on' => '2026-01-01',
                'ends_on' => '2026-12-31',
                'status' => 'active',
            ],
        );

        $site = Site::query()->firstOrCreate(
            ['program_id' => $program->id, 'name' => 'Taman Hutan Raya'],
            ['address' => 'Jl. Konservasi No. 10'],
        );

        $plot = Plot::query()->firstOrCreate(
            ['site_id' => $site->id, 'code' => 'P-01'],
            ['capacity' => 100],
        );

        $species = TreeSpecies::query()->firstOrCreate(
            ['name' => 'Beringin'],
            ['care_notes' => 'Perawatan akar dan tanah'],
        );

        return Tree::query()->create([
            'species_id' => $species->id,
            'plot_id' => $plot->id,
            'code' => $code,
            'planted_on' => '2026-03-01',
            'initial_condition' => 'good',
        ]);
    }

    private function createSubmittedRecord(User $performer, ?Tree $tree = null): CareRecord
    {
        $tree = $tree ?? $this->createTree();

        return CareRecord::query()->create([
            'tree_id' => $tree->id,
            'plan_id' => null,
            'performed_by' => $performer->id,
            'performed_on' => '2026-11-12',
            'action' => 'Pembersihan gulma di sekeliling pangkal pohon dan pemberian pupuk kompos.',
            'status' => 'submitted',
        ]);
    }

    // ------------------------------------------------------------------ AC 1 & 3: Verifikasi

    public function test_manager_can_verify_submitted_care_record_from_different_user(): void
    {
        $staff = $this->fieldStaff();
        $manager = $this->manager();
        $record = $this->createSubmittedRecord($staff);

        $this->assertTrue($manager->can('verify', $record));

        $verified = app(VerifyCareRecord::class)->handle($manager, $record);

        $this->assertSame('verified', $verified->status);
        $this->assertSame($manager->id, $verified->verified_by);
        $this->assertNotNull($verified->verified_at);

        $this->assertDatabaseHas('care_records', [
            'id' => $record->id,
            'status' => 'verified',
            'verified_by' => $manager->id,
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'actor_id' => $manager->id,
            'action' => 'care_record.verified',
            'subject_type' => CareRecord::class,
            'subject_id' => $record->id,
            'from_status' => 'submitted',
            'to_status' => 'verified',
        ]);
    }

    public function test_verifier_cannot_be_same_as_reporter_when_verifying(): void
    {
        // AC 1: Verifikator harus berbeda dari pengirim laporan
        $userWhoBothManagesAndReported = $this->manager();
        $record = $this->createSubmittedRecord($userWhoBothManagesAndReported);

        // Policy harus menolak
        $this->assertFalse($userWhoBothManagesAndReported->can('verify', $record));

        $this->expectException(AuthorizationException::class);

        app(VerifyCareRecord::class)->handle($userWhoBothManagesAndReported, $record);
    }

    public function test_only_submitted_records_can_be_verified(): void
    {
        // AC 3: Status: diajukan -> terverifikasi/dikembalikan
        $staff = $this->fieldStaff();
        $manager = $this->manager();
        $tree = $this->createTree();

        // 1. Record draft tidak boleh langsung diverifikasi
        $draftRecord = CareRecord::query()->create([
            'tree_id' => $tree->id,
            'performed_by' => $staff->id,
            'performed_on' => '2026-11-12',
            'action' => 'Tindakan draf.',
            'status' => 'draft',
        ]);

        $this->expectException(ValidationException::class);

        app(VerifyCareRecord::class)->handle($manager, $draftRecord);
    }

    public function test_already_verified_record_cannot_be_verified_again(): void
    {
        $staff = $this->fieldStaff();
        $manager = $this->manager();
        $tree = $this->createTree();

        $verifiedRecord = CareRecord::query()->create([
            'tree_id' => $tree->id,
            'performed_by' => $staff->id,
            'performed_on' => '2026-11-12',
            'action' => 'Tindakan sudah terverifikasi.',
            'status' => 'verified',
            'verified_by' => $manager->id,
            'verified_at' => now(),
        ]);

        $this->expectException(ValidationException::class);

        app(VerifyCareRecord::class)->handle($manager, $verifiedRecord);
    }

    public function test_non_manager_cannot_verify_care_record(): void
    {
        $staff = $this->fieldStaff();
        $anotherStaff = $this->fieldStaff();
        $citizen = $this->citizen();
        $record = $this->createSubmittedRecord($staff);

        $this->assertFalse($anotherStaff->can('verify', $record));
        $this->assertFalse($citizen->can('verify', $record));

        $this->expectException(AuthorizationException::class);

        app(VerifyCareRecord::class)->handle($citizen, $record);
    }

    // ------------------------------------------------------------------ AC 2 & 3: Penolakan / Pengembalian

    public function test_manager_can_return_submitted_care_record_with_reason(): void
    {
        // AC 2 & 3: Penolakan wajib beralasan dan status diajukan -> dikembalikan
        $staff = $this->fieldStaff();
        $manager = $this->manager();
        $record = $this->createSubmittedRecord($staff);

        $returned = app(ReturnCareRecord::class)->handle($manager, $record, [
            'review_note' => 'Foto dokumentasi kurang jelas, mohon perjelas foto kondisi tanah.',
        ]);

        $this->assertSame('returned', $returned->status);
        $this->assertSame($manager->id, $returned->verified_by);
        $this->assertSame('Foto dokumentasi kurang jelas, mohon perjelas foto kondisi tanah.', $returned->review_note);
        $this->assertNotNull($returned->verified_at);

        $this->assertDatabaseHas('care_records', [
            'id' => $record->id,
            'status' => 'returned',
            'verified_by' => $manager->id,
            'review_note' => 'Foto dokumentasi kurang jelas, mohon perjelas foto kondisi tanah.',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'actor_id' => $manager->id,
            'action' => 'care_record.returned',
            'subject_type' => CareRecord::class,
            'subject_id' => $record->id,
            'from_status' => 'submitted',
            'to_status' => 'returned',
        ]);
    }

    public function test_return_requires_reason_and_rejects_empty_reason(): void
    {
        // AC 2: Penolakan wajib beralasan
        $staff = $this->fieldStaff();
        $manager = $this->manager();
        $record = $this->createSubmittedRecord($staff);

        $this->expectException(ValidationException::class);

        app(ReturnCareRecord::class)->handle($manager, $record, [
            'review_note' => '   ', // Hanya whitespace
        ]);
    }

    public function test_return_requires_minimum_length_reason(): void
    {
        // Alasan terlalu pendek (< 5 karakter) harus ditolak
        $staff = $this->fieldStaff();
        $manager = $this->manager();
        $record = $this->createSubmittedRecord($staff);

        $this->expectException(ValidationException::class);

        app(ReturnCareRecord::class)->handle($manager, $record, [
            'review_note' => 'bad',
        ]);
    }

    public function test_verifier_cannot_be_same_as_reporter_when_returning(): void
    {
        // AC 1: Verifikator harus berbeda dari pengirim laporan
        $userWhoBothManagesAndReported = $this->manager();
        $record = $this->createSubmittedRecord($userWhoBothManagesAndReported);

        $this->expectException(AuthorizationException::class);

        app(ReturnCareRecord::class)->handle($userWhoBothManagesAndReported, $record, [
            'review_note' => 'Alasan pengembalian valid.',
        ]);
    }

    public function test_field_staff_can_view_rejection_reason_on_petugas_panel(): void
    {
        // AC 2: Penolakan wajib beralasan dan bisa dibaca petugas
        $staff = $this->fieldStaff();
        $manager = $this->manager();
        $tree = $this->createTree('PH-VRF-99');

        $returnedRecord = CareRecord::query()->create([
            'tree_id' => $tree->id,
            'performed_by' => $staff->id,
            'performed_on' => '2026-11-12',
            'action' => 'Pemangkasan dahan.',
            'status' => 'returned',
            'verified_by' => $manager->id,
            'verified_at' => now(),
            'review_note' => 'Tolong semprot ulang bagian daun bawah dengan fungisida organik.',
        ]);

        $this->actingAs($staff);
        Filament::setCurrentPanel('petugas');

        Livewire::test(PetugasListCareRecords::class)
            ->assertCanSeeTableRecords([$returnedRecord]);

        // Verifikasi bahwa data review_note dapat dibaca di record
        $this->assertSame('Tolong semprot ulang bagian daun bawah dengan fungisida organik.', $returnedRecord->fresh()->review_note);
    }

    // ------------------------------------------------------------------ Manager Panel Livewire UI Tests

    public function test_manager_panel_displays_records_and_actions(): void
    {
        $staff = $this->fieldStaff();
        $manager = $this->manager();
        $tree = $this->createTree('PH-MGR-01');

        $submittedRecord = CareRecord::query()->create([
            'tree_id' => $tree->id,
            'performed_by' => $staff->id,
            'performed_on' => '2026-11-12',
            'action' => 'Penyiraman dan sanitasi.',
            'status' => 'submitted',
        ]);

        $this->actingAs($manager);
        Filament::setCurrentPanel('app');

        Livewire::test(ManagerListCareRecords::class)
            ->assertCanSeeTableRecords([$submittedRecord])
            ->assertTableActionVisible('verify', $submittedRecord)
            ->assertTableActionVisible('return', $submittedRecord)
            ->assertTableActionVisible('view', $submittedRecord);
    }

    public function test_manager_cannot_see_verify_or_return_action_for_their_own_record(): void
    {
        // AC 1: Manager yang sekaligus pengirim tidak boleh melihat tombol aksi verifikasi/kembalikan
        $manager = $this->manager();
        $tree = $this->createTree('PH-MGR-02');

        $ownRecord = CareRecord::query()->create([
            'tree_id' => $tree->id,
            'performed_by' => $manager->id,
            'performed_on' => '2026-11-12',
            'action' => 'Realisasi dicatat oleh manager itu sendiri.',
            'status' => 'submitted',
        ]);

        $this->actingAs($manager);
        Filament::setCurrentPanel('app');

        Livewire::test(ManagerListCareRecords::class)
            ->assertTableActionHidden('verify', $ownRecord)
            ->assertTableActionHidden('return', $ownRecord);
    }

    public function test_manager_can_verify_via_livewire_action(): void
    {
        $staff = $this->fieldStaff();
        $manager = $this->manager();
        $record = $this->createSubmittedRecord($staff);

        $this->actingAs($manager);
        Filament::setCurrentPanel('app');

        Livewire::test(ManagerListCareRecords::class)
            ->callTableAction('verify', $record)
            ->assertHasNoTableActionErrors();

        $this->assertSame('verified', $record->fresh()->status);
        $this->assertSame($manager->id, $record->fresh()->verified_by);
    }

    public function test_manager_can_return_via_livewire_action(): void
    {
        $staff = $this->fieldStaff();
        $manager = $this->manager();
        $record = $this->createSubmittedRecord($staff);

        $this->actingAs($manager);
        Filament::setCurrentPanel('app');

        Livewire::test(ManagerListCareRecords::class)
            ->callTableAction('return', $record, [
                'review_note' => 'Kondisi drainase belum diperbaiki sesuai arahan.',
            ])
            ->assertHasNoTableActionErrors();

        $fresh = $record->fresh();
        $this->assertSame('returned', $fresh->status);
        $this->assertSame('Kondisi drainase belum diperbaiki sesuai arahan.', $fresh->review_note);
        $this->assertSame($manager->id, $fresh->verified_by);
    }
}
