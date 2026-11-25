<?php

namespace Tests\Feature;

use App\Actions\TreeIssues\HandleTreeIssue;
use App\Actions\TreeIssues\RejectTreeIssue;
use App\Enums\UserRole;
use App\Filament\Manager\Resources\TreeIssues\Pages\ListTreeIssues;
use App\Models\ActivityLog;
use App\Models\Plot;
use App\Models\Program;
use App\Models\Site;
use App\Models\Tree;
use App\Models\TreeIssue;
use App\Models\TreeSpecies;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class TreeIssueWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private int $treeSequence = 0;

    public function test_manager_can_assign_a_field_staff_member_and_mark_a_report_handled(): void
    {
        $manager = $this->manager();
        $reporter = $this->citizen();
        $assignee = $this->fieldStaff();
        $issue = $this->issue($reporter);

        $handled = app(HandleTreeIssue::class)->handle($manager, $issue, [
            'assignee_id' => $assignee->getKey(),
            'status' => 'rejected',
            'reviewer_id' => $this->fieldStaff()->getKey(),
        ]);

        $this->assertSame('in_progress', $handled->status);
        $this->assertSame($assignee->getKey(), $handled->assignee_id);
        $this->assertSame($manager->getKey(), $handled->reviewer_id);
        $this->assertDatabaseHas('tree_issues', [
            'id' => $issue->getKey(),
            'status' => 'in_progress',
            'assignee_id' => $assignee->getKey(),
            'reviewer_id' => $manager->getKey(),
        ]);

        $log = ActivityLog::query()
            ->where('subject_type', TreeIssue::class)
            ->where('subject_id', $issue->getKey())
            ->firstOrFail();

        $this->assertSame($manager->getKey(), $log->actor_id);
        $this->assertSame('tree_issue.handled', $log->action);
        $this->assertSame('submitted', $log->from_status);
        $this->assertSame('in_progress', $log->to_status);
        $this->assertSame(['assignee_id' => $assignee->getKey()], $log->metadata);
        $this->assertNotNull($log->created_at);
    }

    public function test_manager_can_reject_a_report_with_a_reason_and_audit_the_transition(): void
    {
        $manager = $this->manager();
        $issue = $this->issue($this->citizen());

        $rejected = app(RejectTreeIssue::class)->handle(
            $manager,
            $issue,
            '  Bukti tidak sesuai dengan pohon yang dilaporkan.  '
        );

        $this->assertSame('rejected', $rejected->status);
        $this->assertSame('Bukti tidak sesuai dengan pohon yang dilaporkan.', $rejected->resolution);
        $this->assertSame($manager->getKey(), $rejected->reviewer_id);
        $this->assertDatabaseHas('tree_issues', [
            'id' => $issue->getKey(),
            'status' => 'rejected',
            'resolution' => 'Bukti tidak sesuai dengan pohon yang dilaporkan.',
            'reviewer_id' => $manager->getKey(),
        ]);

        $log = ActivityLog::query()
            ->where('subject_type', TreeIssue::class)
            ->where('subject_id', $issue->getKey())
            ->firstOrFail();

        $this->assertSame($manager->getKey(), $log->actor_id);
        $this->assertSame('tree_issue.rejected', $log->action);
        $this->assertSame('submitted', $log->from_status);
        $this->assertSame('rejected', $log->to_status);
        $this->assertSame(
            ['reason' => 'Bukti tidak sesuai dengan pohon yang dilaporkan.'],
            $log->metadata
        );
        $this->assertNotNull($log->created_at);
    }

    public function test_rejection_requires_a_non_empty_reason(): void
    {
        $manager = $this->manager();
        $issue = $this->issue($this->citizen());

        try {
            app(RejectTreeIssue::class)->handle($manager, $issue, '   ');

            $this->fail('Penolakan tanpa alasan seharusnya ditolak.');
        } catch (ValidationException $exception) {
            $this->assertSame(
                ['Alasan penolakan wajib diisi.'],
                $exception->errors()['reason']
            );
        }

        $this->assertSame('submitted', $issue->fresh()->status);
        $this->assertNull($issue->fresh()->resolution);
        $this->assertDatabaseCount('activity_logs', 0);
    }

    public function test_rejection_rejects_a_reason_longer_than_1000_characters(): void
    {
        $manager = $this->manager();
        $issue = $this->issue($this->citizen());

        try {
            app(RejectTreeIssue::class)->handle($manager, $issue, str_repeat('a', 1001));

            $this->fail('Alasan lebih dari 1000 karakter seharusnya ditolak.');
        } catch (ValidationException $exception) {
            $this->assertSame(
                ['Alasan penolakan maksimal 1000 karakter.'],
                $exception->errors()['reason']
            );
        }

        $this->assertSame('submitted', $issue->fresh()->status);
        $this->assertDatabaseCount('activity_logs', 0);
    }

    public function test_a_report_can_only_be_marked_handled_once_from_submitted_status(): void
    {
        $manager = $this->manager();
        $assignee = $this->fieldStaff();
        $issue = $this->issue($this->citizen(), 'in_progress');

        try {
            app(HandleTreeIssue::class)->handle($manager, $issue, [
                'assignee_id' => $assignee->getKey(),
            ]);

            $this->fail('Laporan yang sudah ditangani tidak boleh ditangani ulang.');
        } catch (ValidationException $exception) {
            $this->assertSame(
                ['Hanya laporan berstatus dilaporkan yang dapat ditangani.'],
                $exception->errors()['status']
            );
        }

        $this->assertSame('in_progress', $issue->fresh()->status);
        $this->assertNull($issue->fresh()->assignee_id);
        $this->assertDatabaseCount('activity_logs', 0);
    }

    public function test_a_report_can_only_be_rejected_from_submitted_status(): void
    {
        $manager = $this->manager();
        $issue = $this->issue($this->citizen(), 'in_progress');

        try {
            app(RejectTreeIssue::class)->handle($manager, $issue, 'Laporan tidak sah.');

            $this->fail('Laporan yang sudah ditangani tidak boleh ditolak.');
        } catch (ValidationException $exception) {
            $this->assertSame(
                ['Hanya laporan berstatus dilaporkan yang dapat ditolak.'],
                $exception->errors()['reason']
            );
        }

        $this->assertSame('in_progress', $issue->fresh()->status);
        $this->assertNull($issue->fresh()->resolution);
        $this->assertDatabaseCount('activity_logs', 0);
    }

    public function test_assignment_requires_a_field_staff_member(): void
    {
        $manager = $this->manager();
        $issue = $this->issue($this->citizen());

        try {
            app(HandleTreeIssue::class)->handle($manager, $issue, []);

            $this->fail('Penugasan tanpa petugas seharusnya ditolak.');
        } catch (ValidationException $exception) {
            $this->assertSame(
                ['Petugas tindak lanjut wajib dipilih.'],
                $exception->errors()['assignee_id']
            );
        }

        try {
            app(HandleTreeIssue::class)->handle($manager, $issue, [
                'assignee_id' => $this->citizen()->getKey(),
            ]);

            $this->fail('Warga tidak boleh menjadi petugas tindak lanjut.');
        } catch (ValidationException $exception) {
            $this->assertSame(
                ['Petugas tindak lanjut harus memiliki akun petugas aktif.'],
                $exception->errors()['assignee_id']
            );
        }

        $this->assertSame('submitted', $issue->fresh()->status);
        $this->assertDatabaseCount('activity_logs', 0);
    }

    public function test_an_inactive_field_staff_member_cannot_be_assigned(): void
    {
        $manager = $this->manager();
        $issue = $this->issue($this->citizen());
        $inactiveFieldStaff = User::factory()->create([
            'role' => UserRole::FieldStaff,
            'is_active' => false,
        ]);

        try {
            app(HandleTreeIssue::class)->handle($manager, $issue, [
                'assignee_id' => $inactiveFieldStaff->getKey(),
            ]);

            $this->fail('Petugas tidak aktif tidak boleh menjadi penanggung jawab.');
        } catch (ValidationException $exception) {
            $this->assertSame(
                ['Petugas tindak lanjut harus memiliki akun petugas aktif.'],
                $exception->errors()['assignee_id']
            );
        }

        $this->assertSame('submitted', $issue->fresh()->status);
        $this->assertDatabaseCount('activity_logs', 0);
    }

    public function test_field_staff_cannot_handle_a_report(): void
    {
        $issue = $this->issue($this->citizen());
        $fieldStaff = $this->fieldStaff();

        try {
            app(HandleTreeIssue::class)->handle($fieldStaff, $issue, [
                'assignee_id' => $fieldStaff->getKey(),
            ]);

            $this->fail('Petugas tidak boleh mengubah status laporan.');
        } catch (AuthorizationException) {
            $this->assertSame('submitted', $issue->fresh()->status);
        }

        $this->assertDatabaseCount('activity_logs', 0);
    }

    public function test_citizen_cannot_reject_a_report(): void
    {
        $issue = $this->issue($this->citizen());
        $citizen = $this->citizen();

        try {
            app(RejectTreeIssue::class)->handle($citizen, $issue, 'Tidak berwenang.');

            $this->fail('Warga tidak boleh menolak laporan.');
        } catch (AuthorizationException) {
            $this->assertSame('submitted', $issue->fresh()->status);
        }

        $this->assertDatabaseCount('activity_logs', 0);
    }

    public function test_manager_can_review_all_reports_and_actions_are_limited_to_submitted_reports(): void
    {
        $manager = $this->manager();
        $this->actingAs($manager);
        Filament::setCurrentPanel('app');

        $submittedIssue = $this->issue($this->citizen());
        $handledIssue = $this->issue($this->fieldStaff(), 'in_progress');
        $rejectedIssue = $this->issue($this->citizen(), 'rejected');

        Livewire::test(ListTreeIssues::class)
            ->assertCanSeeTableRecords([$submittedIssue, $handledIssue, $rejectedIssue])
            ->assertTableActionVisible('handle', $submittedIssue)
            ->assertTableActionVisible('reject', $submittedIssue)
            ->assertTableActionHidden('handle', $handledIssue)
            ->assertTableActionHidden('reject', $rejectedIssue);
    }

    public function test_manager_can_download_evidence_for_a_report(): void
    {
        Storage::fake('private');

        $manager = $this->manager();
        $issue = $this->issue($this->citizen());
        Storage::disk('private')->put($issue->evidence_path, 'private evidence');

        $this->actingAs($manager)
            ->get(route('tree-issues.evidence', $issue))
            ->assertOk()
            ->assertHeader('content-disposition');
    }

    public function test_reporter_can_view_their_issue_but_another_reporter_cannot(): void
    {
        $reporter = $this->citizen();
        $otherReporter = $this->citizen();
        $issue = $this->issue($reporter);

        $this->assertTrue($reporter->can('view', $issue));
        $this->assertFalse($otherReporter->can('view', $issue));
        $manager = $this->manager();
        $this->assertTrue($manager->can('view', $issue));
        $this->assertFalse($manager->can('update', $issue));
        $this->assertFalse($manager->can('delete', $issue));
        $this->assertFalse($reporter->can('handle', $issue));
        $this->assertFalse($reporter->can('reject', $issue));
    }

    private function manager(): User
    {
        return User::factory()->create([
            'role' => UserRole::Manager,
            'is_active' => true,
        ]);
    }

    private function citizen(): User
    {
        return User::factory()->create([
            'role' => UserRole::Citizen,
            'is_active' => true,
        ]);
    }

    private function fieldStaff(): User
    {
        return User::factory()->create([
            'role' => UserRole::FieldStaff,
            'is_active' => true,
        ]);
    }

    private function tree(): Tree
    {
        $suffix = (string) ++$this->treeSequence;
        $manager = $this->manager();
        $program = Program::query()->create([
            'name' => 'Program Tes HJK-035 '.$suffix,
            'objective' => 'Pengujian tindak lanjut laporan masalah pohon.',
            'starts_on' => '2026-11-01',
            'ends_on' => '2027-11-01',
            'status' => 'active',
            'created_by' => $manager->getKey(),
        ]);
        $site = Site::query()->create([
            'program_id' => $program->getKey(),
            'name' => 'Lokasi Tes HJK-035 '.$suffix,
            'address' => 'Lokasi pengujian.',
        ]);
        $plot = Plot::query()->create([
            'site_id' => $site->getKey(),
            'code' => 'PETAK-HJK-035-'.$suffix,
            'capacity' => 10,
            'status' => 'open',
        ]);
        $species = TreeSpecies::query()->create([
            'name' => 'Jenis Tes HJK-035 '.$suffix,
            'is_active' => true,
        ]);

        return Tree::query()->create([
            'species_id' => $species->getKey(),
            'plot_id' => $plot->getKey(),
            'code' => 'POHON-HJK-035-'.$suffix,
            'initial_condition' => 'healthy',
        ]);
    }

    private function issue(User $reporter, string $status = 'submitted'): TreeIssue
    {
        return TreeIssue::query()->create([
            'tree_id' => $this->tree()->getKey(),
            'reporter_id' => $reporter->getKey(),
            'description' => 'Daun pohon perlu diperiksa.',
            'severity' => 'normal',
            'evidence_path' => 'tree-issues/evidence/hjk-035-test.jpg',
            'status' => $status,
        ]);
    }
}
