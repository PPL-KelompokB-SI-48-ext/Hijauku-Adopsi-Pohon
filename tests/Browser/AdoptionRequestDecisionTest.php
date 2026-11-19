<?php

namespace Tests\Browser;

use App\Enums\UserRole;
use App\Models\AdoptionRequest;
use App\Models\Tree;
use App\Models\User;
use Facebook\WebDriver\WebDriverBy;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class AdoptionRequestDecisionTest extends DuskTestCase
{
    public function test_manager_can_approve_and_reject_submitted_requests_from_browser(): void
    {
        $manager = User::query()
            ->where('email', 'pengelola@hijauku.test')
            ->firstOrFail();

        $citizen = User::query()
            ->where('email', 'warga@hijauku.test')
            ->firstOrFail();

        $anotherCitizen = User::factory()->create([
            'role' => UserRole::Citizen,
            'is_active' => true,
        ]);

        $sourceTree = Tree::query()
            ->where('code', 'POHON-DEMO-001')
            ->firstOrFail();

        $approvedTree = Tree::query()->create([
            'species_id' => $sourceTree->species_id,
            'plot_id' => $sourceTree->plot_id,
            'batch_id' => null,
            'code' => 'DUSK-H35-APPROVE-'.Str::upper(Str::random(6)),
            'initial_condition' => $sourceTree->initial_condition,
        ]);

        $rejectedTree = Tree::query()->create([
            'species_id' => $sourceTree->species_id,
            'plot_id' => $sourceTree->plot_id,
            'batch_id' => null,
            'code' => 'DUSK-H35-REJECT-'.Str::upper(Str::random(6)),
            'initial_condition' => $sourceTree->initial_condition,
        ]);

        $approvalReason = 'DUSK-H35-APPROVE-'.Str::upper(Str::random(6));
        $rejectionReason = 'DUSK-H35-REJECT-'.Str::upper(Str::random(6));
        $decisionReason = 'Data permohonan belum memenuhi ketentuan.';

        $approvalRequest = AdoptionRequest::query()->create([
            'tree_id' => $approvedTree->getKey(),
            'requester_id' => $citizen->getKey(),
            'reason' => $approvalReason,
            'status' => 'submitted',
        ]);

        $rejectionRequest = AdoptionRequest::query()->create([
            'tree_id' => $rejectedTree->getKey(),
            'requester_id' => $anotherCitizen->getKey(),
            'reason' => $rejectionReason,
            'status' => 'submitted',
        ]);

        $this->browse(function (Browser $browser) use (
            $approvalReason,
            $rejectionReason,
            $decisionReason
        ): void {
            $browser->visit('/app/login')
                ->type('input[type="email"]', 'pengelola@hijauku.test')
                ->type('input[type="password"]', (string) env('DEMO_PASSWORD', 'demo-hijauku'))
                ->click('form button[type="submit"]')
                ->waitForLocation('/app')
                ->visit('/app/adoption-requests')
                ->waitForText($approvalReason)
                ->waitForText($rejectionReason);

            $approvalRows = array_values(array_filter(
                $browser->elements('table tbody tr'),
                fn ($row) => str_contains($row->getText(), $approvalReason),
            ));

            $this->assertCount(1, $approvalRows);

            $approvalRows[0]->findElement(
                WebDriverBy::xpath('.//button[contains(., "Setujui")]')
            )->click();

            $browser->waitForText('Apakah Anda yakin ingin menyetujui permohonan adopsi ini?')
                ->press('Ya, Setujui');

            $browser->waitUsing(15, 0.1, function () use ($browser, $approvalReason): bool {
                foreach ($browser->elements('table tbody tr') as $row) {
                    if (str_contains($row->getText(), $approvalReason)) {
                        return str_contains($row->getText(), 'Disetujui');
                    }
                }

                return false;
            });

            $rejectionRows = array_values(array_filter(
                $browser->elements('table tbody tr'),
                fn ($row) => str_contains($row->getText(), $rejectionReason),
            ));

            $this->assertCount(1, $rejectionRows);

            $rejectionRows[0]->findElement(
                WebDriverBy::xpath('.//button[contains(., "Tolak")]')
            )->click();

            $browser->waitForText('Alasan Penolakan')
                ->type('textarea', $decisionReason)
                ->press('Tolak Permohonan');

            $browser->waitUsing(15, 0.1, function () use ($browser, $rejectionReason): bool {
                foreach ($browser->elements('table tbody tr') as $row) {
                    if (str_contains($row->getText(), $rejectionReason)) {
                        return str_contains($row->getText(), 'Ditolak');
                    }
                }

                return false;
            });
        });

        $this->assertDatabaseHas('adoption_requests', [
            'id' => $approvalRequest->getKey(),
            'status' => 'approved',
            'reviewer_id' => $manager->getKey(),
        ]);

        $this->assertDatabaseHas('adoption_requests', [
            'id' => $rejectionRequest->getKey(),
            'status' => 'rejected',
            'reviewer_id' => $manager->getKey(),
            'decision_reason' => $decisionReason,
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'actor_id' => $manager->getKey(),
            'action' => 'adoption_request.decided',
            'subject_type' => AdoptionRequest::class,
            'subject_id' => $approvalRequest->getKey(),
            'from_status' => 'submitted',
            'to_status' => 'approved',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'actor_id' => $manager->getKey(),
            'action' => 'adoption_request.decided',
            'subject_type' => AdoptionRequest::class,
            'subject_id' => $rejectionRequest->getKey(),
            'from_status' => 'submitted',
            'to_status' => 'rejected',
        ]);
    }
}
