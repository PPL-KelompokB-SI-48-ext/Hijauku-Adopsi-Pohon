<?php

namespace Tests\Browser;

use App\Models\AdoptionAgreement;
use App\Models\AdoptionRequest;
use App\Models\Tree;
use App\Models\TreeAdoptionAllocation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class EndAdoptionAgreementTest extends DuskTestCase
{
    public function test_manager_can_terminate_active_adoption_agreement_from_browser(): void
    {
        $manager = User::query()
            ->where('email', 'pengelola@hijauku.test')
            ->firstOrFail();

        $citizen = User::query()
            ->where('email', 'warga@hijauku.test')
            ->firstOrFail();

        $sourceTree = Tree::query()
            ->where('code', 'POHON-DEMO-001')
            ->firstOrFail();

        $treeCode = 'DUSK-H39-'.Str::upper(Str::random(6));

        $tree = Tree::query()->create([
            'species_id' => $sourceTree->species_id,
            'plot_id' => $sourceTree->plot_id,
            'batch_id' => null,
            'code' => $treeCode,
            'initial_condition' => $sourceTree->initial_condition,
        ]);

        $request = AdoptionRequest::query()->create([
            'tree_id' => $tree->getKey(),
            'requester_id' => $citizen->getKey(),
            'reason' => 'Pengujian pengakhiran kesepakatan '.$treeCode,
            'status' => 'approved',
            'reviewer_id' => $manager->getKey(),
        ]);

        $agreement = AdoptionAgreement::query()->create([
            'request_id' => $request->getKey(),
            'tree_id' => $tree->getKey(),
            'adopter_id' => $citizen->getKey(),
            'terms' => 'Pohon dirawat secara rutin oleh pengadopsi.',
            'starts_on' => now()->subDay()->toDateString(),
            'ends_on' => now()->addMonth()->toDateString(),
            'status' => 'active',
            'accepted_at' => now(),
        ]);

        TreeAdoptionAllocation::query()->create([
            'tree_id' => $tree->getKey(),
            'request_id' => $request->getKey(),
            'agreement_id' => $agreement->getKey(),
            'status' => 'active',
            'reserved_at' => now(),
        ]);

        $reason = 'Kesepakatan dihentikan karena pengadopsi tidak dapat melanjutkan perawatan.';

        $this->browse(function (Browser $browser) use (
            $agreement,
            $manager,
            $treeCode,
            $reason,
        ): void {
            $rowSelector = 'tr[wire\\:key$=".table.records.'.$agreement->getKey().'"]';

            $browser->loginAs($manager)
                ->visit('/app/adoption-agreements')
                ->waitUsing(30, 0.2, fn (): bool => $agreement->fresh()->status === 'active')
                ->waitFor($rowSelector, 30)
                ->with($rowSelector, function (Browser $row) use ($treeCode): void {
                    $row->assertSee($treeCode)
                        ->press('Akhiri Kesepakatan');
                });

            $browser->waitForText('Alasan Penghentian')
                ->with('.fi-modal', function (Browser $modal) use ($reason): void {
                    $modal->type('textarea', $reason)
                        ->press('Akhiri Kesepakatan');
                })
                ->waitUntilMissingText('Alasan Penghentian', 15)
                ->assertSee($treeCode)
                ->assertSee('Dihentikan');
        });

        $this->assertSame('terminated', $agreement->fresh()->status);

        $this->assertDatabaseHas('tree_adoption_allocations', [
            'agreement_id' => $agreement->getKey(),
            'tree_id' => $tree->getKey(),
            'status' => 'released',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'subject_type' => AdoptionAgreement::class,
            'subject_id' => $agreement->getKey(),
            'actor_id' => $manager->getKey(),
            'action' => 'adoption_agreement.terminated',
            'from_status' => 'active',
            'to_status' => 'terminated',
        ]);

        $activity = DB::table('activity_logs')
            ->where('subject_type', AdoptionAgreement::class)
            ->where('subject_id', $agreement->getKey())
            ->where('action', 'adoption_agreement.terminated')
            ->first();

        $this->assertNotNull($activity);
        $this->assertStringContainsString($reason, json_encode($activity->metadata));
    }
}
