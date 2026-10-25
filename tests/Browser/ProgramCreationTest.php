<?php

namespace Tests\Browser;

use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ProgramCreationTest extends DuskTestCase
{
    public function test_manager_can_create_a_draft_program_from_the_filament_panel(): void
    {
        $programName = 'Program Browser '.Str::upper(Str::random(8));

        $this->browse(function (Browser $browser) use ($programName): void {
            $browser->visit('/app/login')
                ->type('input[type="email"]', 'pengelola@hijauku.test')
                ->type('input[type="password"]', (string) env('DEMO_PASSWORD', 'demo-hijauku'))
                ->press('Masuk')
                ->waitForLocation('/app')
                ->visit('/app/programs/create')
                ->type('input[id="data.name"]', $programName)
                ->type('textarea[id="data.objective"]', 'Menguji pencatatan tujuan program melalui browser.');

            $browser->script(<<<'JS'
                const dates = {
                    'data.starts_on': '2026-10-26',
                    'data.ends_on': '2026-10-31',
                };

                for (const [name, value] of Object.entries(dates)) {
                    const input = document.getElementById(name);

                    if (! input) {
                        throw new Error(`Kolom tanggal ${name} tidak ditemukan.`);
                    }

                    if (input._flatpickr) {
                        input._flatpickr.setDate(value, true);
                        continue;
                    }

                    const setter = Object.getOwnPropertyDescriptor(
                        HTMLInputElement.prototype,
                        'value',
                    ).set;

                    setter.call(input, value);
                    input.dispatchEvent(new Event('input', { bubbles: true }));
                    input.dispatchEvent(new Event('change', { bubbles: true }));
                }
                JS);

            $browser->press('Simpan sebagai draf')
                ->waitForText($programName)
                ->assertSee($programName)
                ->assertSee('Menguji pencatatan tujuan program melalui browser.')
                ->assertSee('26/10/2026 - 31/10/2026')
                ->assertSee('Draf');
        });
    }
}
