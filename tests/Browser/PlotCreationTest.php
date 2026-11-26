<?php

namespace Tests\Browser;

use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class PlotCreationTest extends DuskTestCase
{
    public function test_manager_can_create_a_plot_and_view_the_responsive_list(): void
    {
        $code = 'DUSK-'.Str::upper(Str::random(8));

        $this->browse(function (Browser $browser) use ($code): void {
            $browser->visit('/login')
                ->type('input[type="email"]', 'pengelola@hijauku.test')
                ->type('input[type="password"]', (string) env('DEMO_PASSWORD', 'demo-hijauku'))
                ->click('form button[type="submit"]')
                ->waitForLocation('/app')
                ->visit('/app/plots/create')
                ->click('button[id="form.site_id"]')
                ->waitFor('[role="option"]')
                ->click('[role="option"]')
                ->type('input[wire\\:model="data.code"]', $code)
                ->type('input[wire\\:model="data.capacity"]', '25')
                ->press('Simpan petak')
                ->waitForLocation('/app/plots', 15)
                ->waitForText($code)
                ->assertSee($code)
                ->assertSee('25 Pohon')
                ->waitForText('TOTAL KAPASITAS', 15)
                ->assertSee('Atur Ulang')
                ->type('.fi-ta-search-field input', 'TIDAK-ADA-PL')
                ->waitForText('Belum ada petak tanam', 10)
                ->press('Atur Ulang')
                ->waitForText($code, 10)
                ->visit('/app/plots/create')
                ->click('button[id="form.site_id"]')
                ->waitFor('[role="option"]')
                ->click('[role="option"]')
                ->type('input[wire\\:model="data.code"]', $code)
                ->type('input[wire\\:model="data.capacity"]', '30')
                ->press('Simpan petak')
                ->waitForText('Kode petak sudah digunakan pada lokasi ini.', 15)
                ->assertSee('Kode petak sudah digunakan pada lokasi ini.')
                ->visit('/app/plots')
                ->resize(390, 844)
                ->assertVisible('.fi-ta-search-field')
                ->assertVisible('.fi-ta-filters-above-content-ctn .fi-select-input-btn')
                ->assertScript('getComputedStyle(document.querySelector(".fi-sidebar-item.fi-active > .fi-sidebar-item-btn")).backgroundColor', 'rgb(220, 252, 231)')
                ->assertScript('document.documentElement.scrollWidth <= window.innerWidth')
                ->assertSee($code);
        });
    }
}
