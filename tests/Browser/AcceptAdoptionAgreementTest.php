<?php

namespace Tests\Browser;

use App\Enums\UserRole;
use App\Models\AdoptionAgreement;
use App\Models\AdoptionRequest;
use App\Models\Plot;
use App\Models\Program;
use App\Models\Site;
use App\Models\Tree;
use App\Models\TreeAdoptionAllocation;
use App\Models\TreeSpecies;
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
            'role' => UserRole::Citizen,
            'is_active' => true,
        ]);

        $manager = User::factory()->create([
            'role' => UserRole::Manager,
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
            'code' => 'DUSK-H38-'.Str::upper(Str::random(6)),
            'initial_condition' => 'healthy',
        ]);

        $request = AdoptionRequest::query()->create([
            'tree_id' => $tree->getKey(),
            'requester_id' => $citizen->getKey(),
            'reason' => 'Pengujian penerimaan kesepakatan HAP-38.',
            'status' => 'approved',
            'reviewer_id' => $manager->getKey(),
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

        $allocation = TreeAdoptionAllocation::query()->create([
            'tree_id' => $tree->getKey(),
            'request_id' => $request->getKey(),
            'agreement_id' => $agreement->getKey(),
            'status' => 'awaiting_acceptance',
            'reserved_at' => now(),
        ]);

        $treeCode = $tree->code;

        $rowSelector = 'tr[wire\\:key$=".table.records.'.$agreement->getKey().'"]';

        $this->browse(function (Browser $browser) use (
            $citizen,
            $treeCode,
            $rowSelector,
        ): void {
            $browser->loginAs($citizen)
                ->visit('/warga/adopsi-saya')
                ->waitForText('Dokumen Legalitas & Komitmen', 15)
                ->assertSee('Kesepakatan Adopsi Saya')
                ->assertSee('POHON')
                ->assertSee('NO. KESEPAKATAN')
                ->assertSee('PERIODE ADOPSI')
                ->assertSee('KETENTUAN (RINGKAS)')
                ->assertSee('STATUS KESEPAKATAN')
                ->waitFor($rowSelector, 15)
                ->with($rowSelector, function (Browser $row) use ($treeCode): void {
                    $row->assertSee($treeCode)
                        ->assertSee('Menunggu Persetujuan Warga')
                        ->press('Terima Kesepakatan');
                });

            $browser->waitForText('Terima Kesepakatan Adopsi', 10)
                ->with('.fi-modal', function (Browser $modal): void {
                    $modal->press('Terima Kesepakatan');
                })
                ->waitUntilMissingText('Terima Kesepakatan Adopsi', 15)
                ->with($rowSelector, function (Browser $row) use ($treeCode): void {
                    $row->assertSee($treeCode)
                        ->assertSee('Aktif')
                        ->assertDontSee('Menunggu Persetujuan Warga');
                });
        });

        $this->assertSame('active', $agreement->fresh()->status);
        $this->assertNotNull($agreement->fresh()->accepted_at);

        $this->assertDatabaseHas('tree_adoption_allocations', [
            'id' => $allocation->getKey(),
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