<?php

namespace Tests\Browser;

use App\Models\AdoptionAgreement;
use App\Models\AdoptionRequest;
use App\Models\Tree;
use App\Models\TreeAdoptionAllocation;
use App\Models\TreeIssue;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class TreeIssueCreationTest extends DuskTestCase
{
    public function test_citizen_can_submit_a_tree_issue_with_private_photo_from_the_panel(): void
    {
        $tree = null;
        $citizen = null;
        $temporaryPath = tempnam(sys_get_temp_dir(), 'hjk034-');
        $evidencePath = $temporaryPath.'.png';
        rename($temporaryPath, $evidencePath);
        file_put_contents(
            $evidencePath,
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR4nGNgYAAAAAMAASsJTYQAAAAASUVORK5CYII=', true)
        );

        try {
            $citizen = User::query()->where('email', 'warga@hijauku.test')->firstOrFail();
            $sourceTree = Tree::query()->where('code', 'POHON-DEMO-001')->firstOrFail();
            $tree = Tree::query()->create([
                'species_id' => $sourceTree->species_id,
                'plot_id' => $sourceTree->plot_id,
                'batch_id' => null,
                'code' => 'DUSK-HJK-034-'.(Tree::query()->max('id') + 1),
                'initial_condition' => $sourceTree->initial_condition,
            ]);
            $request = AdoptionRequest::query()->create([
                'tree_id' => $tree->getKey(),
                'requester_id' => $citizen->getKey(),
                'reason' => 'Data adopsi untuk pengujian HAP-40.',
                'status' => 'approved',
            ]);
            $agreement = AdoptionAgreement::query()->create([
                'request_id' => $request->getKey(),
                'tree_id' => $tree->getKey(),
                'adopter_id' => $citizen->getKey(),
                'terms' => 'Komitmen perawatan untuk pengujian browser.',
                'starts_on' => now()->toDateString(),
                'ends_on' => now()->addYear()->toDateString(),
                'status' => 'active',
            ]);
            TreeAdoptionAllocation::query()->create([
                'tree_id' => $tree->getKey(),
                'request_id' => $request->getKey(),
                'agreement_id' => $agreement->getKey(),
                'status' => 'active',
                'reserved_at' => now(),
            ]);

            $description = 'Daun pada pohon perlu diperiksa setelah hujan.';

            $this->browse(function (Browser $browser) use ($description, $tree, $evidencePath): void {
                $browser->visit('/login')
                    ->type('input[type="email"]', 'warga@hijauku.test')
                    ->type('input[type="password"]', (string) env('DEMO_PASSWORD', 'demo-hijauku'))
                    ->click('form button[type="submit"]')
                    ->waitForLocation('/warga')
                    ->visit('/warga/laporan-pohon/create')
                    ->waitFor('select[id="form.tree_id"]')
                    ->waitUntil("document.querySelector('input[type=file]') !== null", 10);

                $browser->attach('input[type="file"]', $evidencePath);

                $browser->waitForText('Pengunggahan selesai', 10)
                    ->select('select[id="form.tree_id"]', (string) $tree->getKey())
                    ->select('select[id="form.severity"]', 'urgent')
                    ->type('textarea[wire\\:model="data.description"]', $description)
                    ->press('Kirim Laporan')
                    ->waitForLocation('/warga/laporan-pohon', 15)
                    ->waitForText($tree->code)
                    ->assertSee($description)
                    ->assertSee('Mendesak')
                    ->assertSee('Dilaporkan');
            });

            $issue = TreeIssue::query()
                ->where('tree_id', $tree->getKey())
                ->where('reporter_id', $citizen->getKey())
                ->firstOrFail();

            $this->assertSame('submitted', $issue->status);
            $this->assertSame('urgent', $issue->severity);
            $this->assertNotNull($issue->evidence_path);
            $this->assertTrue(Storage::disk('private')->exists($issue->evidence_path));
        } finally {
            if (is_file($evidencePath)) {
                unlink($evidencePath);
            }

            if ($tree instanceof Tree && $citizen instanceof User) {
                TreeIssue::query()
                    ->reportedBy($citizen)
                    ->where('tree_id', $tree->getKey())
                    ->get()
                    ->each(function (TreeIssue $issue): void {
                        if ($issue->evidence_path !== null) {
                            Storage::disk('private')->delete($issue->evidence_path);
                        }

                        $issue->delete();
                    });

                TreeAdoptionAllocation::query()->where('tree_id', $tree->getKey())->delete();
                AdoptionAgreement::query()->where('tree_id', $tree->getKey())->delete();
                AdoptionRequest::query()->where('tree_id', $tree->getKey())->delete();
                $tree->delete();
            }
        }
    }
}
