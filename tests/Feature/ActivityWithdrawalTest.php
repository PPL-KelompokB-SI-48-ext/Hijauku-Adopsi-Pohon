<?php

namespace Tests\Feature;

use App\Actions\Activities\CancelActivityRegistration;
use App\Actions\Activities\RegisterForActivity;
use App\Enums\UserRole;
use App\Filament\Citizen\Resources\Registrations\Pages\ListRegistrations as CitizenListRegistrations;
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

class ActivityWithdrawalTest extends TestCase
{
    use RefreshDatabase;

    public function test_citizen_can_withdraw_their_own_registration_before_activity_starts(): void
    {
        $citizen = $this->citizen();
        $activity = $this->futureActivity();

        $registration = ActivityRegistration::query()->create([
            'activity_id' => $activity->getKey(),
            'user_id' => $citizen->getKey(),
            'status' => 'registered',
        ]);

        $withdrawn = app(CancelActivityRegistration::class)->handle($citizen, $registration, [
            'reason' => 'Ada keperluan keluarga mendesak.',
        ]);

        // Kriteria 1: Hanya pendaftaran milik sendiri yang bisa ditarik
        // Kriteria 2: Penarikan sebelum kegiatan dimulai
        $this->assertSame('cancelled', $withdrawn->status);
        $this->assertDatabaseHas('activity_registrations', [
            'id' => $registration->getKey(),
            'status' => 'cancelled',
        ]);

        // Audit log tercatat
        $this->assertDatabaseHas('activity_logs', [
            'actor_id' => $citizen->getKey(),
            'action' => 'activity_registration.cancelled',
            'subject_type' => Activity::class,
            'subject_id' => $activity->getKey(),
            'from_status' => 'registered',
            'to_status' => 'cancelled',
        ]);

        $log = $activity->activityLogs()->where('action', 'activity_registration.cancelled')->sole();
        $this->assertSame('Ada keperluan keluarga mendesak.', $log->metadata['reason']);
        $this->assertSame($registration->getKey(), $log->metadata['registration_id']);
    }

    public function test_quota_is_freed_up_and_another_citizen_can_register_after_withdrawal(): void
    {
        $citizenA = $this->citizen();
        $citizenB = $this->citizen();
        // Kegiatan dengan kuota hanya 1 peserta
        $activity = $this->futureActivity(quota: 1);

        // Citizen A mendaftar pertama kali -> kuota penuh
        $regA = app(RegisterForActivity::class)->handle($citizenA, $activity);
        $this->assertSame('registered', $regA->status);

        // Citizen B mencoba mendaftar -> ditolak karena kuota penuh
        try {
            app(RegisterForActivity::class)->handle($citizenB, $activity);
            $this->fail('Seharusnya pendaftaran ditolak karena kuota penuh.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('quota', $e->errors());
        }

        // Kriteria 3: Kuota kembali tersedia setelah penarikan
        // Citizen A menarik pendaftarannya
        app(CancelActivityRegistration::class)->handle($citizenA, $regA);
        $this->assertSame('cancelled', $regA->fresh()->status);

        // Sekarang Citizen B mencoba mendaftar kembali -> harus berhasil karena kuota sudah bebas
        $regB = app(RegisterForActivity::class)->handle($citizenB, $activity);
        $this->assertSame('registered', $regB->status);
        $this->assertSame($citizenB->getKey(), $regB->user_id);
    }

    public function test_citizen_cannot_withdraw_after_activity_has_started(): void
    {
        $citizen = $this->citizen();
        // Kegiatan yang sudah lewat / sedang berlangsung
        $startedActivity = $this->pastActivity();

        $registration = ActivityRegistration::query()->create([
            'activity_id' => $startedActivity->getKey(),
            'user_id' => $citizen->getKey(),
            'status' => 'registered',
        ]);

        // Kriteria 2: Penarikan harus sebelum kegiatan dimulai
        try {
            app(CancelActivityRegistration::class)->handle($citizen, $registration);
            $this->fail('Penarikan seharusnya gagal jika kegiatan sudah dimulai.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('activity', $e->errors());
            $this->assertSame(
                'Pendaftaran tidak dapat ditarik karena kegiatan sudah dimulai atau telah lewat.',
                $e->errors()['activity'][0],
            );
        }

        $this->assertSame('registered', $registration->fresh()->status);
    }

    public function test_citizen_cannot_withdraw_another_citizens_registration(): void
    {
        $citizenA = $this->citizen();
        $citizenB = $this->citizen();
        $activity = $this->futureActivity();

        // Pendaftaran milik Citizen B
        $registrationB = ActivityRegistration::query()->create([
            'activity_id' => $activity->getKey(),
            'user_id' => $citizenB->getKey(),
            'status' => 'registered',
        ]);

        // Kriteria 1: Hanya pendaftaran milik sendiri yang bisa ditarik
        $this->assertFalse(Gate::forUser($citizenA)->allows('cancel', $registrationB));
        $this->assertFalse(Gate::forUser($citizenA)->allows('withdraw', $registrationB));

        try {
            app(CancelActivityRegistration::class)->handle($citizenA, $registrationB);
            $this->fail('Citizen A tidak boleh menarik pendaftaran Citizen B.');
        } catch (AuthorizationException) {
            $this->assertTrue(true);
        }

        $this->assertSame('registered', $registrationB->fresh()->status);
    }

    public function test_manager_and_field_staff_cannot_withdraw_via_citizen_action(): void
    {
        $manager = User::factory()->create(['role' => UserRole::Manager, 'is_active' => true]);
        $fieldStaff = User::factory()->create(['role' => UserRole::FieldStaff, 'is_active' => true]);
        $citizen = $this->citizen();
        $activity = $this->futureActivity();

        $registration = ActivityRegistration::query()->create([
            'activity_id' => $activity->getKey(),
            'user_id' => $citizen->getKey(),
            'status' => 'registered',
        ]);

        $this->assertFalse(Gate::forUser($manager)->allows('cancel', $registration));
        $this->assertFalse(Gate::forUser($fieldStaff)->allows('cancel', $registration));

        try {
            app(CancelActivityRegistration::class)->handle($manager, $registration);
            $this->fail('Manager tidak boleh menarik pendaftaran via aksi warga.');
        } catch (AuthorizationException) {
            $this->assertTrue(true);
        }

        try {
            app(CancelActivityRegistration::class)->handle($fieldStaff, $registration);
            $this->fail('Petugas lapangan tidak boleh menarik pendaftaran warga.');
        } catch (AuthorizationException) {
            $this->assertTrue(true);
        }
    }

    public function test_withdrawal_is_rejected_if_already_attended(): void
    {
        $citizen = $this->citizen();
        $activity = $this->futureActivity();

        $registration = ActivityRegistration::query()->create([
            'activity_id' => $activity->getKey(),
            'user_id' => $citizen->getKey(),
            'status' => 'attended',
            'attended_at' => now(),
        ]);

        try {
            app(CancelActivityRegistration::class)->handle($citizen, $registration);
            $this->fail('Pendaftaran yang sudah hadir tidak boleh ditarik.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('registration', $e->errors());
            $this->assertSame(
                'Pendaftaran tidak dapat ditarik karena Anda sudah tercatat hadir pada kegiatan ini.',
                $e->errors()['registration'][0],
            );
        }
    }

    public function test_withdrawal_is_rejected_if_already_cancelled(): void
    {
        $citizen = $this->citizen();
        $activity = $this->futureActivity();

        $registration = ActivityRegistration::query()->create([
            'activity_id' => $activity->getKey(),
            'user_id' => $citizen->getKey(),
            'status' => 'cancelled',
        ]);

        try {
            app(CancelActivityRegistration::class)->handle($citizen, $registration);
            $this->fail('Pendaftaran yang sudah dibatalkan tidak boleh ditarik lagi.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('registration', $e->errors());
            $this->assertSame('Pendaftaran ini sudah dibatalkan sebelumnya.', $e->errors()['registration'][0]);
        }
    }

    public function test_withdrawal_is_rejected_if_activity_is_cancelled_or_completed(): void
    {
        $citizen = $this->citizen();
        $cancelledActivity = $this->futureActivity();
        $cancelledActivity->update(['status' => 'cancelled']);

        $registration = ActivityRegistration::query()->create([
            'activity_id' => $cancelledActivity->getKey(),
            'user_id' => $citizen->getKey(),
            'status' => 'registered',
        ]);

        try {
            app(CancelActivityRegistration::class)->handle($citizen, $registration);
            $this->fail('Pendaftaran tidak dapat ditarik jika kegiatan dibatalkan.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('activity', $e->errors());
        }
    }

    public function test_filament_citizen_panel_allows_withdrawing_registration(): void
    {
        $citizen = $this->citizen();
        $activity = $this->futureActivity(title: 'Aksi Tanam Bersama Masa Depan');

        $registration = ActivityRegistration::query()->create([
            'activity_id' => $activity->getKey(),
            'user_id' => $citizen->getKey(),
            'status' => 'registered',
        ]);

        $this->actingAs($citizen);
        Filament::setCurrentPanel('warga');

        Livewire::test(CitizenListRegistrations::class)
            ->assertSee('Aksi Tanam Bersama Masa Depan')
            ->callTableAction('cancelRegistration', $registration, data: [
                'reason' => 'Ada jadwal kuliah pengganti.',
            ])
            ->assertHasNoTableActionErrors();

        $this->assertSame('cancelled', $registration->fresh()->status);
    }

    private function citizen(): User
    {
        return User::factory()->create([
            'name' => 'Warga Komunitas '.uniqid(),
            'email' => 'citizen_withdraw_'.uniqid().'@hijauku.test',
            'role' => UserRole::Citizen,
            'is_active' => true,
        ]);
    }

    private function futureActivity(int $quota = 20, string $title = 'Kegiatan Lapangan Masa Depan'): Activity
    {
        $manager = User::factory()->create(['role' => UserRole::Manager, 'is_active' => true]);

        $program = Program::query()->create([
            'name' => 'Program Penarikan '.uniqid(),
            'objective' => 'Pengujian penarikan pendaftaran.',
            'starts_on' => '2026-11-01',
            'ends_on' => '2027-11-01',
            'status' => 'active',
            'created_by' => $manager->getKey(),
        ]);

        $site = Site::query()->create([
            'program_id' => $program->getKey(),
            'name' => 'Taman Hijau Lestari',
            'address' => 'Jl. Hijau Masa Depan No. 5',
        ]);

        return Activity::query()->create([
            'site_id' => $site->getKey(),
            'title' => $title,
            'kind' => 'planting',
            'starts_at' => now()->addDays(5),
            'ends_at' => now()->addDays(5)->addHours(4),
            'quota' => $quota,
            'status' => 'open',
        ]);
    }

    private function pastActivity(): Activity
    {
        $act = $this->futureActivity();
        $act->update([
            'starts_at' => now()->subHours(2),
            'ends_at' => now()->addHours(2),
        ]);

        return $act;
    }
}
