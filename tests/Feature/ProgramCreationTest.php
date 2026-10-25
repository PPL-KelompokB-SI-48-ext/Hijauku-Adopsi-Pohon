<?php

namespace Tests\Feature;

use App\Actions\Programs\CreateDraftProgram;
use App\Enums\UserRole;
use App\Filament\Manager\Resources\Programs\Pages\CreateProgram;
use App\Models\Program;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class ProgramCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_create_a_draft_program_and_audit_event(): void
    {
        $manager = $this->manager();
        $this->openManagerPanelAs($manager);

        Livewire::test(CreateProgram::class)
            ->fillForm([
                'name' => 'Kampung Teduh',
                'objective' => 'Menambah tutupan pohon di ruang publik.',
                'starts_on' => '2026-11-01',
                'ends_on' => '2027-03-31',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $program = Program::query()->where('name', 'Kampung Teduh')->sole();

        $this->assertSame('draft', $program->status);
        $this->assertSame($manager->getKey(), $program->created_by);
        $this->assertSame($manager->getKey(), $program->creator->getKey());

        $this->assertDatabaseHas('activity_logs', [
            'actor_id' => $manager->getKey(),
            'action' => 'program.created',
            'subject_type' => Program::class,
            'subject_id' => $program->getKey(),
            'from_status' => null,
            'to_status' => 'draft',
        ]);
    }

    public function test_program_form_requires_name_objective_and_both_period_dates(): void
    {
        $this->openManagerPanelAs($this->manager());

        Livewire::test(CreateProgram::class)
            ->call('create')
            ->assertHasFormErrors([
                'name' => 'required',
                'objective' => 'required',
                'starts_on' => 'required',
                'ends_on' => 'required',
            ]);

        $this->assertDatabaseCount('programs', 0);
        $this->assertDatabaseCount('activity_logs', 0);
    }

    public function test_program_end_date_must_be_after_its_start_date(): void
    {
        $this->openManagerPanelAs($this->manager());

        Livewire::test(CreateProgram::class)
            ->fillForm([
                'name' => 'Kampung Teduh',
                'objective' => 'Menambah tutupan pohon di ruang publik.',
                'starts_on' => '2027-03-31',
                'ends_on' => '2027-03-31',
            ])
            ->call('create')
            ->assertHasFormErrors(['ends_on' => 'after']);

        $this->assertDatabaseCount('programs', 0);
        $this->assertDatabaseCount('activity_logs', 0);
    }

    public function test_whitespace_only_name_and_objective_are_rejected(): void
    {
        $this->openManagerPanelAs($this->manager());

        Livewire::test(CreateProgram::class)
            ->fillForm([
                'name' => '   ',
                'objective' => "\t  ",
                'starts_on' => '2026-11-01',
                'ends_on' => '2027-03-31',
            ])
            ->call('create')
            ->assertHasFormErrors([
                'name' => 'required',
                'objective' => 'required',
            ]);

        $this->assertDatabaseCount('programs', 0);
    }

    public function test_server_owned_status_and_creator_ignore_forged_form_attributes(): void
    {
        $manager = $this->manager();
        $otherManager = $this->manager();

        $program = app(CreateDraftProgram::class)->handle($manager, [
            'name' => 'Hutan Sekolah',
            'objective' => 'Menambah area teduh di sekolah.',
            'starts_on' => '2026-11-01',
            'ends_on' => '2027-03-31',
            'status' => 'active',
            'created_by' => $otherManager->getKey(),
        ]);

        $this->assertSame('draft', $program->status);
        $this->assertSame($manager->getKey(), $program->created_by);
    }

    public function test_creation_action_rechecks_the_period_for_non_form_requests(): void
    {
        try {
            app(CreateDraftProgram::class)->handle($this->manager(), [
                'name' => 'Taman Warga',
                'objective' => 'Meningkatkan ruang teduh di lingkungan warga.',
                'starts_on' => '2027-05-01',
                'ends_on' => '2027-04-30',
            ]);

            $this->fail('Action seharusnya menolak tanggal akhir yang lebih awal.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('ends_on', $exception->errors());
        }

        $this->assertDatabaseCount('programs', 0);
        $this->assertDatabaseCount('activity_logs', 0);
    }

    public function test_creation_rejects_timestamps_that_would_collapse_to_the_same_calendar_date(): void
    {
        try {
            app(CreateDraftProgram::class)->handle($this->manager(), [
                'name' => 'Taman Warga',
                'objective' => 'Menjaga periode sebagai tanggal kalender.',
                'starts_on' => '2026-11-01 08:00:00',
                'ends_on' => '2026-11-01 17:00:00',
            ]);

            $this->fail('Action seharusnya menolak timestamp pada kolom tanggal program.');
        } catch (ValidationException $exception) {
            $this->assertSame(
                ['Tanggal mulai harus berupa tanggal yang valid.'],
                $exception->errors()['starts_on'],
            );
            $this->assertSame(
                ['Tanggal akhir harus berupa tanggal yang valid.'],
                $exception->errors()['ends_on'],
            );
        }

        $this->assertDatabaseCount('programs', 0);
        $this->assertDatabaseCount('activity_logs', 0);
    }

    public function test_only_managers_can_open_program_pages_or_call_creation_action(): void
    {
        $fieldStaff = User::factory()->create([
            'role' => UserRole::FieldStaff,
            'is_active' => true,
        ]);
        $citizen = User::factory()->create([
            'role' => UserRole::Citizen,
            'is_active' => true,
        ]);

        $this->actingAs($fieldStaff)
            ->get('/app/programs')
            ->assertForbidden();

        $this->assertFalse($fieldStaff->can('viewAny', Program::class));
        $this->assertFalse($citizen->can('viewAny', Program::class));

        $this->expectException(AuthorizationException::class);

        app(CreateDraftProgram::class)->handle($fieldStaff, [
            'name' => 'Program tidak sah',
            'objective' => 'Tidak boleh dibuat oleh petugas.',
            'starts_on' => '2026-11-01',
            'ends_on' => '2027-03-31',
        ]);
    }

    public function test_manager_is_not_allowed_to_update_or_delete_programs_in_this_story(): void
    {
        $manager = $this->manager();
        $program = new Program;
        $program->forceFill(['id' => 42, 'created_by' => $manager->getKey()]);

        $this->assertTrue($manager->can('create', Program::class));
        $this->assertTrue($manager->can('viewAny', Program::class));
        $this->assertFalse($manager->can('update', $program));
        $this->assertFalse($manager->can('delete', $program));
    }

    private function manager(): User
    {
        return User::factory()->create([
            'role' => UserRole::Manager,
            'is_active' => true,
        ]);
    }

    private function openManagerPanelAs(User $manager): void
    {
        $this->actingAs($manager);
        Filament::setCurrentPanel('app');
    }
}
