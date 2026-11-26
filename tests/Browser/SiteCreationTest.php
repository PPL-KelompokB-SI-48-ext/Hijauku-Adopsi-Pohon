<?php

namespace Tests\Browser;

use App\Models\Program;
use App\Models\Site;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class SiteCreationTest extends DuskTestCase
{
    public function test_manager_can_create_a_location_from_the_filament_panel(): void
    {
        $name = 'Lokasi Browser '.Str::upper(Str::random(8));
        $address = 'Jalan Uji Browser, Bandung';

        $this->browse(function (Browser $browser) use ($name, $address): void {
            $browser->visit('/login')
                ->type('input[type="email"]', 'pengelola@hijauku.test')
                ->type('input[type="password"]', (string) env('DEMO_PASSWORD', 'demo-hijauku'))
                ->click('form button[type="submit"]')
                ->waitForLocation('/app')
                ->visit('/app/sites/create')
                ->click('button[id="form.program_id"]')
                ->waitFor('[role="option"]')
                ->click('[role="option"]')
                ->type('input[wire\\:model="data.name"]', $name)
                ->type('textarea[wire\\:model="data.address"]', $address)
                ->type('input[wire\\:model="data.latitude"]', '-6.914744')
                ->type('input[wire\\:model="data.longitude"]', '107.609810')
                ->press('Simpan lokasi')
                ->waitForLocation('/app/sites', 15)
                ->waitForText($name)
                ->assertSee($name)
                ->assertSee($address)
                ->assertSee('-6.914744, 107.609810')
                ->resize(390, 844)
                ->assertVisible('.fi-ta-search-field')
                ->assertScript('(() => { const filter = document.querySelector(".fi-ta-filters-above-content-ctn .fi-select-input-btn"); return filter && filter.getBoundingClientRect().width > 0; })()')
                ->assertScript('document.documentElement.scrollWidth <= window.innerWidth')
                ->assertScript('(() => { const table = document.querySelector(".fi-ta-content-ctn"); return table && table.scrollWidth > table.clientWidth; })()');
        });
    }

    public function test_location_edit_modal_renders_above_the_sidebar_at_desktop_breakpoint(): void
    {
        $program = Program::query()->firstOrFail();
        $site = Site::query()->create([
            'program_id' => $program->getKey(),
            'name' => 'Lokasi Modal '.Str::upper(Str::random(8)),
            'address' => 'Jalan Uji Modal, Bandung',
        ]);

        $this->browse(function (Browser $browser) use ($site): void {
            $rowSelector = 'tr[wire\\:key$=".table.records.'.$site->getKey().'"]';

            $browser->resize(1024, 768)->visit('/login');

            if ($browser->element('input[type="email"]') !== null) {
                $browser->type('input[type="email"]', 'pengelola@hijauku.test')
                    ->type('input[type="password"]', (string) env('DEMO_PASSWORD', 'demo-hijauku'))
                    ->click('form button[type="submit"]')
                    ->waitForLocation('/app');
            }

            $browser->visit('/app/sites')
                ->with($rowSelector, function (Browser $row): void {
                    $row->press('Edit');
                })
                ->waitForText('Ubah lokasi tanam')
                ->assertScript('(() => { const modal = document.querySelector(".fi-modal-window-ctn"); const sidebar = document.querySelector(".fi-sidebar"); return modal && sidebar && Number(getComputedStyle(modal).zIndex) > Number(getComputedStyle(sidebar).zIndex); })()')
                ->press('Batal')
                ->waitUntilMissingText('Ubah lokasi tanam', 15);
        });
    }
}
