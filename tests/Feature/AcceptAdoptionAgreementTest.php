<?php

namespace Tests\Feature;

use App\Actions\Adoptions\AcceptAdoptionAgreementAction;
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
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AcceptAdoptionAgreementTest extends TestCase
{
    use RefreshDatabase;

    public function test_citizen_can_accept_their_own_agreement(): void
    {
        $agreement = $this->agreement();
        $citizen = User::query()->findOrFail($agreement->adopter_id);

        $accepted = app(AcceptAdoptionAgreementAction::class)
            ->handle($citizen, $agreement);

        $this->assertSame('active', $accepted->status);
        $this->assertNotNull($accepted->accepted_at);

        $this->assertDatabaseHas('tree_adoption_allocations', [
            'tree_id' => $agreement->tree_id,
            'request_id' => $agreement->request_id,
            'agreement_id' => $agreement->id,
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'actor_id' => $citizen->id,
            'action' => 'adoption_agreement.accepted',
            'from_status' => 'awaiting_acceptance',
            'to_status' => 'active',
        ]);
    }

    public function test_other_citizen_cannot_accept_the_agreement(): void
    {
        $agreement = $this->agreement();

        $otherCitizen = User::factory()->create([
            'role' => UserRole::Citizen,
            'is_active' => true,
        ]);

        $this->assertAuthorizationDenied($otherCitizen, $agreement);

        $this->assertSame(
            'awaiting_acceptance',
            $agreement->fresh()->status
        );

        $this->assertDatabaseCount('tree_adoption_allocations', 0);
    }

    public function test_manager_cannot_accept_citizen_agreement(): void
    {
        $agreement = $this->agreement();

        $manager = User::factory()->create([
            'role' => UserRole::Manager,
            'is_active' => true,
        ]);

        $this->assertAuthorizationDenied($manager, $agreement);

        $this->assertDatabaseCount('tree_adoption_allocations', 0);
    }

    public function test_agreement_cannot_be_accepted_twice(): void
    {
        $agreement = $this->agreement();
        $citizen = User::query()->findOrFail($agreement->adopter_id);

        app(AcceptAdoptionAgreementAction::class)->handle(
            $citizen,
            $agreement
        );

        $this->assertAuthorizationDenied($citizen, $agreement->fresh());

        $this->assertDatabaseCount('tree_adoption_allocations', 1);
    }

    public function test_tree_cannot_be_allocated_to_another_agreement(): void
    {
        $firstAgreement = $this->agreement();
        $citizen = User::query()->findOrFail($firstAgreement->adopter_id);

        $secondCitizen = User::factory()->create([
            'role' => UserRole::Citizen,
            'is_active' => true,
        ]);

        $secondRequest = AdoptionRequest::query()->create([
            'tree_id' => $firstAgreement->tree_id,
            'requester_id' => $secondCitizen->id,
            'reason' => 'Saya ingin merawat pohon.',
            'status' => 'approved',
        ]);

        $secondAgreement = $this->createAgreement($secondRequest);

        app(AcceptAdoptionAgreementAction::class)->handle(
            $citizen,
            $firstAgreement
        );

        $this->assertValidationError(
            $secondCitizen,
            $secondAgreement
        );

        $this->assertDatabaseCount('tree_adoption_allocations', 1);

        $this->assertSame(
            'awaiting_acceptance',
            $secondAgreement->fresh()->status
        );
    }

    private function assertAuthorizationDenied(
        User $actor,
        AdoptionAgreement $agreement
    ): void {
        try {
            app(AcceptAdoptionAgreementAction::class)
                ->handle($actor, $agreement);
        } catch (AuthorizationException) {
            return;
        }

        $this->fail('Pengguna tersebut seharusnya tidak memiliki akses.');
    }

    private function assertValidationError(
        User $actor,
        AdoptionAgreement $agreement
    ): void {
        try {
            app(AcceptAdoptionAgreementAction::class)
                ->handle($actor, $agreement);
        } catch (ValidationException) {
            return;
        }

        $this->fail('Penerimaan kesepakatan seharusnya ditolak.');
    }

    private function agreement(): AdoptionAgreement
    {
        $citizen = User::factory()->create([
            'role' => UserRole::Citizen,
            'is_active' => true,
        ]);

        $request = AdoptionRequest::query()->create([
            'tree_id' => $this->tree()->id,
            'requester_id' => $citizen->id,
            'reason' => 'Saya ingin membantu merawat pohon.',
            'status' => 'approved',
        ]);

        return $this->createAgreement($request);
    }

    private function createAgreement(
        AdoptionRequest $request
    ): AdoptionAgreement {
        return AdoptionAgreement::query()->create([
            'request_id' => $request->id,
            'tree_id' => $request->tree_id,
            'adopter_id' => $request->requester_id,
            'terms' => 'Pengadopsi wajib merawat pohon secara rutin.',
            'starts_on' => '2026-11-20',
            'ends_on' => '2027-11-20',
            'status' => 'awaiting_acceptance',
        ]);
    }

    private function tree(): Tree
    {
        $manager = User::factory()->create([
            'role' => UserRole::Manager,
            'is_active' => true,
        ]);

        $program = Program::query()->create([
            'name' => 'Program Tes Adopsi',
            'objective' => 'Pengujian penerimaan adopsi.',
            'starts_on' => '2026-11-01',
            'ends_on' => '2027-11-01',
            'status' => 'active',
            'created_by' => $manager->id,
        ]);

        $site = Site::query()->create([
            'program_id' => $program->id,
            'name' => 'Lokasi Tes',
            'address' => 'Lokasi pengujian.',
        ]);

        $plot = Plot::query()->create([
            'site_id' => $site->id,
            'code' => 'PLOT-TEST',
            'capacity' => 10,
            'status' => 'open',
        ]);

        $species = TreeSpecies::query()->create([
            'name' => 'Pohon Tes',
            'care_notes' => 'Perawatan rutin.',
            'is_active' => true,
        ]);

        return Tree::query()->create([
            'species_id' => $species->id,
            'plot_id' => $plot->id,
            'code' => 'TREE-TEST',
            'initial_condition' => 'healthy',
        ]);
    }
}
