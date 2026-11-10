<?php

namespace Tests\Browser;

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
            $browser->visit('/app/login')
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
                ->assertSee('-6.914744, 107.609810');
        });
    }
}
