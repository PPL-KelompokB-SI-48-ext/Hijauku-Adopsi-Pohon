<?php

namespace Tests\Browser;

use App\Models\TreeSpecies;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class TreeSpeciesManagementTest extends DuskTestCase
{
    public function test_manager_can_create_and_update_tree_species_from_the_filament_panel(): void
    {
        $speciesName = 'Spesies Dusk '.Str::upper(Str::random(6));
        $initialCareNotes = 'Penyiraman rutin setiap pagi dan sore.';
        $updatedCareNotes = 'Penyiraman disesuaikan musim kemarau.';
        $updateReason = 'Penyesuaian protokol perawatan berkala.';

        $this->browse(function (Browser $browser) use ($speciesName, $initialCareNotes, $updatedCareNotes, $updateReason): void {
            $browser->visit('/login')
                ->type('input[type="email"]', 'pengelola@hijauku.test')
                ->type('input[type="password"]', (string) env('DEMO_PASSWORD', 'demo-hijauku'))
                ->click('form button[type="submit"]')
                ->waitForLocation('/app')
                ->visit('/app/tree-species/create')
                ->waitForLocation('/app/tree-species/create')
                ->type('input[wire\\:model="data.name"]', $speciesName)
                ->type('textarea[wire\\:model="data.care_notes"]', $initialCareNotes)
                ->press('Simpan Jenis Pohon')
                ->waitForLocation('/app/tree-species', 15)
                ->waitForText($speciesName)
                ->assertSee($speciesName)
                ->assertSee($initialCareNotes);

            $species = TreeSpecies::where('name', $speciesName)->firstOrFail();

            $browser->visit("/app/tree-species/{$species->getKey()}/edit")
                ->waitForLocation("/app/tree-species/{$species->getKey()}/edit")
                ->type('textarea[wire\\:model="data.care_notes"]', $updatedCareNotes)
                ->type('textarea[wire\\:model="data.reason"]', $updateReason)
                ->press('Simpan Perubahan')
                ->waitForLocation('/app/tree-species', 15)
                ->waitForText($speciesName)
                ->assertSee($speciesName)
                ->assertSee($updatedCareNotes);
        });
    }
}
