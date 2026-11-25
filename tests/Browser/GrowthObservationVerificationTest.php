<?php

namespace Tests\Browser;

use App\Models\GrowthObservation;
use App\Models\Tree;
use App\Models\User;
use Facebook\WebDriver\WebDriverBy;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class GrowthObservationVerificationTest extends DuskTestCase
{
    public function test_manager_can_verify_observation_and_view_non_sdg_growth_summary(): void
    {
        $manager = User::query()
            ->where('email', 'pengelola@hijauku.test')
            ->firstOrFail();

        $staff = User::query()
            ->where('email', 'petugas@hijauku.test')
            ->firstOrFail();

        $sourceTree = Tree::query()
            ->where('code', 'POHON-DEMO-001')
            ->firstOrFail();

        $tree = Tree::query()->create([
            'species_id' => $sourceTree->species_id,
            'plot_id' => $sourceTree->plot_id,
            'batch_id' => null,
            'code' => 'DUSK-H50-'.Str::upper(Str::random(6)),
            'planted_on' => now()->subMonths(2)->toDateString(),
            'initial_condition' => $sourceTree->initial_condition,
        ]);

        $observation = GrowthObservation::query()->create([
            'tree_id' => $tree->getKey(),
            'observed_by' => $staff->getKey(),
            'observed_on' => now()->toDateString(),
            'height_cm' => 230.00,
            'diameter_cm' => 15.20,
            'condition' => GrowthObservation::CONDITION_HEALTHY,
            'status' => 'submitted',
        ]);

        $this->browse(function (Browser $browser) use ($manager, $tree): void {
            $browser->resize(1440, 900);
            $browser->driver->manage()->deleteAllCookies();

            $browser->loginAs($manager)
                ->visit('/app/growth-observations')
                ->waitForText($tree->code, 15)
                ->assertSee('Grafik Kondisi Fisik Pohon (Non-SDG)')
                ->assertSee('tidak mengklaim angka atau capaian SDG')
                ->assertSee('Pohon Belum Terpantau (>30 Hari)');

            $rows = array_values(array_filter(
                $browser->elements('table tbody tr'),
                fn ($row) => str_contains($row->getText(), $tree->code),
            ));

            $this->assertNotEmpty($rows);

            $rows[0]->findElement(
                WebDriverBy::xpath('.//button[contains(., "Verifikasi")]')
            )->click();

            $browser->waitForText('Verifikasi Pengamatan Pertumbuhan', 10)
                ->press('Ya, Verifikasi');

            $browser->waitUsing(15, 0.2, function () use ($browser, $tree): bool {
                foreach ($browser->elements('table tbody tr') as $row) {
                    if (str_contains($row->getText(), $tree->code)) {
                        return str_contains($row->getText(), 'Terverifikasi');
                    }
                }

                return false;
            });

            $browser->resize(390, 844)
                ->assertScript('document.documentElement.scrollWidth <= window.innerWidth');
        });

        $observation->refresh();

        $this->assertSame('verified', $observation->status);
        $this->assertSame($manager->getKey(), $observation->verified_by);

        $this->assertDatabaseHas('activity_logs', [
            'actor_id' => $manager->getKey(),
            'action' => 'growth_observation.verified',
            'subject_type' => GrowthObservation::class,
            'subject_id' => $observation->getKey(),
            'from_status' => 'submitted',
            'to_status' => 'verified',
        ]);
    }
}
