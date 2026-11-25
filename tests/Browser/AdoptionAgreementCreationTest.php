<?php

namespace Tests\Browser;

use App\Models\AdoptionAgreement;
use App\Models\AdoptionRequest;
use App\Models\Tree;
use App\Models\User;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class AdoptionAgreementCreationTest extends DuskTestCase
{
    public function test_manager_can_create_adoption_agreement_from_browser(): void
    {
        $manager = User::query()
            ->where('email', 'pengelola@hijauku.test')
            ->firstOrFail();

        $citizen = User::query()
            ->where('email', 'warga@hijauku.test')
            ->firstOrFail();

        $sourceTree = Tree::query()
            ->where('code', 'POHON-DEMO-001')
            ->firstOrFail();

        $treeCode = 'DUSK-H37-'.Str::upper(Str::random(6));
        $reason = 'Pengujian kesepakatan HAP-37 '.$treeCode;
        $terms = 'Pengadopsi wajib menyiram dan merawat pohon secara rutin.';

        $tree = Tree::query()->create([
            'species_id' => $sourceTree->species_id,
            'plot_id' => $sourceTree->plot_id,
            'batch_id' => null,
            'code' => $treeCode,
            'initial_condition' => $sourceTree->initial_condition,
        ]);

        $request = AdoptionRequest::query()->create([
            'tree_id' => $tree->getKey(),
            'requester_id' => $citizen->getKey(),
            'reason' => $reason,
            'status' => 'approved',
            'reviewer_id' => $manager->getKey(),
        ]);

        $startsOn = now()->addDays(3)->startOfDay();
        $endsOn = $startsOn->copy()->addDays(3);

        $startsOnValue = $startsOn->toDateString();
        $endsOnValue = $endsOn->toDateString();

        $this->browse(function (Browser $browser) use (
            $request,
            $treeCode,
            $citizen,
            $terms,
            $startsOnValue,
            $endsOnValue,
            $startsOn,
            $endsOn,
        ): void {
            $browser->visit('/login')
                ->type('input[type="email"]', 'pengelola@hijauku.test')
                ->type('input[type="password"]', (string) env('DEMO_PASSWORD', 'demo-hijauku'))
                ->click('form button[type="submit"]')
                ->waitForLocation('/app')
                ->visit('/app/adoption-agreements/create')
                ->waitFor('button[id="form.request_id"]', 15)
                ->click('button[id="form.request_id"]');

            $browser->waitUntil(
                "document.querySelector('button[id=\"form.request_id\"]')?.getAttribute('aria-expanded') === 'true'",
                10
            );

            $optionSelector = '[role="option"][data-value="'.$request->getKey().'"]';

            $browser->waitFor($optionSelector, 10)
                ->click($optionSelector)
                ->assertSee($treeCode)
                ->type('textarea[wire\\:model="data.terms"]', $terms);

            $this->selectAgreementDate($browser, 'starts_on', $startsOnValue);
            $this->selectAgreementDate($browser, 'ends_on', $endsOnValue);

            $browser->press('Simpan Kesepakatan')
                ->waitForLocation('/app/adoption-agreements', 15)
                ->waitForText($treeCode)
                ->assertSee($citizen->name)
                ->assertSee($startsOn->format('d/m/Y').' - '.$endsOn->format('d/m/Y'))
                ->assertSee('Menunggu Warga');
        });

        $agreement = AdoptionAgreement::query()
            ->where('request_id', $request->getKey())
            ->firstOrFail();

        $this->assertSame($tree->getKey(), $agreement->tree_id);
        $this->assertSame($citizen->getKey(), $agreement->adopter_id);
        $this->assertSame('awaiting_acceptance', $agreement->status);
        $this->assertSame($terms, $agreement->terms);
        $this->assertSame($startsOnValue, $agreement->starts_on->toDateString());
        $this->assertSame($endsOnValue, $agreement->ends_on->toDateString());

        $this->assertDatabaseHas('activity_logs', [
            'subject_type' => AdoptionAgreement::class,
            'subject_id' => $agreement->getKey(),
            'actor_id' => $manager->getKey(),
            'action' => 'adoption_agreement.created',
            'to_status' => 'awaiting_acceptance',
        ]);
    }

    private function selectAgreementDate(
        Browser $browser,
        string $field,
        string $date
    ): void {
        $input = 'input[id="form.'.$field.'"]';
        $inputSelector = json_encode($input, JSON_THROW_ON_ERROR);
        $dateValue = json_encode($date, JSON_THROW_ON_ERROR);
        $calendarPanel = '[id="form.'.$field.'-panel"]';

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
