<?php

namespace Tests\Browser;

use App\Models\AdoptionRequest;
use App\Models\Tree;
use App\Models\User;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class AdoptionRequestCreationTest extends DuskTestCase
{
    public function test_citizen_can_submit_an_adoption_request_from_browser(): void
    {
        $citizen = User::query()
            ->where('email', 'warga@hijauku.test')
            ->firstOrFail();

        $tree = Tree::query()
            ->where('code', 'POHON-DEMO-001')
            ->firstOrFail();

        $reason = 'Pengujian HAP-34 melalui Dusk '.Str::upper(Str::random(8));
        $treeOption = '[role="option"][data-value="'.$tree->getKey().'"]';
        $treeCode = $tree->code;

        $this->browse(function (Browser $browser) use ($reason, $treeOption, $treeCode): void {
            $browser->visit('/warga/login')
                ->type('input[type="email"]', 'warga@hijauku.test')
                ->type('input[type="password"]', (string) env('DEMO_PASSWORD', 'demo-hijauku'))
                ->press('Masuk')
                ->waitForLocation('/warga')
                ->visit('/warga/adoption-requests/create')
                ->waitFor('button[aria-haspopup="listbox"]')
                ->click('button[aria-haspopup="listbox"]')
                ->waitUntil("document.querySelector('input[role=\"combobox\"]')?.getBoundingClientRect().width > 0", 10)
                ->type('input[role="combobox"]', $treeCode)
                ->waitUntil(
                    'document.querySelector('.json_encode($treeOption, JSON_THROW_ON_ERROR).') !== null',
                    10
                )
                ->click($treeOption)
                ->type('textarea[id="form.reason"]', $reason)
                ->press('Kirim Permohonan')
                ->waitForLocation('/warga/adoption-requests', 15)
                ->waitForText($reason, 15)
                ->assertSee('Menunggu Keputusan');
        });

        $request = AdoptionRequest::query()
            ->where('requester_id', $citizen->getKey())
            ->where('reason', $reason)
            ->firstOrFail();

        $this->assertSame($tree->getKey(), $request->tree_id);
        $this->assertSame('submitted', $request->status);

        $this->assertDatabaseHas('activity_logs', [
            'actor_id' => $citizen->getKey(),
            'action' => 'adoption_request.created',
            'subject_id' => $request->getKey(),
            'to_status' => 'submitted',
        ]);
    }
}
