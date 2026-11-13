<?php

namespace Tests\Browser;

use App\Models\AdoptionRequest;
use App\Models\Tree;
use App\Models\User;
use Facebook\WebDriver\WebDriverBy;
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

        $this->browse(function (Browser $browser) use ($reason): void {
            $browser->visit('/warga/login')
                ->type('input[type="email"]', 'warga@hijauku.test')
                ->type('input[type="password"]', (string) env('DEMO_PASSWORD', 'demo-hijauku'))
                ->press('Masuk')
                ->waitForLocation('/warga')
                ->visit('/warga/adoption-requests')
                ->waitForText($reason)
                ->assertSee('Menunggu Keputusan');

            $matchingRows = array_values(array_filter(
                $browser->elements('table tbody tr'),
                fn ($row) => str_contains($row->getText(), $reason),
            ));

            $this->assertCount(
                1,
                $matchingRows,
                'Permohonan pengujian tidak ditemukan atau tidak unik.',
            );

            $matchingRows[0]->findElement(
                WebDriverBy::xpath('.//button[contains(., "Tarik Permohonan")]')
            )->click();

            $browser->waitForText('Apakah Anda yakin ingin menarik permohonan adopsi ini?')
                ->press('Ya, Tarik Permohonan');

            $browser->waitUsing(10, 0.1, function () use ($browser, $reason): bool {
                foreach ($browser->elements('table tbody tr') as $row) {
                    $text = $row->getText();

                    if (str_contains($text, $reason)) {
                        return str_contains($text, 'Ditarik')
                            && ! str_contains($text, 'Tarik Permohonan');
                    }
                }

                return false;
            });

            $updatedRows = array_values(array_filter(
                $browser->elements('table tbody tr'),
                fn ($row) => str_contains($row->getText(), $reason),
            ));

            $this->assertCount(1, $updatedRows);

            $rowText = $updatedRows[0]->getText();

            $this->assertStringContainsString('Ditarik', $rowText);
            $this->assertStringNotContainsString('Tarik Permohonan', $rowText);
        });

        $this->assertDatabaseHas('adoption_requests', [
            'id' => $request->id,
            'requester_id' => $citizen->id,
            'status' => 'withdrawn',
        ]);
    }
}
