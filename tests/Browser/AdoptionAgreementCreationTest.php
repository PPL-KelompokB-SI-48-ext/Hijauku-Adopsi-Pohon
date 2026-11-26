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

        // Keep both test dates in the same calendar month so the date picker
        // does not need a month-navigation step near the end of a month.
        $startsOn = now()->startOfMonth()->addDay();
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
        $calendarPanel = '[id="form.'.$field.'-panel"]';

        $inputSelector = json_encode($input, JSON_THROW_ON_ERROR);

        $calendarDaySelector = $calendarPanel
            .' .fi-fo-date-time-picker-calendar-day[data-date="'.$date.'"]';

        $browser->waitUntil(
            "document.querySelector({$inputSelector})?.parentElement?._x_dataStack?.length > 0",
            10
        );

        $browser->click($input);

        $browser->waitUntil(
            "document.querySelector({$inputSelector})?.getAttribute('aria-expanded') === 'true'",
            10
        );

        $browser->waitFor($calendarDaySelector, 10)
            ->click($calendarDaySelector);

        $browser->waitUntil(
            "document.querySelector({$inputSelector})?.value !== ''",
            10
        );
    }
}
