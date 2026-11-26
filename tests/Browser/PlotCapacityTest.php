<?php

namespace Tests\Browser;

use App\Models\Plot;
use App\Models\Tree;
use App\Models\TreeSpecies;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class PlotCapacityTest extends DuskTestCase
{
    public function test_manager_sees_the_near_capacity_warning_on_the_plot_list(): void
    {
        $species = TreeSpecies::factory()->create(['is_active' => true]);
        $plot = Plot::factory()->create([
            'code' => 'DUSK-WARN-'.Str::upper(Str::random(5)),
            'capacity' => 10,
        ]);

        Tree::factory()->count(9)->create([
            'species_id' => $species->getKey(),
            'plot_id' => $plot->getKey(),
        ]);

        $this->browse(function (Browser $browser) use ($plot): void {
            $browser->driver->manage()->deleteAllCookies();

            $browser->visit('/login')
                ->type('input[type="email"]', 'pengelola@hijauku.test')
                ->type('input[type="password"]', (string) env('DEMO_PASSWORD', 'demo-hijauku'))
                ->click('form button[type="submit"]')
                ->waitForLocation('/app')
                ->visit('/app/plots')
                ->waitForText($plot->code, 15)
                ->assertSee('9 Pohon')
                ->assertSee('90%')
                ->assertSee('Hampir penuh');
        });
    }

    public function test_field_staff_does_not_get_full_plots_as_tree_creation_options(): void
    {
        $fullPlot = Plot::factory()->create([
            'code' => 'DUSK-FULL-'.Str::upper(Str::random(5)),
            'capacity' => 1,
        ]);
        $availablePlot = Plot::factory()->create([
            'code' => 'DUSK-OPEN-'.Str::upper(Str::random(5)),
            'capacity' => 2,
        ]);

        Tree::factory()->create([
            'plot_id' => $fullPlot->getKey(),
            'code' => 'DUSK-TREE-'.Str::upper(Str::random(6)),
        ]);

        $this->browse(function (Browser $browser) use ($availablePlot, $fullPlot): void {
            $browser->driver->manage()->deleteAllCookies();

            $browser->visit('/login')
                ->type('input[type="email"]', 'petugas@hijauku.test')
                ->type('input[type="password"]', (string) env('DEMO_PASSWORD', 'demo-hijauku'))
                ->click('form button[type="submit"]')
                ->waitForLocation('/petugas')
                ->visit('/petugas/trees/create')
                ->waitFor('button[id="form.plot_id"]', 15)
                ->click('button[id="form.plot_id"]')
                ->waitForText($availablePlot->code, 10)
                ->assertSee($availablePlot->code)
                ->assertDontSee($fullPlot->code)
                ->assertSee('Petak yang sudah penuh tidak tersedia.');
        });
    }
}
