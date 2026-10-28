<?php

namespace Tests\Feature;

use App\Actions\Activities\CreateDraftActivity;
use App\Enums\UserRole;
use App\Filament\Manager\Resources\Activities\Pages\CreateActivity;
use App\Models\Activity;
use App\Models\Program;
use App\Models\Site;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class ActivityCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_create_a_draft_activity_and_audit_event(): void
    {
        $manager = $this->manager();
        $site = $this->site($manager);
        $this->openManagerPanelAs($manager);

        Livewire::test(CreateActivity::class)
            ->fillForm([
                'title' => 'Aksi Penanaman Bibit Mangrove',
                'site_id' => $site->getKey(),
                'kind' => 'planting',
                'starts_at' => '2026-11-10 08:00:00',
                'ends_at' => '2026-11-10 12:00:00',
                'quota' => 30,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $activity = Activity::query()->where('title', 'Aksi Penanaman Bibit Mangrove')->sole();

        $this->assertSame('draft', $activity->status);
        $this->assertSame($site->getKey(), $activity->site_id);
        $this->assertSame('planting', $activity->kind);
        $this->assertSame(30, $activity->quota);
        $this->assertSame('2026-11-10 08:00:00', $activity->starts_at->format('Y-m-d H:i:s'));
        $this->assertSame('2026-11-10 12:00:00', $activity->ends_at->format('Y-m-d H:i:s'));

        $this->assertDatabaseHas('activity_logs', [
            'actor_id' => $manager->getKey(),
            'action' => 'activity.created',
            'subject_type' => Activity::class,
            'subject_id' => $activity->getKey(),
            'from_status' => null,
            'to_status' => 'draft',
        ]);
    }

    public function test_activity_form_requires_title_site_kind_and_period(): void
    {
        $this->openManagerPanelAs($this->manager());

        Livewire::test(CreateActivity::class)
            ->fillForm([
                'title' => '',
                'site_id' => null,
                'kind' => '',
                'starts_at' => null,
                'ends_at' => null,
            ])
            ->call('create')
            ->assertHasFormErrors([
                'title' => 'required',
                'site_id' => 'required',
                'kind' => 'required',
                'starts_at' => 'required',
                'ends_at' => 'required',
            ]);

        $this->assertDatabaseCount('activities', 0);
        $this->assertDatabaseCount('activity_logs', 0);
    }

    public function test_activity_end_time_must_be_after_start_time(): void
    {
        $manager = $this->manager();
        $site = $this->site($manager);
        $this->openManagerPanelAs($manager);

        Livewire::test(CreateActivity::class)
            ->fillForm([
                'title' => 'Perawatan Rutin Pohon',
                'site_id' => $site->getKey(),
                'kind' => 'care',
                'starts_at' => '2026-11-10 14:00:00',
                'ends_at' => '2026-11-10 14:00:00',
            ])
            ->call('create')
            ->assertHasFormErrors(['ends_at' => 'after']);

        Livewire::test(CreateActivity::class)
            ->fillForm([
                'title' => 'Perawatan Rutin Pohon',
                'site_id' => $site->getKey(),
                'kind' => 'care',
                'starts_at' => '2026-11-10 15:00:00',
                'ends_at' => '2026-11-10 14:00:00',
            ])
            ->call('create')
            ->assertHasFormErrors(['ends_at' => 'after']);

        $this->assertDatabaseCount('activities', 0);
        $this->assertDatabaseCount('activity_logs', 0);
    }

    public function test_creation_action_rejects_end_time_not_after_start_time(): void
    {
        $manager = $this->manager();
        $site = $this->site($manager);

        try {
            app(CreateDraftActivity::class)->handle($manager, [
                'title' => 'Perawatan Rutin Pohon',
                'site_id' => $site->getKey(),
                'kind' => 'care',
                'starts_at' => '2026-11-10 14:00:00',
                'ends_at' => '2026-11-10 13:00:00',
            ]);

            $this->fail('Action seharusnya menolak waktu selesai yang lebih awal dari waktu mulai.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('ends_at', $exception->errors());
            $this->assertSame(
                'Waktu akhir harus setelah waktu awal.',
                $exception->errors()['ends_at'][0],
            );
        }

        $this->assertDatabaseCount('activities', 0);
        $this->assertDatabaseCount('activity_logs', 0);
    }

    public function test_activity_must_be_bound_to_a_valid_site(): void
    {
        $this->openManagerPanelAs($this->manager());

        Livewire::test(CreateActivity::class)
            ->fillForm([
                'title' => 'Kegiatan Tanpa Lokasi',
                'site_id' => null,
                'kind' => 'care',
                'starts_at' => '2026-11-10 08:00:00',
                'ends_at' => '2026-11-10 10:00:00',
            ])
            ->call('create')
            ->assertHasFormErrors(['site_id' => 'required']);

        try {
            app(CreateDraftActivity::class)->handle($this->manager(), [
                'title' => 'Kegiatan Lokasi Salah',
                'site_id' => 999999,
                'kind' => 'care',
                'starts_at' => '2026-11-10 08:00:00',
                'ends_at' => '2026-11-10 10:00:00',
            ]);

            $this->fail('Action seharusnya menolak site_id yang tidak ada di database.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('site_id', $exception->errors());
            $this->assertSame(
                'Lokasi yang dipilih tidak valid.',
                $exception->errors()['site_id'][0],
            );
        }

        $this->assertDatabaseCount('activities', 0);
    }

    public function test_activity_quota_must_be_positive_integer_when_filled(): void
    {
        $manager = $this->manager();
        $site = $this->site($manager);
        $this->openManagerPanelAs($manager);

        Livewire::test(CreateActivity::class)
            ->fillForm([
                'title' => 'Kegiatan Kuota Nol',
                'site_id' => $site->getKey(),
                'kind' => 'care',
                'starts_at' => '2026-11-10 08:00:00',
                'ends_at' => '2026-11-10 10:00:00',
                'quota' => 0,
            ])
            ->call('create')
            ->assertHasFormErrors(['quota' => 'min']);

        try {
            app(CreateDraftActivity::class)->handle($manager, [
                'title' => 'Kegiatan Kuota Negatif',
                'site_id' => $site->getKey(),
                'kind' => 'care',
                'starts_at' => '2026-11-10 08:00:00',
                'ends_at' => '2026-11-10 10:00:00',
                'quota' => -10,
            ]);

            $this->fail('Action seharusnya menolak kuota bilangan negatif.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('quota', $exception->errors());
            $this->assertSame(
                'Kuota berupa bilangan positif bila diisi.',
                $exception->errors()['quota'][0],
            );
        }

        $this->assertDatabaseCount('activities', 0);
    }

    public function test_activity_can_be_created_without_quota(): void
    {
        $manager = $this->manager();
        $site = $this->site($manager);
        $this->openManagerPanelAs($manager);

        Livewire::test(CreateActivity::class)
            ->fillForm([
                'title' => 'Kegiatan Terbuka Tanpa Batas Kuota',
                'site_id' => $site->getKey(),
                'kind' => 'socialization',
                'starts_at' => '2026-11-12 09:00:00',
                'ends_at' => '2026-11-12 11:30:00',
                'quota' => null,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $activity = Activity::query()->where('title', 'Kegiatan Terbuka Tanpa Batas Kuota')->sole();

        $this->assertNull($activity->quota);
        $this->assertSame('draft', $activity->status);
    }

    public function test_whitespace_only_title_is_rejected(): void
    {
        $manager = $this->manager();
        $site = $this->site($manager);
        $this->openManagerPanelAs($manager);

        Livewire::test(CreateActivity::class)
            ->fillForm([
                'title' => '   ',
                'site_id' => $site->getKey(),
                'kind' => 'care',
                'starts_at' => '2026-11-10 08:00:00',
                'ends_at' => '2026-11-10 10:00:00',
            ])
            ->call('create')
            ->assertHasFormErrors(['title' => 'required']);

        $this->assertDatabaseCount('activities', 0);
    }

    public function test_server_owned_status_ignores_forged_form_attributes(): void
    {
        $manager = $this->manager();
        $site = $this->site($manager);

        $activity = app(CreateDraftActivity::class)->handle($manager, [
            'title' => 'Aksi Langsung Buka',
            'site_id' => $site->getKey(),
            'kind' => 'planting',
            'starts_at' => '2026-11-10 08:00:00',
            'ends_at' => '2026-11-10 10:00:00',
            'quota' => 20,
            'status' => 'open',
        ]);

        $this->assertSame('draft', $activity->status);
    }

    public function test_only_managers_can_open_activity_pages_or_call_creation_action(): void
    {
        $fieldStaff = User::factory()->create([
            'role' => UserRole::FieldStaff,
            'is_active' => true,
        ]);
        $citizen = User::factory()->create([
            'role' => UserRole::Citizen,
            'is_active' => true,
        ]);
        $site = $this->site($this->manager());

        $this->actingAs($fieldStaff)
            ->get('/app/activities')
            ->assertForbidden();

        $this->actingAs($fieldStaff)
            ->get('/app/activities/create')
            ->assertForbidden();

        $this->assertFalse($fieldStaff->can('viewAny', Activity::class));
        $this->assertFalse($citizen->can('viewAny', Activity::class));
        $this->assertFalse($fieldStaff->can('create', Activity::class));
        $this->assertFalse($citizen->can('create', Activity::class));

        $this->expectException(AuthorizationException::class);

        app(CreateDraftActivity::class)->handle($fieldStaff, [
            'title' => 'Kegiatan Ilegal',
            'site_id' => $site->getKey(),
            'kind' => 'care',
            'starts_at' => '2026-11-10 08:00:00',
            'ends_at' => '2026-11-10 10:00:00',
        ]);
    }

    public function test_manager_is_not_allowed_to_update_or_delete_activities_in_this_story(): void
    {
        $manager = $this->manager();
        $activity = new Activity;
        $activity->forceFill(['id' => 99, 'site_id' => 1]);

        $this->assertTrue($manager->can('create', Activity::class));
        $this->assertTrue($manager->can('viewAny', Activity::class));
        $this->assertFalse($manager->can('update', $activity));
        $this->assertFalse($manager->can('delete', $activity));
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
            'name' => 'Program Penghijauan Lokasi',
            'objective' => 'Menyediakan lokasi untuk kegiatan.',
            'starts_on' => '2026-11-01',
            'ends_on' => '2027-11-01',
            'status' => 'active',
            'created_by' => $manager->getKey(),
        ]);

        return Site::query()->create([
            'program_id' => $program->getKey(),
            'name' => 'Taman Kota Utama',
            'address' => 'Jl. Hijau Lestari No. 10',
        ]);
    }

    private function openManagerPanelAs(User $manager): void
    {
        $this->actingAs($manager);
        Filament::setCurrentPanel('app');
    }
}

