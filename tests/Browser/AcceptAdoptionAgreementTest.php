<?php

namespace Tests\Browser;

use App\Models\AdoptionAgreement;
use App\Models\AdoptionRequest;
use App\Models\Tree;
use App\Models\Plot;
use App\Models\Program;
use App\Models\Site;
use App\Models\TreeSpecies;
use App\Models\TreeAdoptionAllocation;
use App\Models\User;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class AcceptAdoptionAgreementTest extends DuskTestCase
{
    public function test_citizen_can_accept_adoption_agreement_from_browser(): void
    {

        $citizen = User::factory()->create([
            'name' => 'Warga Pengujian HAP-38',
            'email' => 'warga-hap38-'.Str::lower(Str::random(8)).'@test.local',
            'password' => bcrypt('password'),
            'role' => \App\Enums\UserRole::Citizen,
            'is_active' => true,
        ]);

        $treeCode = 'DUSK-H38-'.Str::upper(Str::random(6));

        $manager = User::factory()->create([
            'role' => \App\Enums\UserRole::Manager,
            'is_active' => true,
        ]);

        $program = Program::query()->create([
            'name' => 'Program Dusk HAP-38-'.Str::upper(Str::random(6)),
            'objective' => 'Pengujian penerimaan adopsi.',
            'starts_on' => '2026-11-01',
            'ends_on' => '2027-11-01',
            'status' => 'active',
            'created_by' => $manager->getKey(),
        ]);

        $site = Site::query()->create([
            'program_id' => $program->getKey(),
            'name' => 'Lokasi Dusk HAP-38-'.Str::upper(Str::random(6)),
            'address' => 'Lokasi pengujian.',
        ]);

        $plot = Plot::query()->create([
            'site_id' => $site->getKey(),
            'code' => 'PLOT-DUSK-H38-'.Str::upper(Str::random(6)),
            'capacity' => 10,
            'status' => 'open',
        ]);

        $species = TreeSpecies::query()->create([
            'name' => 'Pohon Dusk HAP-38-'.Str::upper(Str::random(6)),
            'care_notes' => 'Perawatan rutin.',
            'is_active' => true,
        ]);

        $tree = Tree::query()->create([
            'species_id' => $species->getKey(),
            'plot_id' => $plot->getKey(),
            'batch_id' => null,
            'code' => $treeCode,
            'initial_condition' => 'healthy',
        ]);

        $request = AdoptionRequest::query()->create([
            'tree_id' => $tree->getKey(),
            'requester_id' => $citizen->getKey(),
            'reason' => 'Pengujian penerimaan kesepakatan HAP-38.',
            'status' => 'approved',

        ]);

        $agreement = AdoptionAgreement::query()->create([
            'request_id' => $request->getKey(),
            'tree_id' => $tree->getKey(),
            'adopter_id' => $citizen->getKey(),
            'terms' => 'Pengadopsi wajib merawat pohon secara rutin.',
            'starts_on' => now()->addDays(3)->toDateString(),
            'ends_on' => now()->addYear()->toDateString(),
            'status' => 'awaiting_acceptance',
        ]);

        $this->browse(function (Browser $browser) use (
            $citizen,
            $treeCode,
        ): void {
            $browser->visit('/login')
                ->type('input[type="email"]', $citizen->email)
                ->type('input[type="password"]', 'password')
                ->click('form button[type="submit"]')
                ->waitForLocation('/warga')
                ->visit('/warga/adoption-agreements')
                ->waitForText($treeCode, 15)
                ->assertSee('Menunggu Persetujuan')
                ->press('Terima Kesepakatan')
                ->waitForText('Terima Kesepakatan Adopsi', 10);

            $browser->script("
                Array.from(document.querySelectorAll('.fi-modal-footer-actions button'))
                    .find(button => button.innerText.trim() === 'Terima Kesepakatan')
                    ?.click();
            ");

            $browser->pause(1000);
        });

        $this->assertSame('active', $agreement->fresh()->status);
        $this->assertNotNull($agreement->fresh()->accepted_at);

        $this->assertDatabaseHas('tree_adoption_allocations', [
            'tree_id' => $tree->getKey(),
            'request_id' => $request->getKey(),
            'agreement_id' => $agreement->getKey(),
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'subject_type' => AdoptionAgreement::class,
            'subject_id' => $agreement->getKey(),
            'actor_id' => $citizen->getKey(),
            'action' => 'adoption_agreement.accepted',
            'from_status' => 'awaiting_acceptance',
            'to_status' => 'active',
        ]);
    }
}
