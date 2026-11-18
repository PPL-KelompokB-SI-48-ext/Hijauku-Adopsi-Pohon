<?php

namespace Tests\Feature;

use App\Actions\CareRecords\CorrectCareRecord;
use App\Actions\CareRecords\ResubmitCareRecord;
use App\Actions\CareRecords\ReturnCareRecord;
use App\Actions\CareRecords\VerifyCareRecord;
use App\Enums\UserRole;
use App\Filament\FieldStaff\Resources\CareRecords\Pages\ListCareRecords;
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

class CareRecordCorrectionTest extends TestCase
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

    private function createTree(string $code = 'PH-CRT-01'): Tree
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
            ['site_id' => $site->id, 'code' => 'P-02'],
            ['capacity' => 100],
        );

        $species = TreeSpecies::query()->firstOrCreate(
            ['name' => 'Mahoni'],
            ['care_notes' => 'Penyiraman dan pemangkasan'],
        );

        return Tree::query()->create([
            'species_id' => $species->id,
            'plot_id' => $plot->id,
            'code' => $code,
            'planted_on' => '2026-03-01',
            'initial_condition' => 'good',
        ]);
    }

    private function createVerifiedRecord(User $performer, User $verifier, ?Tree $tree = null): CareRecord
    {
        $tree = $tree ?? $this->createTree();

        return CareRecord::query()->create([
            'tree_id' => $tree->id,
            'plan_id' => null,
            'performed_by' => $performer->id,
            'performed_on' => '2026-11-12',
            'action' => 'Pemupukan NPK 150 gram pada radius 50 cm.',
            'status' => 'verified',
            'verified_by' => $verifier->id,
            'verified_at' => now(),
        ]);
    }

    // ------------------------------------------------------------------ Tahap 1: Data terverifikasi dikoreksi via keputusan tercatat

    public function test_manager_can_correct_verified_record_with_recorded_decision_reason(): void
    {
        $staff = $this->fieldStaff();
        $manager = $this->manager();
        $record = $this->createVerifiedRecord($staff, $manager);

        $updated = app(CorrectCareRecord::class)->handle($manager, $record, [
            'action' => 'Pemupukan NPK 200 gram dan perbaikan cekungan resapan air.',
            'performed_on' => '2026-11-13',
            'reason' => 'Ralat volume pupuk dari 150g menjadi 200g berdasarkan verifikasi nota logistik lapangan.',
        ]);

        $this->assertSame('Pemupukan NPK 200 gram dan perbaikan cekungan resapan air.', $updated->action);
        $this->assertSame('2026-11-13', $updated->performed_on->format('Y-m-d'));

        // Pastikan bukan edit diam-diam: activity log mencatat keputusan koreksi
        $this->assertDatabaseHas('activity_logs', [
            'actor_id' => $manager->id,
            'action' => 'care_record.corrected',
            'subject_type' => CareRecord::class,
            'subject_id' => $record->id,
        ]);

        $log = $record->activityLogs()->where('action', 'care_record.corrected')->latest()->first();
        $this->assertNotNull($log);
        $this->assertSame('Ralat volume pupuk dari 150g menjadi 200g berdasarkan verifikasi nota logistik lapangan.', $log->metadata['decision_reason']);
        $this->assertArrayHasKey('action', $log->metadata['changes']);
    }

    public function test_correction_rejects_empty_reason_to_prevent_silent_edits(): void
    {
        $staff = $this->fieldStaff();
        $manager = $this->manager();
        $record = $this->createVerifiedRecord($staff, $manager);

        $this->expectException(ValidationException::class);

        app(CorrectCareRecord::class)->handle($manager, $record, [
            'action' => 'Tindakan diubah tanpa alasan.',
            'reason' => '   ', // Kosong / spasi saja
        ]);
    }

    public function test_correction_rejects_if_actor_is_the_reporter(): void
    {
        $userWhoReportedAndManages = $this->manager();
        $anotherManager = $this->manager();
        $record = $this->createVerifiedRecord($userWhoReportedAndManages, $anotherManager);

        $this->expectException(AuthorizationException::class);

        app(CorrectCareRecord::class)->handle($userWhoReportedAndManages, $record, [
            'action' => 'Pengirim mencoba koreksi laporannya sendiri.',
            'reason' => 'Alasan keputusan ada tapi aktor adalah pengirim.',
        ]);
    }

    public function test_non_manager_cannot_correct_record(): void
    {
        $staff = $this->fieldStaff();
        $manager = $this->manager();
        $citizen = $this->citizen();
        $record = $this->createVerifiedRecord($staff, $manager);

        $this->expectException(AuthorizationException::class);

        app(CorrectCareRecord::class)->handle($citizen, $record, [
            'action' => 'Warga tidak boleh koreksi.',
            'reason' => 'Alasan keputusan valid.',
        ]);
    }

    public function test_manager_can_return_verified_record_if_found_problematic(): void
    {
        // Pengelola dapat mengembalikan data terverifikasi yang bermasalah dengan alasan tercatat
        $staff = $this->fieldStaff();
        $manager = $this->manager();
        $record = $this->createVerifiedRecord($staff, $manager);

        $returned = app(ReturnCareRecord::class)->handle($manager, $record, [
            'review_note' => 'Ditemukan bukti fisik pohon belum dipangkas, verifikasi dicabut dan dikembalikan ke petugas.',
        ]);

        $this->assertSame('returned', $returned->status);
        $this->assertSame('Ditemukan bukti fisik pohon belum dipangkas, verifikasi dicabut dan dikembalikan ke petugas.', $returned->review_note);

        $this->assertDatabaseHas('activity_logs', [
            'actor_id' => $manager->id,
            'action' => 'care_record.returned',
            'subject_id' => $record->id,
            'from_status' => 'verified',
            'to_status' => 'returned',
        ]);
    }

    public function test_manager_can_execute_correct_action_via_livewire(): void
    {
        $staff = $this->fieldStaff();
        $manager = $this->manager();
        $record = $this->createVerifiedRecord($staff, $manager);

        $this->actingAs($manager);
        Filament::setCurrentPanel('app');

        Livewire::test(ManagerListCareRecords::class)
            ->assertTableActionVisible('correct', $record)
            ->callTableAction('correct', $record, [
                'action' => 'Pemupukan diperbarui dan disiram air secukupnya.',
                'performed_on' => '2026-11-14',
                'reason' => 'Keputusan koreksi teknis: menambahkan catatan penyiraman air.',
            ])
            ->assertHasNoTableActionErrors();

        $fresh = $record->fresh();
        $this->assertSame('Pemupukan diperbarui dan disiram air secukupnya.', $fresh->action);
    }

    // ------------------------------------------------------------------ Tahap 2: Riwayat verifikasi bisa diaudit

    public function test_full_lifecycle_generates_auditable_verification_trail(): void
    {
        $staff = $this->fieldStaff();
        $manager = $this->manager();
        $tree = $this->createTree('PH-AUDIT-01');

        // 1. Dibuat oleh petugas
        $record = CareRecord::query()->create([
            'tree_id' => $tree->id,
            'performed_by' => $staff->id,
            'performed_on' => '2026-11-12',
            'action' => 'Pemupukan dasar.',
            'status' => 'submitted',
        ]);
        $record->activityLogs()->create([
            'actor_id' => $staff->id,
            'action' => 'care_record.created',
            'from_status' => null,
            'to_status' => 'submitted',
        ]);

        // 2. Diverifikasi oleh pengelola
        app(VerifyCareRecord::class)->handle($manager, $record);

        // 3. Dikoreksi oleh pengelola dengan keputusan tercatat
        app(CorrectCareRecord::class)->handle($manager, $record, [
            'action' => 'Pemupukan dasar 200 gram.',
            'reason' => 'Koreksi volume pupuk yang sebenarnya digunakan.',
        ]);

        // 4. Dikembalikan karena ada temuan masalah pada pohon
        app(ReturnCareRecord::class)->handle($manager, $record, [
            'review_note' => 'Pohon menunjukkan gejala daun menguning pasca pemupukan, mohon cek keasaman tanah.',
        ]);

        // Verifikasi relasi auditTrail
        $trail = $record->auditTrail()->get();
        $this->assertCount(4, $trail);
        $this->assertSame('care_record.created', $trail[0]->action);
        $this->assertSame('care_record.verified', $trail[1]->action);
        $this->assertSame('care_record.corrected', $trail[2]->action);
        $this->assertSame('care_record.returned', $trail[3]->action);

        // Verifikasi getAuditSummary
        $summary = $record->getAuditSummary();
        $this->assertCount(4, $summary);

        $this->assertSame('Laporan Diajukan', $summary[0]['action']);
        $this->assertSame('Verifikasi Diterima', $summary[1]['action']);
        $this->assertSame('Koreksi Data Tercatat', $summary[2]['action']);
        $this->assertSame('Dikembalikan untuk Perbaikan', $summary[3]['action']);

        $this->assertSame('Koreksi volume pupuk yang sebenarnya digunakan.', $summary[2]['note']);
        $this->assertSame('Pohon menunjukkan gejala daun menguning pasca pemupukan, mohon cek keasaman tanah.', $summary[3]['note']);
    }

    // ------------------------------------------------------------------ Tahap 3: Petugas mendapat status yang jelas

    public function test_field_staff_can_resubmit_returned_record_with_corrections(): void
    {
        $staff = $this->fieldStaff();
        $manager = $this->manager();
        $tree = $this->createTree('PH-RESUBMIT-01');

        $record = CareRecord::query()->create([
            'tree_id' => $tree->id,
            'performed_by' => $staff->id,
            'performed_on' => '2026-11-12',
            'action' => 'Penyiraman 1 liter.',
            'status' => 'returned',
            'verified_by' => $manager->id,
            'review_note' => 'Penyiraman musim kemarau minimal 5 liter per pohon.',
        ]);

        $resubmitted = app(ResubmitCareRecord::class)->handle($staff, $record, [
            'action' => 'Penyiraman diperbanyak menjadi 5 liter di sekeliling perakaran dan diberi mulsa daun.',
        ]);

        $this->assertSame('submitted', $resubmitted->status);
        $this->assertSame('Penyiraman diperbanyak menjadi 5 liter di sekeliling perakaran dan diberi mulsa daun.', $resubmitted->action);

        $this->assertDatabaseHas('activity_logs', [
            'actor_id' => $staff->id,
            'action' => 'care_record.resubmitted',
            'subject_id' => $record->id,
            'from_status' => 'returned',
            'to_status' => 'submitted',
        ]);
    }

    public function test_cannot_resubmit_if_not_returned_status(): void
    {
        $staff = $this->fieldStaff();
        $tree = $this->createTree('PH-RESUBMIT-02');

        $record = CareRecord::query()->create([
            'tree_id' => $tree->id,
            'performed_by' => $staff->id,
            'performed_on' => '2026-11-12',
            'action' => 'Penyiraman.',
            'status' => 'submitted',
        ]);

        $this->expectException(AuthorizationException::class);

        app(ResubmitCareRecord::class)->handle($staff, $record, [
            'action' => 'Mencoba ajukan ulang padahal belum dikembalikan.',
        ]);
    }

    public function test_cannot_resubmit_by_different_staff(): void
    {
        $staffA = $this->fieldStaff();
        $staffB = $this->fieldStaff();
        $tree = $this->createTree('PH-RESUBMIT-03');

        $record = CareRecord::query()->create([
            'tree_id' => $tree->id,
            'performed_by' => $staffA->id,
            'performed_on' => '2026-11-12',
            'action' => 'Penyiraman.',
            'status' => 'returned',
            'review_note' => 'Perbaiki foto.',
        ]);

        $this->expectException(AuthorizationException::class);

        app(ResubmitCareRecord::class)->handle($staffB, $record, [
            'action' => 'Staff lain mencoba ajukan ulang.',
        ]);
    }

    public function test_field_staff_can_execute_resubmit_via_livewire_action(): void
    {
        $staff = $this->fieldStaff();
        $manager = $this->manager();
        $tree = $this->createTree('PH-RESUBMIT-04');

        $record = CareRecord::query()->create([
            'tree_id' => $tree->id,
            'performed_by' => $staff->id,
            'performed_on' => '2026-11-12',
            'action' => 'Pemangkasan dahan bawah.',
            'status' => 'returned',
            'verified_by' => $manager->id,
            'review_note' => 'Harap rapikan potongan bekas pangkasan.',
        ]);

        $this->actingAs($staff);
        Filament::setCurrentPanel('petugas');

        Livewire::test(ListCareRecords::class)
            ->assertTableActionVisible('resubmit', $record)
            ->callTableAction('resubmit', $record, [
                'action' => 'Pemangkasan dahan bawah dan pembersihan seluruh bekas potongan ranting.',
                'performed_on' => '2026-11-13',
            ])
            ->assertHasNoTableActionErrors();

        $fresh = $record->fresh();
        $this->assertSame('submitted', $fresh->status);
        $this->assertSame('Pemangkasan dahan bawah dan pembersihan seluruh bekas potongan ranting.', $fresh->action);
    }
}
