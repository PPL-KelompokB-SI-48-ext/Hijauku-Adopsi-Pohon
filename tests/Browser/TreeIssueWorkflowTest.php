<?php

namespace Tests\Browser;

use App\Enums\UserRole;
use App\Models\Tree;
use App\Models\TreeIssue;
use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class TreeIssueWorkflowTest extends DuskTestCase
{
    public function test_manager_can_assign_and_reject_reported_tree_issues_from_the_panel(): void
    {
        $manager = User::query()->where('email', 'pengelola@hijauku.test')->firstOrFail();
        $reporter = User::query()->where('email', 'warga@hijauku.test')->firstOrFail();
        $assignee = User::factory()->create([
            'role' => UserRole::FieldStaff,
            'is_active' => true,
        ]);
        $tree = Tree::query()->where('code', 'POHON-DEMO-001')->firstOrFail();
        $handledIssue = $this->issue($reporter, $tree, 'Laporan untuk penugasan browser.');
        $rejectedIssue = $this->issue($reporter, $tree, 'Laporan untuk penolakan browser.');

        try {
            $this->browse(function (Browser $browser) use (
                $assignee,
                $handledIssue,
                $rejectedIssue
            ): void {
                $handleRow = 'tr[wire\\:key$=".table.records.'.$handledIssue->getKey().'"]';
                $rejectRow = 'tr[wire\\:key$=".table.records.'.$rejectedIssue->getKey().'"]';

                $browser->visit('/login')
                    ->type('input[type="email"]', 'pengelola@hijauku.test')
                    ->type('input[type="password"]', (string) env('DEMO_PASSWORD', 'demo-hijauku'))
                    ->click('form button[type="submit"]')
                    ->waitForLocation('/app')
                    ->visit('/app/laporan-pohon')
                    ->assertSee($handledIssue->description)
                    ->assertSee($rejectedIssue->description);

                $browser->with($handleRow, function (Browser $row): void {
                    $row->press('Tugaskan dan Tangani');
                });

                $browser->waitForText('Pilih petugas lapangan')
                    ->select('select[id$="assignee_id"]', (string) $assignee->getKey())
                    ->press('Tugaskan dan Tandai Ditangani')
                    ->waitUntilMissingText('Pilih petugas lapangan', 15)
                    ->assertSeeIn($handleRow, 'Ditangani')
                    ->assertSeeIn($handleRow, $assignee->name);

                $browser->with($rejectRow, function (Browser $row): void {
                    $row->press('Tolak');
                });

                $browser->waitForText('Alasan Penolakan')
                    ->type('textarea', 'Bukti tidak sesuai dengan pohon.')
                    ->press('Tolak Laporan')
                    ->waitUntilMissingText('Alasan Penolakan', 15)
                    ->assertSeeIn($rejectRow, 'Ditolak')
                    ->assertSeeIn($rejectRow, 'Bukti tidak sesuai dengan pohon.');
            });

            $this->assertSame('in_progress', $handledIssue->fresh()->status);
            $this->assertSame($assignee->getKey(), $handledIssue->fresh()->assignee_id);
            $this->assertSame('rejected', $rejectedIssue->fresh()->status);
            $this->assertSame('Bukti tidak sesuai dengan pohon.', $rejectedIssue->fresh()->resolution);
            $this->assertDatabaseHas('activity_logs', [
                'actor_id' => $manager->getKey(),
                'action' => 'tree_issue.handled',
                'subject_type' => TreeIssue::class,
                'subject_id' => $handledIssue->getKey(),
                'from_status' => 'submitted',
                'to_status' => 'in_progress',
            ]);
            $this->assertDatabaseHas('activity_logs', [
                'actor_id' => $manager->getKey(),
                'action' => 'tree_issue.rejected',
                'subject_type' => TreeIssue::class,
                'subject_id' => $rejectedIssue->getKey(),
                'from_status' => 'submitted',
                'to_status' => 'rejected',
            ]);
        } finally {
            $handledIssue->activityLogs()->delete();
            $rejectedIssue->activityLogs()->delete();
            $handledIssue->delete();
            $rejectedIssue->delete();
            $assignee->delete();
        }
    }

    private function issue(User $reporter, Tree $tree, string $description): TreeIssue
    {
        return TreeIssue::query()->create([
            'tree_id' => $tree->getKey(),
            'reporter_id' => $reporter->getKey(),
            'description' => $description,
            'severity' => 'normal',
            'evidence_path' => 'tree-issues/evidence/hjk-035-browser.jpg',
            'status' => 'submitted',
        ]);
    }
}
