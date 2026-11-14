<?php

namespace Tests\Feature;

use App\Actions\Activities\RegisterForActivity;
use App\Enums\UserRole;
use App\Filament\Citizen\Resources\Activities\Pages\ListActivities as CitizenListActivities;
use App\Filament\Citizen\Resources\Registrations\Pages\ListRegistrations as CitizenListRegistrations;
use App\Models\Activity;
use App\Models\ActivityRegistration;
use App\Models\Program;
use App\Models\Site;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class ActivityRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_citizen_can_register_for_an_open_activity_and_time_is_recorded(): void
    {
        $citizen = $this->citizen();
        $activity = $this->openActivity(quota: 20);

        $registration = app(RegisterForActivity::class)->handle($citizen, $activity);

        // Kriteria 3: Pendaftar tercatat dengan waktu
        $this->assertSame('registered', $registration->status);
        $this->assertSame($activity->getKey(), $registration->activity_id);
        $this->assertSame($citizen->getKey(), $registration->user_id);
        $this->assertNotNull($registration->created_at);

        $this->assertDatabaseHas('activity_registrations', [
            'activity_id' => $activity->getKey(),
            'user_id' => $citizen->getKey(),
            'status' => 'registered',
        ]);

        // Audit log tercatat
        $this->assertDatabaseHas('activity_logs', [
            'actor_id' => $citizen->getKey(),
            'action' => 'activity.registered',
            'subject_type' => Activity::class,
            'subject_id' => $activity->getKey(),
            'from_status' => null,
            'to_status' => 'registered',
        ]);
    }

    public function test_only_one_active_registration_per_account_per_activity(): void
    {
        $citizen = $this->citizen();
        $activity = $this->openActivity(quota: 20);

        // Pendaftaran pertama
        app(RegisterForActivity::class)->handle($citizen, $activity);

        // Kriteria 1: Satu akun hanya satu pendaftaran per kegiatan
        try {
            app(RegisterForActivity::class)->handle($citizen, $activity);
            $this->fail('Seharusnya menolak pendaftaran ganda dari akun yang sama.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('activity', $exception->errors());
            $this->assertSame(
                'Anda sudah terdaftar pada kegiatan ini.',
                $exception->errors()['activity'][0],
            );
        }

        $this->assertDatabaseCount('activity_registrations', 1);
    }

    public function test_registration_is_rejected_when_quota_is_full(): void
    {
        $citizen1 = $this->citizen();
        $citizen2 = $this->citizen();
        $citizen3 = $this->citizen();

        // Kuota diset 2
        $activity = $this->openActivity(quota: 2);

        // Citizen 1 dan 2 berhasil mendaftar
        app(RegisterForActivity::class)->handle($citizen1, $activity);
        app(RegisterForActivity::class)->handle($citizen2, $activity);

        // Kriteria 2: Pendaftaran ditolak bila kuota penuh
        try {
            app(RegisterForActivity::class)->handle($citizen3, $activity);
            $this->fail('Seharusnya menolak pendaftaran saat kuota sudah penuh.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('quota', $exception->errors());
            $this->assertSame(
                'Pendaftaran ditolak karena kuota kegiatan sudah penuh.',
                $exception->errors()['quota'][0],
            );
        }

        $this->assertDatabaseCount('activity_registrations', 2);
    }

    public function test_registration_allowed_when_activity_has_no_quota_limit(): void
    {
        $activity = $this->openActivity(quota: null);

        for ($i = 0; $i < 5; $i++) {
            $citizen = $this->citizen();
            $registration = app(RegisterForActivity::class)->handle($citizen, $activity);
            $this->assertSame('registered', $registration->status);
        }

        $this->assertDatabaseCount('activity_registrations', 5);
    }

    public function test_registration_is_rejected_if_activity_is_not_open(): void
    {
        $citizen = $this->citizen();
        $draftActivity = $this->activityWithStatus('draft');
        $completedActivity = $this->activityWithStatus('completed');
        $cancelledActivity = $this->activityWithStatus('cancelled');

        foreach ([$draftActivity, $completedActivity, $cancelledActivity] as $act) {
            try {
                app(RegisterForActivity::class)->handle($citizen, $act);
                $this->fail("Seharusnya menolak pendaftaran pada kegiatan berstatus {$act->status}");
            } catch (ValidationException $exception) {
                $this->assertArrayHasKey('activity', $exception->errors());
                $this->assertSame(
                    'Pendaftaran hanya dapat dilakukan pada kegiatan yang berstatus terbuka.',
                    $exception->errors()['activity'][0],
                );
            }
        }

        $this->assertDatabaseCount('activity_registrations', 0);
    }

    public function test_non_citizen_roles_cannot_register_for_activity(): void
    {
        $manager = User::factory()->create(['role' => UserRole::Manager, 'is_active' => true]);
        $fieldStaff = User::factory()->create(['role' => UserRole::FieldStaff, 'is_active' => true]);
        $activity = $this->openActivity(quota: 10);

        $this->assertFalse($manager->can('create', ActivityRegistration::class));
        $this->assertFalse($fieldStaff->can('create', ActivityRegistration::class));

        try {
            app(RegisterForActivity::class)->handle($manager, $activity);
            $this->fail('Manager tidak boleh mendaftar kegiatan.');
        } catch (AuthorizationException $e) {
            $this->assertTrue(true);
        }

        try {
            app(RegisterForActivity::class)->handle($fieldStaff, $activity);
            $this->fail('Petugas lapangan tidak boleh mendaftar kegiatan.');
        } catch (AuthorizationException $e) {
            $this->assertTrue(true);
        }
    }

    public function test_citizen_can_re_register_if_previous_registration_was_cancelled_and_quota_available(): void
    {
        $citizen = $this->citizen();
        $activity = $this->openActivity(quota: 5);

        // Simulasi pendaftaran lama yang dibatalkan
        $reg = ActivityRegistration::query()->create([
            'activity_id' => $activity->getKey(),
            'user_id' => $citizen->getKey(),
            'status' => 'cancelled',
        ]);

        $newReg = app(RegisterForActivity::class)->handle($citizen, $activity);

        $this->assertSame('registered', $newReg->status);
        $this->assertSame($reg->getKey(), $newReg->getKey());
        $this->assertDatabaseCount('activity_registrations', 1);
    }

    public function test_filament_citizen_panel_lists_available_activities_and_allows_registration(): void
    {
        $citizen = $this->citizen();
        $activity = $this->openActivity(quota: 15);

        $this->actingAs($citizen);
        Filament::setCurrentPanel('warga');

        Livewire::test(CitizenListActivities::class)
            ->assertSee($activity->title)
            ->callTableAction('register', $activity)
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('activity_registrations', [
            'activity_id' => $activity->getKey(),
            'user_id' => $citizen->getKey(),
            'status' => 'registered',
        ]);
    }

    public function test_citizen_panel_registrations_resource_only_shows_own_registrations(): void
    {
        $citizen = $this->citizen();
        $otherCitizen = $this->citizen();

        $activity1 = $this->openActivity(title: 'Aksi Bersama Pohon Hijau');
        $activity2 = $this->openActivity(title: 'Penanaman Rahasia Warga Lain');

        ActivityRegistration::query()->create([
            'activity_id' => $activity1->getKey(),
            'user_id' => $citizen->getKey(),
            'status' => 'registered',
        ]);

        ActivityRegistration::query()->create([
            'activity_id' => $activity2->getKey(),
            'user_id' => $otherCitizen->getKey(),
            'status' => 'registered',
        ]);

        $this->actingAs($citizen);
        Filament::setCurrentPanel('warga');

        // Warga hanya boleh melihat pendaftarannya sendiri
        Livewire::test(CitizenListRegistrations::class)
            ->assertSee('Aksi Bersama Pohon Hijau')
            ->assertDontSee('Penanaman Rahasia Warga Lain');
    }

    private function citizen(): User
    {
        return User::factory()->create([
            'role' => UserRole::Citizen,
            'is_active' => true,
        ]);
    }

    private function openActivity(?int $quota = 20, string $title = 'Kegiatan Lapangan Terbuka'): Activity
    {
        $manager = User::factory()->create(['role' => UserRole::Manager, 'is_active' => true]);

        $program = Program::query()->create([
            'name' => 'Program Penghijauan Terbuka',
            'objective' => 'Menyediakan lokasi kegiatan.',
            'starts_on' => '2026-11-01',
            'ends_on' => '2027-11-01',
            'status' => 'active',
            'created_by' => $manager->getKey(),
        ]);

        $site = Site::query()->create([
            'program_id' => $program->getKey(),
            'name' => 'Taman Kota Utama',
            'address' => 'Jl. Hijau Teduh No. 1',
        ]);

        return Activity::query()->create([
            'site_id' => $site->getKey(),
            'title' => $title,
            'kind' => 'planting',
            'starts_at' => now()->addDays(3),
            'ends_at' => now()->addDays(3)->addHours(3),
            'quota' => $quota,
            'status' => 'open',
        ]);
    }

    private function activityWithStatus(string $status): Activity
    {
        $manager = User::factory()->create(['role' => UserRole::Manager, 'is_active' => true]);

        $program = Program::query()->create([
            'name' => 'Program Penghijauan Lokasi',
            'objective' => 'Lokasi program.',
            'starts_on' => '2026-11-01',
            'ends_on' => '2027-11-01',
            'status' => 'active',
            'created_by' => $manager->getKey(),
        ]);

        $site = Site::query()->create([
            'program_id' => $program->getKey(),
            'name' => 'Taman Asri',
            'address' => 'Jl. Asri Sejahtera',
        ]);

        return Activity::query()->create([
            'site_id' => $site->getKey(),
            'title' => "Kegiatan Status {$status}",
            'kind' => 'care',
            'starts_at' => now()->addDays(2),
            'ends_at' => now()->addDays(2)->addHours(2),
            'quota' => 10,
            'status' => $status,
        ]);
    }
}
