<?php

namespace Tests\Feature;

use App\Actions\Activities\CorrectAttendance;
use App\Actions\Activities\RecordAttendance;
use App\Enums\UserRole;
use App\Filament\Manager\Resources\Registrations\Pages\ListRegistrations as ManagerListRegistrations;
use App\Models\Activity;
use App\Models\ActivityRegistration;
use App\Models\Program;
use App\Models\Site;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class ActivityAttendanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_record_attendance_for_registered_participant_and_time_is_recorded(): void
    {
        $manager = $this->manager();
        $citizen = $this->citizen();
        $activity = $this->openActivity();

        $registration = ActivityRegistration::query()->create([
            'activity_id' => $activity->getKey(),
            'user_id' => $citizen->getKey(),
            'status' => 'registered',
        ]);

        $recorded = app(RecordAttendance::class)->handle($manager, $registration);

        // Kriteria 1: Hanya pendaftar yang bisa ditandai hadir
        // Kriteria 2: Waktu kehadiran tercatat
        $this->assertSame('attended', $recorded->status);
        $this->assertNotNull($recorded->attended_at);

        $this->assertDatabaseHas('activity_registrations', [
            'id' => $registration->getKey(),
            'status' => 'attended',
        ]);

        // Audit log tercatat
        $this->assertDatabaseHas('activity_logs', [
            'actor_id' => $manager->getKey(),
            'action' => 'activity_registration.attendance_recorded',
            'subject_type' => Activity::class,
            'subject_id' => $activity->getKey(),
            'from_status' => 'registered',
            'to_status' => 'attended',
        ]);
    }

    public function test_manager_can_provide_explicit_attended_time_and_notes(): void
    {
        $manager = $this->manager();
        $citizen = $this->citizen();
        $activity = $this->openActivity();

        $registration = ActivityRegistration::query()->create([
            'activity_id' => $activity->getKey(),
            'user_id' => $citizen->getKey(),
            'status' => 'registered',
        ]);

        $customTime = now()->subMinutes(30)->startOfMinute();

        $recorded = app(RecordAttendance::class)->handle($manager, $registration, [
            'attended_at' => $customTime->format('Y-m-d H:i:s'),
            'notes' => 'Hadir tepat waktu di pos 1.',
        ]);

        $this->assertSame('attended', $recorded->status);
        $this->assertSame($customTime->toIso8601String(), $recorded->attended_at->toIso8601String());

        $log = $activity->activityLogs()->where('action', 'activity_registration.attendance_recorded')->sole();
        $this->assertSame('Hadir tepat waktu di pos 1.', $log->metadata['notes']);
        $this->assertSame($registration->getKey(), $log->metadata['registration_id']);
    }

    public function test_attended_at_cannot_be_in_the_future(): void
    {
        $manager = $this->manager();
        $citizen = $this->citizen();
        $activity = $this->openActivity();

        $registration = ActivityRegistration::query()->create([
            'activity_id' => $activity->getKey(),
            'user_id' => $citizen->getKey(),
            'status' => 'registered',
        ]);

        $futureTime = now()->addHours(2)->format('Y-m-d H:i:s');

        try {
            app(RecordAttendance::class)->handle($manager, $registration, [
                'attended_at' => $futureTime,
            ]);
            $this->fail('Seharusnya gagal jika waktu kehadiran di masa depan.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('attended_at', $e->errors());
        }

        $this->assertSame('registered', $registration->fresh()->status);
        $this->assertNull($registration->fresh()->attended_at);
    }

    public function test_attendance_cannot_be_recorded_for_cancelled_registration(): void
    {
        $manager = $this->manager();
        $citizen = $this->citizen();
        $activity = $this->openActivity();

        $registration = ActivityRegistration::query()->create([
            'activity_id' => $activity->getKey(),
            'user_id' => $citizen->getKey(),
            'status' => 'cancelled',
        ]);

        try {
            app(RecordAttendance::class)->handle($manager, $registration);
            $this->fail('Pendaftaran yang dibatalkan tidak boleh ditandai hadir.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('registration', $e->errors());
            $this->assertSame('Pendaftaran yang telah dibatalkan tidak dapat ditandai hadir.', $e->errors()['registration'][0]);
        }

        $this->assertSame('cancelled', $registration->fresh()->status);
    }

    public function test_attendance_cannot_be_recorded_if_already_marked_attended(): void
    {
        $manager = $this->manager();
        $citizen = $this->citizen();
        $activity = $this->openActivity();

        $registration = ActivityRegistration::query()->create([
            'activity_id' => $activity->getKey(),
            'user_id' => $citizen->getKey(),
            'status' => 'attended',
            'attended_at' => now(),
        ]);

        try {
            app(RecordAttendance::class)->handle($manager, $registration);
            $this->fail('Pendaftaran yang sudah hadir tidak boleh dicatat hadir ulang tanpa koreksi.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('registration', $e->errors());
            $this->assertSame('Peserta sudah ditandai hadir sebelumnya.', $e->errors()['registration'][0]);
        }
    }

    public function test_attendance_cannot_be_recorded_for_cancelled_activity(): void
    {
        $manager = $this->manager();
        $citizen = $this->citizen();
        $activity = $this->cancelledActivity();

        $registration = ActivityRegistration::query()->create([
            'activity_id' => $activity->getKey(),
            'user_id' => $citizen->getKey(),
            'status' => 'registered',
        ]);

        try {
            app(RecordAttendance::class)->handle($manager, $registration);
            $this->fail('Kehadiran tidak boleh dicatat pada kegiatan yang dibatalkan.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('activity', $e->errors());
        }
    }

    public function test_manager_can_correct_attendance_back_to_registered_with_reason(): void
    {
        $manager = $this->manager();
        $citizen = $this->citizen();
        $activity = $this->openActivity();

        $registration = ActivityRegistration::query()->create([
            'activity_id' => $activity->getKey(),
            'user_id' => $citizen->getKey(),
            'status' => 'attended',
            'attended_at' => now()->subHour(),
        ]);

        // Kriteria 3: Status kehadiran bisa dikoreksi
        $corrected = app(CorrectAttendance::class)->handle($manager, $registration, [
            'status' => 'registered',
            'reason' => 'Keliru mencatat hadir, peserta ternyata berhalangan hadir karena sakit.',
        ]);

        $this->assertSame('registered', $corrected->status);
        $this->assertNull($corrected->attended_at);

        $this->assertDatabaseHas('activity_registrations', [
            'id' => $registration->getKey(),
            'status' => 'registered',
            'attended_at' => null,
        ]);

        // Log koreksi tercatat
        $this->assertDatabaseHas('activity_logs', [
            'actor_id' => $manager->getKey(),
            'action' => 'activity_registration.attendance_corrected',
            'subject_type' => Activity::class,
            'subject_id' => $activity->getKey(),
            'from_status' => 'attended',
            'to_status' => 'registered',
        ]);

        $log = $activity->activityLogs()->where('action', 'activity_registration.attendance_corrected')->sole();
        $this->assertSame('Keliru mencatat hadir, peserta ternyata berhalangan hadir karena sakit.', $log->metadata['reason']);
    }

    public function test_manager_can_correct_attended_time_with_reason(): void
    {
        $manager = $this->manager();
        $citizen = $this->citizen();
        $activity = $this->openActivity();

        $oldTime = now()->subHours(2)->startOfMinute();
        $newTime = now()->subMinutes(15)->startOfMinute();

        $registration = ActivityRegistration::query()->create([
            'activity_id' => $activity->getKey(),
            'user_id' => $citizen->getKey(),
            'status' => 'attended',
            'attended_at' => $oldTime,
        ]);

        $corrected = app(CorrectAttendance::class)->handle($manager, $registration, [
            'status' => 'attended',
            'attended_at' => $newTime->format('Y-m-d H:i:s'),
            'reason' => 'Koreksi jam kedatangan peserta sesuai daftar presensi fisik.',
        ]);

        $this->assertSame('attended', $corrected->status);
        $this->assertSame($newTime->toIso8601String(), $corrected->attended_at->toIso8601String());

        $log = $activity->activityLogs()->where('action', 'activity_registration.attendance_corrected')->sole();
        $this->assertSame('Koreksi jam kedatangan peserta sesuai daftar presensi fisik.', $log->metadata['reason']);
        $this->assertSame($oldTime->toIso8601String(), $log->metadata['previous_attended_at']);
        $this->assertSame($newTime->toIso8601String(), $log->metadata['new_attended_at']);
    }

    public function test_correction_requires_valid_reason(): void
    {
        $manager = $this->manager();
        $citizen = $this->citizen();
        $activity = $this->openActivity();

        $registration = ActivityRegistration::query()->create([
            'activity_id' => $activity->getKey(),
            'user_id' => $citizen->getKey(),
            'status' => 'attended',
            'attended_at' => now(),
        ]);

        try {
            app(CorrectAttendance::class)->handle($manager, $registration, [
                'status' => 'registered',
                'reason' => 'abc', // Kurang dari 5 karakter
            ]);
            $this->fail('Alasan koreksi minimal 5 karakter.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('reason', $e->errors());
        }

        try {
            app(CorrectAttendance::class)->handle($manager, $registration, [
                'status' => 'registered',
                'reason' => '',
            ]);
            $this->fail('Alasan koreksi wajib diisi.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('reason', $e->errors());
        }
    }

    public function test_correction_is_rejected_if_registration_is_not_attended(): void
    {
        $manager = $this->manager();
        $citizen = $this->citizen();
        $activity = $this->openActivity();

        $registration = ActivityRegistration::query()->create([
            'activity_id' => $activity->getKey(),
            'user_id' => $citizen->getKey(),
            'status' => 'registered',
        ]);

        try {
            app(CorrectAttendance::class)->handle($manager, $registration, [
                'status' => 'registered',
                'reason' => 'Mencoba koreksi yang belum hadir.',
            ]);
            $this->fail('Hanya status attended yang dapat dikoreksi.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('registration', $e->errors());
        }
    }

    public function test_non_manager_roles_cannot_record_or_correct_attendance(): void
    {
        $citizen = $this->citizen();
        $fieldStaff = User::factory()->create(['role' => UserRole::FieldStaff, 'is_active' => true]);
        $activity = $this->openActivity();

        $registration = ActivityRegistration::query()->create([
            'activity_id' => $activity->getKey(),
            'user_id' => $citizen->getKey(),
            'status' => 'registered',
        ]);

        $this->assertFalse(Gate::forUser($citizen)->allows('recordAttendance', $registration));
        $this->assertFalse(Gate::forUser($fieldStaff)->allows('recordAttendance', $registration));
        $this->assertFalse(Gate::forUser($citizen)->allows('correctAttendance', $registration));
        $this->assertFalse(Gate::forUser($fieldStaff)->allows('correctAttendance', $registration));

        try {
            app(RecordAttendance::class)->handle($citizen, $registration);
            $this->fail('Citizen tidak boleh mencatat kehadiran.');
        } catch (AuthorizationException) {
            $this->assertTrue(true);
        }

        try {
            app(RecordAttendance::class)->handle($fieldStaff, $registration);
            $this->fail('Petugas lapangan tidak boleh mencatat kehadiran.');
        } catch (AuthorizationException) {
            $this->assertTrue(true);
        }
    }

    public function test_filament_manager_panel_lists_registrations_and_allows_recording_attendance(): void
    {
        $manager = $this->manager();
        $citizen = $this->citizen();
        $activity = $this->openActivity(title: 'Aksi Bersih Hutan Kota');

        $registration = ActivityRegistration::query()->create([
            'activity_id' => $activity->getKey(),
            'user_id' => $citizen->getKey(),
            'status' => 'registered',
        ]);

        $this->actingAs($manager);
        Filament::setCurrentPanel('app');

        Livewire::test(ManagerListRegistrations::class)
            ->assertSee($citizen->name)
            ->assertSee('Aksi Bersih Hutan Kota')
            ->callTableAction('recordAttendance', $registration, data: [
                'notes' => 'Hadir di pos pendaftaran.',
            ])
            ->assertHasNoTableActionErrors();

        $this->assertSame('attended', $registration->fresh()->status);
        $this->assertNotNull($registration->fresh()->attended_at);
    }

    public function test_filament_manager_panel_allows_correcting_attendance(): void
    {
        $manager = $this->manager();
        $citizen = $this->citizen();
        $activity = $this->openActivity(title: 'Penanaman Pohon Bakau');

        $registration = ActivityRegistration::query()->create([
            'activity_id' => $activity->getKey(),
            'user_id' => $citizen->getKey(),
            'status' => 'attended',
            'attended_at' => now()->subHour(),
        ]);

        $this->actingAs($manager);
        Filament::setCurrentPanel('app');

        Livewire::test(ManagerListRegistrations::class)
            ->assertSee($citizen->name)
            ->callTableAction('correctAttendance', $registration, data: [
                'status' => 'registered',
                'reason' => 'Koreksi karena salah memilih nama peserta.',
            ])
            ->assertHasNoTableActionErrors();

        $this->assertSame('registered', $registration->fresh()->status);
        $this->assertNull($registration->fresh()->attended_at);
    }

    private function manager(): User
    {
        return User::factory()->create([
            'name' => 'Pengelola Utama',
            'email' => 'manager_att_'.uniqid().'@hijauku.test',
            'role' => UserRole::Manager,
            'is_active' => true,
        ]);
    }

    private function citizen(): User
    {
        return User::factory()->create([
            'name' => 'Warga Peduli '.uniqid(),
            'email' => 'citizen_att_'.uniqid().'@hijauku.test',
            'role' => UserRole::Citizen,
            'is_active' => true,
        ]);
    }

    private function openActivity(string $title = 'Kegiatan Lapangan Presensi'): Activity
    {
        $manager = $this->manager();

        $program = Program::query()->create([
            'name' => 'Program Presensi '.uniqid(),
            'objective' => 'Pengujian kehadiran peserta.',
            'starts_on' => '2026-11-01',
            'ends_on' => '2027-11-01',
            'status' => 'active',
            'created_by' => $manager->getKey(),
        ]);

        $site = Site::query()->create([
            'program_id' => $program->getKey(),
            'name' => 'Hutan Kota Lestari',
            'address' => 'Jl. Lingkar Luar No. 10',
        ]);

        return Activity::query()->create([
            'site_id' => $site->getKey(),
            'title' => $title,
            'kind' => 'care',
            'starts_at' => now()->subHours(2),
            'ends_at' => now()->addHours(2),
            'quota' => 25,
            'status' => 'open',
        ]);
    }

    private function cancelledActivity(): Activity
    {
        $act = $this->openActivity('Kegiatan Dibatalkan');
        $act->update(['status' => 'cancelled']);

        return $act;
    }
}
