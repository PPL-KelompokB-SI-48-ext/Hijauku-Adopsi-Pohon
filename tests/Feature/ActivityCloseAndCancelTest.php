<?php

namespace Tests\Feature;

use App\Actions\Activities\CancelActivity;
use App\Actions\Activities\CloseActivity;
use App\Enums\UserRole;
use App\Filament\Manager\Resources\Activities\Pages\ListActivities;
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

class ActivityCloseAndCancelTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_cancel_an_open_activity_and_all_active_participants_become_cancelled(): void
    {
        $manager = $this->manager();
        $site = $this->site($manager);

        $activity = Activity::query()->create([
            'site_id' => $site->getKey(),
            'title' => 'Aksi Tanam Bakau Pesisir',
            'kind' => 'planting',
            'starts_at' => now()->addDays(5),
            'ends_at' => now()->addDays(5)->addHours(4),
            'quota' => 30,
            'status' => 'open',
        ]);

        $citizen1 = User::factory()->create(['role' => UserRole::Citizen, 'is_active' => true]);
        $citizen2 = User::factory()->create(['role' => UserRole::Citizen, 'is_active' => true]);
        $citizen3 = User::factory()->create(['role' => UserRole::Citizen, 'is_active' => true]);

        $reg1 = ActivityRegistration::query()->create([
            'activity_id' => $activity->getKey(),
            'user_id' => $citizen1->getKey(),
            'status' => 'registered',
        ]);
        $reg2 = ActivityRegistration::query()->create([
            'activity_id' => $activity->getKey(),
            'user_id' => $citizen2->getKey(),
            'status' => 'registered',
        ]);
        $reg3 = ActivityRegistration::query()->create([
            'activity_id' => $activity->getKey(),
            'user_id' => $citizen3->getKey(),
            'status' => 'cancelled', // sudah cancel duluan
        ]);

        $cancelled = app(CancelActivity::class)->handle($manager, $activity, [
            'reason' => 'Cuaca ekstrem dan gelombang tinggi di lokasi pesisir.',
        ]);

        $this->assertSame('cancelled', $cancelled->status);

        $this->assertDatabaseHas('activities', [
            'id' => $activity->getKey(),
            'status' => 'cancelled',
        ]);

        // Kriteria 2: Peserta yang sudah daftar mendapat status yang tepat (cancelled)
        $this->assertSame('cancelled', $reg1->fresh()->status);
        $this->assertSame('cancelled', $reg2->fresh()->status);
        $this->assertSame('cancelled', $reg3->fresh()->status);

        // Kriteria 3: Pembatalan tercatat dengan alasan
        $this->assertDatabaseHas('activity_logs', [
            'actor_id' => $manager->getKey(),
            'action' => 'activity.cancelled',
            'subject_type' => Activity::class,
            'subject_id' => $activity->getKey(),
            'from_status' => 'open',
            'to_status' => 'cancelled',
        ]);

        $log = $activity->activityLogs()->where('action', 'activity.cancelled')->sole();
        $this->assertSame('Cuaca ekstrem dan gelombang tinggi di lokasi pesisir.', $log->metadata['reason']);
        $this->assertSame(2, $log->metadata['affected_registrations']);
    }

    public function test_manager_can_cancel_a_draft_activity_with_reason(): void
    {
        $manager = $this->manager();
        $site = $this->site($manager);

        $activity = Activity::query()->create([
            'site_id' => $site->getKey(),
            'title' => 'Rencana Sosialisasi Pupuk',
            'kind' => 'socialization',
            'starts_at' => now()->addDays(7),
            'ends_at' => now()->addDays(7)->addHours(2),
            'quota' => 20,
            'status' => 'draft',
        ]);

        $cancelled = app(CancelActivity::class)->handle($manager, $activity, [
            'reason' => 'Pembatalan karena perubahan jadwal dinas lingkungan hidup.',
        ]);

        $this->assertSame('cancelled', $cancelled->status);

        $this->assertDatabaseHas('activity_logs', [
            'actor_id' => $manager->getKey(),
            'action' => 'activity.cancelled',
            'subject_id' => $activity->getKey(),
            'from_status' => 'draft',
            'to_status' => 'cancelled',
        ]);
    }

    public function test_cancellation_is_rejected_if_activity_is_already_completed(): void
    {
        $manager = $this->manager();
        $site = $this->site($manager);

        $completedActivity = Activity::query()->create([
            'site_id' => $site->getKey(),
            'title' => 'Kegiatan Selesai Dilaksanakan',
            'kind' => 'care',
            'starts_at' => now()->subDays(5),
            'ends_at' => now()->subDays(5)->addHours(3),
            'quota' => 15,
            'status' => 'completed',
        ]);

        // Kriteria 1: Pembatalan hanya sebelum kegiatan selesai
        $this->assertFalse($manager->can('cancel', $completedActivity));

        try {
            app(CancelActivity::class)->handle($manager, $completedActivity, [
                'reason' => 'Mencoba membatalkan kegiatan yang sudah selesai.',
            ]);
            $this->fail('Seharusnya gagal membatalkan kegiatan berstatus completed.');
        } catch (\Throwable $e) {
            $this->assertTrue(
                $e instanceof AuthorizationException || $e instanceof ValidationException
            );
        }

        $completedActivity->refresh();
        $this->assertSame('completed', $completedActivity->status);
    }

    public function test_cancellation_is_rejected_if_activity_is_already_cancelled(): void
    {
        $manager = $this->manager();
        $site = $this->site($manager);

        $cancelledActivity = Activity::query()->create([
            'site_id' => $site->getKey(),
            'title' => 'Kegiatan Sudah Batal',
            'kind' => 'care',
            'starts_at' => now()->addDays(2),
            'ends_at' => now()->addDays(2)->addHours(2),
            'quota' => 10,
            'status' => 'cancelled',
        ]);

        $this->assertFalse($manager->can('cancel', $cancelledActivity));

        try {
            app(CancelActivity::class)->handle($manager, $cancelledActivity, [
                'reason' => 'Mencoba membatalkan ulang.',
            ]);
            $this->fail('Seharusnya gagal membatalkan kegiatan yang sudah cancelled.');
        } catch (\Throwable $e) {
            $this->assertTrue(
                $e instanceof AuthorizationException || $e instanceof ValidationException
            );
        }
    }

    public function test_cancellation_requires_a_valid_reason(): void
    {
        $manager = $this->manager();
        $site = $this->site($manager);

        $activity = Activity::query()->create([
            'site_id' => $site->getKey(),
            'title' => 'Kegiatan Tanpa Alasan Batal',
            'kind' => 'care',
            'starts_at' => now()->addDays(2),
            'ends_at' => now()->addDays(2)->addHours(2),
            'quota' => 10,
            'status' => 'open',
        ]);

        try {
            app(CancelActivity::class)->handle($manager, $activity, [
                'reason' => '',
            ]);
            $this->fail('Action seharusnya menolak pembatalan tanpa alasan.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('reason', $exception->errors());
            $this->assertSame(
                'Alasan pembatalan kegiatan wajib diisi.',
                $exception->errors()['reason'][0],
            );
        }

        try {
            app(CancelActivity::class)->handle($manager, $activity, [
                'reason' => 'abc', // kurang dari 5 karakter
            ]);
            $this->fail('Action seharusnya menolak alasan kurang dari 5 karakter.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('reason', $exception->errors());
            $this->assertSame(
                'Alasan pembatalan kegiatan minimal 5 karakter.',
                $exception->errors()['reason'][0],
            );
        }
    }

    public function test_manager_can_close_an_activity_and_audit_event_is_recorded_with_reason(): void
    {
        $manager = $this->manager();
        $site = $this->site($manager);

        $activity = Activity::query()->create([
            'site_id' => $site->getKey(),
            'title' => 'Kegiatan Selesai Penanaman',
            'kind' => 'planting',
            'starts_at' => now()->subDay(),
            'ends_at' => now()->subDay()->addHours(3),
            'quota' => 25,
            'status' => 'open',
        ]);

        $closed = app(CloseActivity::class)->handle($manager, $activity, [
            'reason' => 'Penanaman 100 bibit mahoni telah selesai dilaksanakan dengan sukses.',
        ]);

        $this->assertSame('completed', $closed->status);

        $this->assertDatabaseHas('activities', [
            'id' => $activity->getKey(),
            'status' => 'completed',
        ]);

        // Kriteria 3: Penutupan tercatat dengan alasan
        $this->assertDatabaseHas('activity_logs', [
            'actor_id' => $manager->getKey(),
            'action' => 'activity.completed',
            'subject_type' => Activity::class,
            'subject_id' => $activity->getKey(),
            'from_status' => 'open',
            'to_status' => 'completed',
        ]);

        $log = $activity->activityLogs()->where('action', 'activity.completed')->sole();
        $this->assertSame('Penanaman 100 bibit mahoni telah selesai dilaksanakan dengan sukses.', $log->metadata['reason']);
    }

    public function test_closing_an_already_completed_or_cancelled_activity_is_rejected(): void
    {
        $manager = $this->manager();
        $site = $this->site($manager);

        $completedActivity = Activity::query()->create([
            'site_id' => $site->getKey(),
            'title' => 'Kegiatan Sudah Completed',
            'kind' => 'care',
            'starts_at' => now()->subDays(2),
            'ends_at' => now()->subDays(2)->addHours(2),
            'quota' => 10,
            'status' => 'completed',
        ]);

        $cancelledActivity = Activity::query()->create([
            'site_id' => $site->getKey(),
            'title' => 'Kegiatan Sudah Batal',
            'kind' => 'care',
            'starts_at' => now()->subDays(2),
            'ends_at' => now()->subDays(2)->addHours(2),
            'quota' => 10,
            'status' => 'cancelled',
        ]);

        $this->assertFalse($manager->can('close', $completedActivity));
        $this->assertFalse($manager->can('close', $cancelledActivity));

        try {
            app(CloseActivity::class)->handle($manager, $completedActivity, [
                'reason' => 'Penutupan ulang.',
            ]);
            $this->fail('Seharusnya gagal menutup kegiatan yang sudah selesai.');
        } catch (\Throwable $e) {
            $this->assertTrue(
                $e instanceof AuthorizationException || $e instanceof ValidationException
            );
        }

        try {
            app(CloseActivity::class)->handle($manager, $cancelledActivity, [
                'reason' => 'Menutup kegiatan yang sudah batal.',
            ]);
            $this->fail('Seharusnya gagal menutup kegiatan yang sudah batal.');
        } catch (\Throwable $e) {
            $this->assertTrue(
                $e instanceof AuthorizationException || $e instanceof ValidationException
            );
        }
    }

    public function test_non_managers_cannot_close_or_cancel_activities(): void
    {
        $manager = $this->manager();
        $site = $this->site($manager);

        $activity = Activity::query()->create([
            'site_id' => $site->getKey(),
            'title' => 'Kegiatan Terbuka',
            'kind' => 'care',
            'starts_at' => now()->addDays(2),
            'ends_at' => now()->addDays(2)->addHours(2),
            'quota' => 10,
            'status' => 'open',
        ]);

        $fieldStaff = User::factory()->create([
            'role' => UserRole::FieldStaff,
            'is_active' => true,
        ]);
        $citizen = User::factory()->create([
            'role' => UserRole::Citizen,
            'is_active' => true,
        ]);

        $this->assertFalse($fieldStaff->can('close', $activity));
        $this->assertFalse($citizen->can('close', $activity));
        $this->assertFalse($fieldStaff->can('cancel', $activity));
        $this->assertFalse($citizen->can('cancel', $activity));

        $this->expectException(AuthorizationException::class);
        app(CancelActivity::class)->handle($fieldStaff, $activity, [
            'reason' => 'Petugas mencoba membatalkan kegiatan.',
        ]);
    }

    public function test_filament_table_close_and_cancel_actions_work_via_livewire(): void
    {
        $manager = $this->manager();
        $site = $this->site($manager);

        $activity1 = Activity::query()->create([
            'site_id' => $site->getKey(),
            'title' => 'Kegiatan Akan Ditutup',
            'kind' => 'care',
            'starts_at' => now()->subDay(),
            'ends_at' => now()->subDay()->addHours(2),
            'quota' => 10,
            'status' => 'open',
        ]);

        $activity2 = Activity::query()->create([
            'site_id' => $site->getKey(),
            'title' => 'Kegiatan Akan Dibatalkan',
            'kind' => 'planting',
            'starts_at' => now()->addDays(3),
            'ends_at' => now()->addDays(3)->addHours(3),
            'quota' => 20,
            'status' => 'open',
        ]);

        $this->actingAs($manager);
        Filament::setCurrentPanel('app');

        // Test Action Close
        Livewire::test(ListActivities::class)
            ->callTableAction('close', $activity1, data: [
                'reason' => 'Kegiatan selesai terlaksana tanpa hambatan.',
            ])
            ->assertHasNoTableActionErrors();

        $activity1->refresh();
        $this->assertSame('completed', $activity1->status);

        // Test Action Cancel
        Livewire::test(ListActivities::class)
            ->callTableAction('cancel', $activity2, data: [
                'reason' => 'Kegiatan dibatalkan karena izin lokasi dicabut.',
            ])
            ->assertHasNoTableActionErrors();

        $activity2->refresh();
        $this->assertSame('cancelled', $activity2->status);
    }

    private function manager(): User
    {
        return User::factory()->create([
            'role' => UserRole::Manager,
            'is_active' => true,
        ]);
    }

    private function site(User $manager): Site
    {
        $program = Program::query()->create([
            'name' => 'Program Penghijauan Lapangan',
            'objective' => 'Menyediakan lokasi kegiatan.',
            'starts_on' => '2026-11-01',
            'ends_on' => '2027-11-01',
            'status' => 'active',
            'created_by' => $manager->getKey(),
        ]);

        return Site::query()->create([
            'program_id' => $program->getKey(),
            'name' => 'Taman Kota Selatan',
            'address' => 'Jl. Selatan Sejuk No. 12',
        ]);
    }
}

