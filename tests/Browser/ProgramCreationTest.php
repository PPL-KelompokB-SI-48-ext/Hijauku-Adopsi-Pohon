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
        $startsOn = now()->startOfMonth()->addDay();
        $endsOn = $startsOn->copy()->addDay();
        $startsOnValue = $startsOn->toDateString();
        $endsOnValue = $endsOn->toDateString();

        $this->browse(function (Browser $browser) use ($programName, $startsOnValue, $endsOnValue, $startsOn, $endsOn): void {
            $browser->visit('/app/login')
                ->type('input[type="email"]', 'pengelola@hijauku.test')
                ->type('input[type="password"]', (string) env('DEMO_PASSWORD', 'demo-hijauku'))
                ->press('Masuk')
                ->waitForLocation('/app')
                ->visit('/app/programs/create')
                ->type('input[wire\\:model="data.name"]', $programName)
                ->type('textarea[wire\\:model="data.objective"]', 'Menguji pencatatan tujuan program melalui browser.');

            $this->selectProgramDate($browser, 'Tanggal mulai', $startsOnValue);
            $this->selectProgramDate($browser, 'Tanggal akhir', $endsOnValue);

            $browser->press('Simpan sebagai draf')
                ->waitForText($programName)
                ->assertSee($programName)
                ->assertSee('Menguji pencatatan tujuan program melalui browser.')
                ->assertSee($startsOn->format('d/m/Y').' - '.$endsOn->format('d/m/Y'))
                ->assertSee('Draf');
        });
    }

    private function selectProgramDate(Browser $browser, string $labelText, string $date): void
    {
        $labelText = json_encode($labelText, JSON_THROW_ON_ERROR);

        $browser->script(<<<JS
            const label = Array.from(document.querySelectorAll('label'))
                .find((element) => element.textContent.includes({$labelText}));
            const field = label?.closest('[data-field-wrapper]');
            const trigger = field?.querySelector('.fi-fo-date-time-picker-trigger');

            if (! trigger) {
                throw new Error(`Pemilih tanggal {$labelText} tidak ditemukan.`);
            }

            trigger.click();
            JS);

        $browser->waitFor('div.fi-fo-date-time-picker-panel:not([style*="display: none"])');

        $day = json_encode((string) ((int) substr($date, -2)), JSON_THROW_ON_ERROR);

        $browser->script(<<<JS
            const day = {$day};
            const cell = Array.from(document.querySelectorAll('.fi-fo-date-time-picker-calendar-day'))
                .find((element) => element.offsetParent && element.textContent.trim() === day);

            if (! cell) {
                throw new Error(`Tanggal {$day} tidak tersedia di kalender yang terbuka.`);
            }

            cell.click();
            JS);
    }
}
