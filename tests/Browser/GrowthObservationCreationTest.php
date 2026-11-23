<?php

namespace Tests\Browser;

use App\Models\GrowthObservation;
use App\Models\Tree;
use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class GrowthObservationCreationTest extends DuskTestCase
{
    public function test_field_staff_can_record_a_growth_observation_from_the_filament_panel(): void
    {
        $staff = User::query()
            ->where('email', 'petugas@hijauku.test')
            ->firstOrFail();

        $tree = Tree::query()
            ->where('code', 'POHON-DEMO-001')
            ->firstOrFail();

        $observedOn = now()->toDateString();

        GrowthObservation::query()
            ->where('tree_id', $tree->getKey())
            ->whereDate('observed_on', $observedOn)
            ->delete();

        $treeOption = '[role="option"][data-value="'.$tree->getKey().'"]';
        $conditionOption = '[role="option"][data-value="'.GrowthObservation::CONDITION_HEALTHY.'"]';

        $this->browse(function (Browser $browser) use ($staff, $tree, $treeOption, $conditionOption): void {
            $browser->resize(1440, 900);
            $browser->driver->manage()->deleteAllCookies();

            $browser->loginAs($staff)
                ->visit('/petugas/growth-observations/create')
                ->waitFor('button[id="form.tree_id"]')
                ->click('button[id="form.tree_id"]')
                ->waitUntil(
                    "document.querySelector('input[role=\"combobox\"]')?.getBoundingClientRect().width > 0",
                    10
                )
                ->type('input[role="combobox"]', $tree->code)
                ->waitUntil(
                    'document.querySelector('.json_encode($treeOption, JSON_THROW_ON_ERROR).') !== null',
                    10
                )
                ->click($treeOption)
                ->type('input[wire\\:model="data.height_cm"]', '215.5')
                ->type('input[wire\\:model="data.diameter_cm"]', '14.8')
                ->click('button[id="form.condition"]')
                ->waitFor($conditionOption, 10)
                ->click($conditionOption)
                ->press('Simpan pengamatan')
                ->waitForLocation('/petugas/growth-observations', 15)
                ->waitForText($tree->code, 15)
                ->assertSee('215,50 cm')
                ->assertSee('14,80 cm')
                ->assertSee('Baik / Sehat')
                ->assertSee('Diajukan')
                ->resize(390, 844)
                ->assertScript('document.documentElement.scrollWidth <= window.innerWidth');
        });

        $observation = GrowthObservation::query()
            ->where('tree_id', $tree->getKey())
            ->where('observed_by', $staff->getKey())
            ->whereDate('observed_on', $observedOn)
            ->firstOrFail();

        $this->assertSame('submitted', $observation->status);
        $this->assertSame(GrowthObservation::CONDITION_HEALTHY, $observation->condition);

        $this->assertDatabaseHas('activity_logs', [
            'actor_id' => $staff->getKey(),
            'action' => 'growth_observation.created',
            'subject_type' => GrowthObservation::class,
            'subject_id' => $observation->getKey(),
            'to_status' => 'submitted',
        ]);
    }
}
