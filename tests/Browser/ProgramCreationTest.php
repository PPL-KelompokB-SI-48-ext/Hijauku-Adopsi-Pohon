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
            $browser->visit('/login')
                ->type('input[type="email"]', 'pengelola@hijauku.test')
                ->type('input[type="password"]', (string) env('DEMO_PASSWORD', 'demo-hijauku'))
                ->click('form button[type="submit"]')
                ->waitForLocation('/app')
                ->visit('/app/programs/create')
                ->type('input[wire\\:model="data.name"]', $programName)
                ->type('textarea[wire\\:model="data.objective"]', 'Menguji pencatatan tujuan program melalui browser.');

            $this->selectProgramDate($browser, 'Tanggal mulai', $startsOnValue);
            $this->selectProgramDate($browser, 'Tanggal akhir', $endsOnValue);

            $browser->press('Simpan sebagai draf')
                ->waitForLocation('/app/programs', 15)
                ->waitForText($programName)
                ->assertSee($programName)
                ->assertSee('Menguji pencatatan tujuan program melalui browser.')
                ->assertSee($startsOn->format('d/m/Y').' - '.$endsOn->format('d/m/Y'))
                ->assertSee('Draf');
        });
    }

    private function selectProgramDate(Browser $browser, string $labelText, string $date): void
    {
        $input = match ($labelText) {
            'Tanggal mulai' => 'input[id="form.starts_on"]',
            'Tanggal akhir' => 'input[id="form.ends_on"]',
            default => throw new \InvalidArgumentException("Label tanggal tidak dikenal: {$labelText}"),
        };
        $calendarPanel = match ($labelText) {
            'Tanggal mulai' => '[id="form.starts_on-panel"]',
            'Tanggal akhir' => '[id="form.ends_on-panel"]',
        };
        $inputSelector = json_encode($input, JSON_THROW_ON_ERROR);
        $dateValue = json_encode($date, JSON_THROW_ON_ERROR);

        $browser->waitUntil(
            "document.querySelector({$inputSelector}) !== null",
            10
        );
        $browser->click($input);
        $browser->waitUntil(
            "document.querySelector({$inputSelector})?.getAttribute('aria-expanded') === 'true'",
            10
        );

        $panelSelector = json_encode($calendarPanel, JSON_THROW_ON_ERROR);
        $browser->script(
            "const panel = document.querySelector({$panelSelector});"
            ."const root = panel?.closest('[x-data]');"
            .'const data = root?._x_dataStack?.[0];'
            ."if (data) { data.focusedDate = dayjs({$dateValue}); data.focusedMonth = data.focusedDate.month(); data.focusedYear = data.focusedDate.year(); data.setupDaysGrid(); data.selectDate(); }"
        );
        $browser->waitUntil(
            "document.querySelector({$inputSelector})?.value !== ''",
            10
        );
    }
}
