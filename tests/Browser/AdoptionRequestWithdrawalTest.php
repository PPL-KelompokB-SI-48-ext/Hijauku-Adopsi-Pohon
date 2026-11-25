<?php

namespace Tests\Browser;

use App\Models\AdoptionRequest;
use App\Models\Tree;
use App\Models\User;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class AdoptionRequestWithdrawalTest extends DuskTestCase
{
    public function test_citizen_can_withdraw_submitted_adoption_request(): void
    {
        $citizen = User::query()
            ->where('email', 'warga@hijauku.test')
            ->firstOrFail();

        $tree = Tree::query()
            ->where('code', 'POHON-DEMO-001')
            ->firstOrFail();

        AdoptionRequest::query()
            ->where('requester_id', $citizen->getKey())
            ->where('reason', 'like', 'Pengujian penarikan HAP-36 melalui Dusk%')
            ->get()
            ->each(function (AdoptionRequest $oldRequest): void {
                $oldRequest->activityLogs()->delete();
                $oldRequest->delete();
            });

        $reason = 'DUSK-H36-'.Str::upper(Str::random(4));

        $request = AdoptionRequest::query()->create([
            'tree_id' => $tree->id,
            'requester_id' => $citizen->id,
            'reason' => $reason,
            'status' => 'submitted',
        ]);

        $this->browse(function (Browser $browser) use ($request): void {
            $rowSelector = 'tr[wire\\:key$=".table.records.'.$request->getKey().'"]';

            $browser->visit('/warga/login')
                ->type('input[type="email"]', 'warga@hijauku.test')
                ->type('input[type="password"]', (string) env('DEMO_PASSWORD', 'demo-hijauku'))
                ->press('Masuk')
                ->waitForLocation('/warga')
                ->visit('/warga/adoption-requests')
                ->waitUsing(30, 0.2, fn (): bool => $request->fresh()->status === 'submitted')
                ->waitFor($rowSelector, 30)
                ->with($rowSelector, function (Browser $row): void {
                    $row->press('Tarik Permohonan');
                });

            $browser->waitForText('Apakah Anda yakin ingin menarik permohonan adopsi ini?')
                ->press('Ya, Tarik Permohonan');

            $browser->waitUsing(10, 0.1, fn (): bool => $request->fresh()->status === 'withdrawn');
        });

        $this->assertDatabaseHas('adoption_requests', [
            'id' => $request->id,
            'requester_id' => $citizen->id,
            'status' => 'withdrawn',
        ]);
    }
}
