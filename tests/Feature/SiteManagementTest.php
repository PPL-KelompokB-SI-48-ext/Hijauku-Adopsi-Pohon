<?php

namespace Tests\Feature;

use App\Actions\Sites\CreateSite;
use App\Actions\Sites\DeleteUnusedSite;
use App\Actions\Sites\UpdateSite;
use App\Enums\UserRole;
use App\Filament\Manager\Resources\Sites\Pages\CreateSite as CreateSitePage;
use App\Filament\Manager\Resources\Sites\Pages\ListSites;
use App\Models\Activity;
use App\Models\Plot;
use App\Models\Program;
use App\Models\Site;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class SiteManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_create_a_location_attached_to_a_program(): void
    {
        $manager = $this->manager();
        $program = $this->program($manager, 'Program Hijau');
        $this->openManagerPanelAs($manager);

        Livewire::test(CreateSitePage::class)
            ->fillForm([
                'program_id' => $program->getKey(),
                'name' => '  Taman RW 04  ',
                'address' => '  Jalan Melati, Bandung  ',
                'latitude' => '-6.8900000',
                'longitude' => '107.6100000',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('sites', [
            'program_id' => $program->getKey(),
            'name' => 'Taman RW 04',
            'address' => 'Jalan Melati, Bandung',
            'latitude' => '-6.8900000',
            'longitude' => '107.6100000',
        ]);
    }

    public function test_location_creation_requires_a_program_name_address_and_valid_coordinates(): void
    {
        $manager = $this->manager();
        $program = $this->program($manager, 'Program Validasi');
        $this->openManagerPanelAs($manager);

        Livewire::test(CreateSitePage::class)
            ->fillForm([
                'program_id' => null,
                'name' => '   ',
                'address' => '   ',
                'latitude' => '91',
                'longitude' => '181',
            ])
            ->call('create')
            ->assertHasFormErrors([
                'program_id' => 'required',
                'name' => 'required',
                'address' => 'required',
                'latitude' => 'max',
                'longitude' => 'max',
            ]);

        try {
            app(CreateSite::class)->handle($manager, [
                'program_id' => $program->getKey(),
                'name' => 'Lokasi tidak valid',
                'address' => 'Alamat uji',
                'latitude' => '90.00000001',
                'longitude' => '107.61',
            ]);

            $this->fail('Latitude di luar rentang seharusnya ditolak oleh action.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('latitude', $exception->errors());
        }

        $this->assertDatabaseCount('sites', 0);
    }

    public function test_only_managers_can_open_or_create_locations(): void
    {
        $fieldStaff = User::factory()->create([
            'role' => UserRole::FieldStaff,
            'is_active' => true,
        ]);

        $this->actingAs($fieldStaff)
            ->get('/app/sites')
            ->assertForbidden();

        $this->assertFalse($fieldStaff->can('viewAny', Site::class));

        $this->expectException(AuthorizationException::class);

        app(CreateSite::class)->handle($fieldStaff, [
            'program_id' => 1,
            'name' => 'Lokasi tidak sah',
            'address' => 'Alamat uji',
        ]);
    }

    public function test_manager_can_filter_search_and_page_locations(): void
    {
        $manager = $this->manager();
        $programA = $this->program($manager, 'Program A');
        $programB = $this->program($manager, 'Program B');
        $siteA = $this->site($programA, 'Taman Utara');
        $siteB = $this->site($programB, 'Lapangan Selatan');
        $this->openManagerPanelAs($manager);

        Livewire::test(ListSites::class)
            ->filterTable('program_id', $programA->getKey())
            ->assertCanSeeTableRecords([$siteA])
            ->assertCanNotSeeTableRecords([$siteB])
            ->searchTable('Utara')
            ->assertCanSeeTableRecords([$siteA])
            ->assertCanNotSeeTableRecords([$siteB]);

        $sites = collect(range(1, 21))
            ->map(fn (int $number): Site => $this->site($programA, 'Page '.str_pad((string) $number, 2, '0', STR_PAD_LEFT)));

        Livewire::test(ListSites::class)
            ->filterTable('program_id', $programA->getKey())
            ->searchTable('Page')
            ->assertCanSeeTableRecords($sites->take(20)->all())
            ->assertCanNotSeeTableRecords([$sites->last()]);
    }

    public function test_location_change_is_audited_with_actor_reason_and_before_after_values(): void
    {
        $manager = $this->manager();
        $originalProgram = $this->program($manager, 'Program Lama');
        $newProgram = $this->program($manager, 'Program Baru');
        $site = $this->site($originalProgram, 'Kebun RW 02', [
            'address' => 'Jalan Lama',
            'latitude' => '-6.9000000',
            'longitude' => '107.6000000',
        ]);

        $updated = app(UpdateSite::class)->handle($manager, $site, [
            'program_id' => $newProgram->getKey(),
            'name' => 'Kebun RW 02',
            'address' => 'Jalan Baru',
            'latitude' => '-6.9000000',
            'longitude' => '107.6000000',
            'reason' => 'Perubahan induk dan alamat sudah disetujui.',
        ]);

        $log = $updated->activityLogs()->sole();

        $this->assertSame($newProgram->getKey(), $updated->program_id);
        $this->assertSame($manager->getKey(), $log->actor_id);
        $this->assertSame('site.updated', $log->action);
        $this->assertNotNull($log->created_at);
        $this->assertSame('Perubahan induk dan alamat sudah disetujui.', $log->metadata['reason']);
        $this->assertSame((string) $originalProgram->getKey(), (string) $log->metadata['before']['program_id']);
        $this->assertSame((string) $newProgram->getKey(), (string) $log->metadata['after']['program_id']);
        $this->assertSame('Jalan Lama', $log->metadata['before']['address']);
        $this->assertSame('Jalan Baru', $log->metadata['after']['address']);
    }

    public function test_parent_program_cannot_change_after_a_plot_uses_the_site(): void
    {
        $manager = $this->manager();
        $originalProgram = $this->program($manager, 'Program Petak');
        $newProgram = $this->program($manager, 'Program Pengganti');
        $site = $this->site($originalProgram, 'Lokasi Berpetak');
        Plot::query()->create([
            'site_id' => $site->getKey(),
            'code' => 'A-01',
            'capacity' => 10,
            'status' => 'open',
        ]);

        try {
            app(UpdateSite::class)->handle($manager, $site, [
                'program_id' => $newProgram->getKey(),
                'name' => $site->name,
                'address' => $site->address,
                'latitude' => null,
                'longitude' => null,
                'reason' => 'Program induk perlu dipindah.',
            ]);

            $this->fail('Perubahan program induk seharusnya ditolak jika lokasi memiliki petak.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('program_id', $exception->errors());
        }

        $this->assertSame($originalProgram->getKey(), $site->fresh()->program_id);
        $this->assertDatabaseCount('activity_logs', 0);
    }

    public function test_update_requires_a_reason_and_rejects_non_managers(): void
    {
        $manager = $this->manager();
        $program = $this->program($manager, 'Program Audit');
        $site = $this->site($program, 'Lokasi Audit');

        try {
            app(UpdateSite::class)->handle($manager, $site, [
                'program_id' => $program->getKey(),
                'name' => 'Lokasi Audit Baru',
                'address' => $site->address,
                'latitude' => null,
                'longitude' => null,
                'reason' => '  ',
            ]);

            $this->fail('Alasan perubahan kosong seharusnya ditolak.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('reason', $exception->errors());
        }

        $fieldStaff = User::factory()->create([
            'role' => UserRole::FieldStaff,
            'is_active' => true,
        ]);

        try {
            app(UpdateSite::class)->handle($fieldStaff, $site, [
                'program_id' => null,
                'name' => '',
                'address' => '',
                'latitude' => null,
                'longitude' => null,
                'reason' => '',
            ]);

            $this->fail('Petugas tidak boleh mengubah lokasi.');
        } catch (AuthorizationException) {
            $this->assertSame('Lokasi Audit', $site->fresh()->name);
        }

        $this->assertDatabaseCount('activity_logs', 0);
    }

    public function test_location_can_only_be_deleted_when_no_plot_or_activity_references_it(): void
    {
        $manager = $this->manager();
        $program = $this->program($manager, 'Program Hapus');
        $unused = $this->site($program, 'Lokasi Kosong');
        $withPlot = $this->site($program, 'Lokasi Berpetak');
        $withActivity = $this->site($program, 'Lokasi Berkegiatan');

        Plot::query()->create([
            'site_id' => $withPlot->getKey(),
            'code' => 'B-01',
            'capacity' => 10,
            'status' => 'open',
        ]);
        Activity::query()->create([
            'site_id' => $withActivity->getKey(),
            'title' => 'Kegiatan uji',
            'kind' => 'care',
            'starts_at' => '2026-11-15 08:00:00',
            'ends_at' => '2026-11-15 10:00:00',
            'quota' => 10,
            'status' => 'draft',
        ]);

        foreach ([$withPlot, $withActivity] as $referencedSite) {
            try {
                app(DeleteUnusedSite::class)->handle($manager, $referencedSite);

                $this->fail('Lokasi yang masih direferensikan seharusnya tidak dapat dihapus.');
            } catch (ValidationException $exception) {
                $this->assertArrayHasKey('site', $exception->errors());
            }

            $this->assertDatabaseHas('sites', ['id' => $referencedSite->getKey()]);
        }

        app(DeleteUnusedSite::class)->handle($manager, $unused);

        $this->assertDatabaseMissing('sites', ['id' => $unused->getKey()]);
    }

    private function manager(): User
    {
        return User::factory()->create([
            'role' => UserRole::Manager,
            'is_active' => true,
        ]);
    }

    private function program(User $manager, string $name): Program
    {
        return Program::query()->create([
            'name' => $name,
            'objective' => 'Data program untuk pengujian lokasi.',
            'starts_on' => '2026-11-01',
            'ends_on' => '2027-11-01',
            'status' => 'active',
            'created_by' => $manager->getKey(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function site(Program $program, string $name, array $attributes = []): Site
    {
        return Site::query()->create(array_merge([
            'program_id' => $program->getKey(),
            'name' => $name,
            'address' => 'Alamat '.$name,
            'latitude' => null,
            'longitude' => null,
        ], $attributes));
    }

    private function openManagerPanelAs(User $manager): void
    {
        $this->actingAs($manager);
        Filament::setCurrentPanel('app');
    }
}
