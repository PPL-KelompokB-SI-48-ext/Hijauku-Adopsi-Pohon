<?php

namespace Tests\Feature;

use App\Actions\Adoptions\CreateAdoptionAgreementAction;
use App\Actions\Adoptions\CreateAdoptionRequestAction;
use App\Enums\UserRole;
use App\Models\AdoptionAgreement;
use App\Models\AdoptionRequest;
use App\Models\Plot;
use App\Models\Program;
use App\Models\Site;
use App\Models\Tree;
use App\Models\TreeSpecies;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AdoptionAgreementCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_create_agreement_from_approved_request(): void
    {
        $manager = $this->manager();
        $request = $this->adoptionRequest('approved');

        $agreement = app(CreateAdoptionAgreementAction::class)->handle($manager, [
            'request_id' => $request->id,
            'starts_on' => '2026-11-20',
            'ends_on' => '2027-11-20',
            'terms' => 'Pengadopsi wajib merawat pohon secara rutin.',
            'tree_id' => 999,
            'adopter_id' => $manager->id,
            'status' => 'active',
            'accepted_at' => '2026-11-20',
        ]);

        $this->assertSame($request->id, $agreement->request_id);
        $this->assertSame($request->tree_id, $agreement->tree_id);
        $this->assertSame($request->requester_id, $agreement->adopter_id);
        $this->assertSame('awaiting_acceptance', $agreement->status);
        $this->assertNull($agreement->accepted_at);

        $this->assertDatabaseHas('adoption_agreements', [
            'id' => $agreement->id,
            'request_id' => $request->id,
            'tree_id' => $request->tree_id,
            'adopter_id' => $request->requester_id,
            'status' => 'awaiting_acceptance',
        ]);

        $this->assertDatabaseHas('tree_adoption_allocations', [
            'tree_id' => $request->tree_id,
            'request_id' => $request->id,
            'agreement_id' => $agreement->id,
            'status' => 'awaiting_acceptance',
        ]);

        $this->assertDatabaseCount('tree_adoption_allocations', 1);

        $savedAgreement = AdoptionAgreement::query()->findOrFail($agreement->id);

        $this->assertSame(
            '2026-11-20',
            $savedAgreement->starts_on->toDateString()
        );

        $this->assertSame(
            '2027-11-20',
            $savedAgreement->ends_on->toDateString()
        );

        $this->assertSame(
            'Pengadopsi wajib merawat pohon secara rutin.',
            $savedAgreement->terms
        );

        $log = $agreement->activityLogs()->firstOrFail();

        $this->assertSame($manager->id, $log->actor_id);
        $this->assertSame('adoption_agreement.created', $log->action);
        $this->assertNull($log->from_status);
        $this->assertSame('awaiting_acceptance', $log->to_status);
        $this->assertSame($request->id, $log->metadata['request_id']);
        $this->assertNotNull($log->created_at);
    }

    public function test_manager_cannot_create_agreement_from_unapproved_request(): void
    {
        $manager = $this->manager();
        $request = $this->adoptionRequest('submitted');

        $this->assertValidationError(
            $manager,
            $this->agreementData($request),
            'request_id'
        );

        $this->assertDatabaseCount('adoption_agreements', 0);
        $this->assertDatabaseCount('activity_logs', 0);
    }

    public function test_pending_agreement_reservation_prevents_another_citizen_from_requesting_tree(): void
    {
        $manager = $this->manager();
        $request = $this->adoptionRequest('approved');

        $agreement = app(CreateAdoptionAgreementAction::class)->handle(
            $manager,
            $this->agreementData($request)
        );

        $secondCitizen = User::factory()->create([
            'role' => UserRole::Citizen,
            'is_active' => true,
        ]);

        try {
            app(CreateAdoptionRequestAction::class)->handle($secondCitizen, [
                'tree_id' => $request->tree_id,
                'reason' => 'Saya ingin membantu merawat pohon.',
            ]);

            $this->fail('Pohon yang sedang direservasi seharusnya tidak tersedia.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('tree_id', $exception->errors());
        }

        $this->assertDatabaseCount('adoption_requests', 1);
        $this->assertDatabaseCount('adoption_agreements', 1);
        $this->assertDatabaseCount('tree_adoption_allocations', 1);

        $this->assertSame('awaiting_acceptance', $agreement->fresh()->status);

        $this->assertDatabaseHas('tree_adoption_allocations', [
            'tree_id' => $request->tree_id,
            'request_id' => $request->id,
            'agreement_id' => $agreement->id,
            'status' => 'awaiting_acceptance',
        ]);
    }

    public function test_request_cannot_have_more_than_one_agreement(): void
    {
        $manager = $this->manager();
        $request = $this->adoptionRequest('approved');

        app(CreateAdoptionAgreementAction::class)->handle(
            $manager,
            $this->agreementData($request)
        );

        $this->assertValidationError(
            $manager,
            $this->agreementData($request),
            'request_id'
        );

        $this->assertDatabaseCount('adoption_agreements', 1);
        $this->assertDatabaseCount('tree_adoption_allocations', 1);
    }

    public function test_tree_with_pending_reservation_cannot_have_another_agreement(): void
    {
        $manager = $this->manager();
        $firstRequest = $this->adoptionRequest('approved');

        app(CreateAdoptionAgreementAction::class)->handle(
            $manager,
            $this->agreementData($firstRequest)
        );

        $secondCitizen = User::factory()->create([
            'role' => UserRole::Citizen,
            'is_active' => true,
        ]);

        $secondRequest = AdoptionRequest::query()->create([
            'tree_id' => $firstRequest->tree_id,
            'requester_id' => $secondCitizen->id,
            'reason' => 'Saya ingin membantu merawat pohon.',
            'status' => 'approved',
        ]);

        $this->assertValidationError(
            $manager,
            $this->agreementData($secondRequest),
            'request_id'
        );

        $this->assertDatabaseCount('adoption_agreements', 1);
        $this->assertDatabaseCount('tree_adoption_allocations', 1);

        $this->assertDatabaseHas('tree_adoption_allocations', [
            'tree_id' => $firstRequest->tree_id,
            'request_id' => $firstRequest->id,
            'status' => 'awaiting_acceptance',
        ]);
    }

    public function test_end_date_must_be_after_start_date(): void
    {
        $manager = $this->manager();
        $request = $this->adoptionRequest('approved');

        $data = $this->agreementData($request);
        $data['ends_on'] = $data['starts_on'];

        $this->assertValidationError(
            $manager,
            $data,
            'ends_on'
        );

        $this->assertDatabaseCount('adoption_agreements', 0);
    }

    public function test_agreement_terms_cannot_be_empty_or_whitespace(): void
    {
        $manager = $this->manager();
        $request = $this->adoptionRequest('approved');

        $data = $this->agreementData($request);
        $data['terms'] = '     ';

        $this->assertValidationError(
            $manager,
            $data,
            'terms'
        );

        $this->assertDatabaseCount('adoption_agreements', 0);
    }

    public function test_citizen_and_field_staff_cannot_create_agreement(): void
    {
        $request = $this->adoptionRequest('approved');
        $data = $this->agreementData($request);

        foreach ([UserRole::Citizen, UserRole::FieldStaff] as $role) {
            $actor = User::factory()->create([
                'role' => $role,
                'is_active' => true,
            ]);

            $this->assertAuthorizationDenied($actor, $data);
        }

        $this->assertDatabaseCount('adoption_agreements', 0);
    }

    public function test_inactive_manager_cannot_create_agreement(): void
    {
        $manager = User::factory()->create([
            'role' => UserRole::Manager,
            'is_active' => false,
        ]);

        $request = $this->adoptionRequest('approved');

        $this->assertAuthorizationDenied(
            $manager,
            $this->agreementData($request)
        );

        $this->assertDatabaseCount('adoption_agreements', 0);
    }

    /**
     * @return array<string, mixed>
     */
    private function agreementData(AdoptionRequest $request): array
    {
        return [
            'request_id' => $request->id,
            'starts_on' => '2026-11-20',
            'ends_on' => '2027-11-20',
            'terms' => 'Pengadopsi wajib merawat pohon secara rutin.',
        ];
    }

    private function assertValidationError(
        User $actor,
        array $data,
        string $field
    ): void {
        try {
            app(CreateAdoptionAgreementAction::class)->handle($actor, $data);
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey($field, $exception->errors());

            return;
        }

        $this->fail(
            "Kesepakatan seharusnya ditolak pada field {$field}."
        );
    }

    private function assertAuthorizationDenied(
        User $actor,
        array $data
    ): void {
        try {
            app(CreateAdoptionAgreementAction::class)->handle($actor, $data);
        } catch (AuthorizationException) {
            return;
        }

        $this->fail('Pengguna tersebut seharusnya tidak memiliki akses.');
    }

    private function manager(): User
    {
        return User::factory()->create([
            'role' => UserRole::Manager,
            'is_active' => true,
        ]);
    }

    private function adoptionRequest(string $status): AdoptionRequest
    {
        $citizen = User::factory()->create([
            'role' => UserRole::Citizen,
            'is_active' => true,
        ]);

        $tree = $this->tree();

        return AdoptionRequest::query()->create([
            'tree_id' => $tree->id,
            'requester_id' => $citizen->id,
            'reason' => 'Saya ingin membantu merawat pohon.',
            'status' => $status,
        ]);
    }

    private function tree(): Tree
    {
        $manager = $this->manager();

        $program = Program::query()->create([
            'name' => 'Program Tes Adopsi',
            'objective' => 'Pengujian kesepakatan adopsi.',
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
