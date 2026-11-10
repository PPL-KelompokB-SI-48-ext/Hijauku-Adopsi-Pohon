<?php

namespace Tests\Feature;

use App\Actions\Programs\ActivateProgram;
use App\Actions\Programs\ArchiveProgram;
use App\Enums\UserRole;
use App\Filament\Manager\Resources\Programs\Pages\ListPrograms;
use App\Models\Program;
use App\Models\Site;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class ProgramStatusWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_activate_a_complete_draft_and_audit_the_transition(): void
    {
        $manager = $this->manager();
        $program = $this->program($manager);

        $activated = app(ActivateProgram::class)->handle(
            $manager,
            $program,
            'Data program sudah diperiksa.'
        );

        $this->assertSame('active', $activated->fresh()->status);
        $this->assertDatabaseHas('activity_logs', [
            'actor_id' => $manager->getKey(),
            'action' => 'program.activated',
            'subject_type' => Program::class,
            'subject_id' => $program->getKey(),
            'from_status' => 'draft',
            'to_status' => 'active',
        ]);
        $this->assertSame(
            ['reason' => 'Data program sudah diperiksa.'],
            $program->activityLogs()->latest('id')->firstOrFail()->metadata
        );
    }

    public function test_program_with_an_incomplete_period_cannot_be_activated(): void
    {
        $manager = $this->manager();
        $program = $this->program($manager, ['ends_on' => null]);

        try {
            app(ActivateProgram::class)->handle($manager, $program, 'Data sudah dicek.');

            $this->fail('Program tanpa tanggal akhir seharusnya tidak dapat diaktifkan.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('ends_on', $exception->errors());
        }

        $this->assertSame('draft', $program->fresh()->status);
        $this->assertDatabaseCount('activity_logs', 0);
    }

    public function test_program_with_an_invalid_period_cannot_be_activated(): void
    {
        $manager = $this->manager();
        $program = $this->program($manager, [
            'starts_on' => '2026-12-31',
            'ends_on' => '2026-12-01',
        ]);

        try {
            app(ActivateProgram::class)->handle($manager, $program, 'Periode perlu diperiksa.');

            $this->fail('Tanggal akhir sebelum tanggal mulai seharusnya ditolak.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('ends_on', $exception->errors());
        }

        $this->assertSame('draft', $program->fresh()->status);
        $this->assertDatabaseCount('activity_logs', 0);
    }

    public function test_only_a_draft_program_can_be_activated_and_reason_is_required(): void
    {
        $manager = $this->manager();
        $activeProgram = $this->program($manager, ['status' => 'active']);

        try {
            app(ActivateProgram::class)->handle($manager, $activeProgram, 'Tidak boleh mengaktifkan ulang.');

            $this->fail('Program yang bukan draf seharusnya tidak dapat diaktifkan ulang.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('status', $exception->errors());
        }

        $draftProgram = $this->program($manager);

        try {
            app(ActivateProgram::class)->handle($manager, $draftProgram, '   ');

            $this->fail('Alasan kosong seharusnya ditolak.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('reason', $exception->errors());
        }

        $this->assertSame('draft', $draftProgram->fresh()->status);
        $this->assertDatabaseCount('activity_logs', 0);
    }

    public function test_only_a_manager_can_activate_a_program(): void
    {
        $manager = $this->manager();
        $program = $this->program($manager);
        $fieldStaff = User::factory()->create([
            'role' => UserRole::FieldStaff,
            'is_active' => true,
        ]);

        $this->expectException(AuthorizationException::class);

        app(ActivateProgram::class)->handle($fieldStaff, $program, 'Tidak berwenang.');
    }

    public function test_manager_can_archive_a_completed_program_without_deleting_its_location(): void
    {
        $manager = $this->manager();
        $program = $this->program($manager, ['status' => 'completed']);
        $site = Site::query()->create([
            'program_id' => $program->getKey(),
            'name' => 'Taman RW 04',
            'address' => 'Jalan Melati',
        ]);

        $archived = app(ArchiveProgram::class)->handle(
            $manager,
            $program,
            'Program selesai dan datanya tetap disimpan.'
        );

        $this->assertSame('archived', $archived->fresh()->status);
        $this->assertDatabaseHas('sites', [
            'id' => $site->getKey(),
            'program_id' => $program->getKey(),
        ]);
        $this->assertDatabaseHas('activity_logs', [
            'actor_id' => $manager->getKey(),
            'action' => 'program.archived',
            'subject_type' => Program::class,
            'subject_id' => $program->getKey(),
            'from_status' => 'completed',
            'to_status' => 'archived',
        ]);
        $this->assertSame(
            ['reason' => 'Program selesai dan datanya tetap disimpan.'],
            $program->activityLogs()->latest('id')->firstOrFail()->metadata
        );
        $this->assertFalse($manager->can('delete', $program->fresh()));
    }

    public function test_a_program_that_is_not_completed_cannot_be_archived(): void
    {
        $manager = $this->manager();
        $program = $this->program($manager, ['status' => 'active']);

        try {
            app(ArchiveProgram::class)->handle($manager, $program, 'Belum selesai.');

            $this->fail('Program aktif seharusnya tidak dapat diarsipkan.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('status', $exception->errors());
        }

        $this->assertSame('active', $program->fresh()->status);
        $this->assertDatabaseCount('activity_logs', 0);
    }

    public function test_archive_requires_a_reason_and_only_a_manager_can_archive(): void
    {
        $manager = $this->manager();
        $completedProgram = $this->program($manager, ['status' => 'completed']);

        try {
            app(ArchiveProgram::class)->handle($manager, $completedProgram, '  ');

            $this->fail('Alasan pengarsipan kosong seharusnya ditolak.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('reason', $exception->errors());
        }

        $fieldStaff = User::factory()->create([
            'role' => UserRole::FieldStaff,
            'is_active' => true,
        ]);

        try {
            app(ArchiveProgram::class)->handle($fieldStaff, $completedProgram, 'Tidak berwenang.');

            $this->fail('Petugas lapangan tidak boleh mengarsipkan program.');
        } catch (AuthorizationException) {
            $this->assertSame('completed', $completedProgram->fresh()->status);
        }

        $this->assertDatabaseCount('activity_logs', 0);
    }

    public function test_filament_exposes_only_the_transition_allowed_by_the_current_status(): void
    {
        $manager = $this->manager();
        $this->openManagerPanelAs($manager);
        $draft = $this->program($manager);
        $completed = $this->program($manager, [
            'name' => 'Program selesai',
            'status' => 'completed',
        ]);

        Livewire::test(ListPrograms::class)
            ->assertTableActionVisible('activate', $draft)
            ->assertTableActionHidden('archive', $draft)
            ->assertTableActionHidden('activate', $completed)
            ->assertTableActionVisible('archive', $completed)
            ->callTableAction('activate', $draft, data: [
                'reason' => 'Data program sudah diperiksa.',
            ])
            ->assertHasNoTableActionErrors()
            ->assertNotified('Program berhasil diaktifkan.');

        $this->assertSame('active', $draft->fresh()->status);

        Livewire::test(ListPrograms::class)
            ->callTableAction('archive', $completed, data: [
                'reason' => 'Program sudah selesai.',
            ])
            ->assertHasNoTableActionErrors()
            ->assertNotified('Program berhasil diarsipkan.');

        $this->assertSame('archived', $completed->fresh()->status);
    }

    private function manager(): User
    {
        return User::factory()->create([
            'role' => UserRole::Manager,
            'is_active' => true,
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function program(User $creator, array $overrides = []): Program
    {
        return Program::query()->forceCreate(array_merge([
            'name' => 'Program Uji',
            'objective' => 'Memulihkan ruang hijau di lingkungan warga.',
            'starts_on' => '2026-11-01',
            'ends_on' => '2027-03-31',
            'status' => 'draft',
            'created_by' => $creator->getKey(),
        ], $overrides));
    }

    private function openManagerPanelAs(User $manager): void
    {
        $this->actingAs($manager);
        Filament::setCurrentPanel('app');
    }
}
