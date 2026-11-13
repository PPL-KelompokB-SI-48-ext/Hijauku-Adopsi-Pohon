<?php

namespace Tests\Feature;

use App\Actions\Adoptions\CreateAdoptionRequestAction;
use App\Actions\Adoptions\WithdrawAdoptionRequestAction;
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

class AdoptionRequestWithdrawalTest extends TestCase
{
    use RefreshDatabase;

    private int $treeSequence = 0;

    public function test_citizen_can_withdraw_own_submitted_request(): void
    {
        $citizen = $this->citizen();
        $tree = $this->tree();
        $request = $this->adoptionRequest($citizen, $tree);

        $result = app(WithdrawAdoptionRequestAction::class)->handle(
            $citizen,
            $request
        );

        $this->assertSame('withdrawn', $result->status);

        $this->assertDatabaseHas('adoption_requests', [
            'id' => $request->id,
            'requester_id' => $citizen->id,
            'status' => 'withdrawn',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'subject_type' => AdoptionRequest::class,
            'subject_id' => $request->id,
            'actor_id' => $citizen->id,
            'action' => 'adoption_request.withdrawn',
            'from_status' => 'submitted',
            'to_status' => 'withdrawn',
        ]);
    }

    public function test_citizen_cannot_withdraw_another_citizens_request(): void
    {
        $citizen = $this->citizen();
        $otherCitizen = $this->citizen();

        $request = $this->adoptionRequest(
            $otherCitizen,
            $this->tree()
        );

        $this->expectException(AuthorizationException::class);

        app(WithdrawAdoptionRequestAction::class)->handle(
            $citizen,
            $request
        );
    }

    public function test_request_with_invalid_status_cannot_be_withdrawn(): void
    {
        $citizen = $this->citizen();

        $invalidStatuses = [
            'draft',
            'approved',
            'rejected',
            'withdrawn',
        ];

        foreach ($invalidStatuses as $status) {
            $request = $this->adoptionRequest(
                $citizen,
                $this->tree(),
                $status
            );

            try {
                app(WithdrawAdoptionRequestAction::class)->handle(
                    $citizen,
                    $request
                );

                $this->fail(
                    "Permohonan berstatus {$status} seharusnya tidak dapat ditarik."
                );
            } catch (ValidationException $exception) {
                $this->assertArrayHasKey(
                    'status',
                    $exception->errors()
                );
            }

            $this->assertDatabaseHas('adoption_requests', [
                'id' => $request->id,
                'status' => $status,
            ]);
        }

        $this->assertDatabaseCount('activity_logs', 0);
    }

    public function test_manager_cannot_withdraw_adoption_request(): void
    {
        $manager = $this->manager();

        $request = $this->adoptionRequest(
            $this->citizen(),
            $this->tree()
        );

        $this->expectException(AuthorizationException::class);

        app(WithdrawAdoptionRequestAction::class)->handle(
            $manager,
            $request
        );
    }

    public function test_citizen_can_submit_again_after_withdrawing_request(): void
    {
        $citizen = $this->citizen();
        $tree = $this->tree();

        $request = $this->adoptionRequest($citizen, $tree);

        app(WithdrawAdoptionRequestAction::class)->handle(
            $citizen,
            $request
        );

        $newRequest = app(CreateAdoptionRequestAction::class)->handle(
            $citizen,
            [
                'tree_id' => $tree->id,
                'reason' => 'Saya ingin mengajukan kembali permohonan ini.',
            ]
        );

        $this->assertSame('withdrawn', $request->fresh()->status);
        $this->assertSame('submitted', $newRequest->status);
        $this->assertNotSame($request->id, $newRequest->id);

        $this->assertDatabaseCount('adoption_requests', 2);
    }

    public function test_withdrawal_does_not_release_valid_allocations(): void
    {
        foreach (['awaiting_acceptance', 'active'] as $status) {
            $citizen = $this->citizen();
            $otherCitizen = $this->citizen();
            $tree = $this->tree();

            $request = $this->adoptionRequest(
                $citizen,
                $tree
            );

            $allocation = $this->createAllocation(
                $otherCitizen,
                $tree,
                $status
            );

            app(WithdrawAdoptionRequestAction::class)->handle(
                $citizen,
                $request
            );

            $this->assertDatabaseHas('tree_adoption_allocations', [
                'id' => $allocation->id,
                'tree_id' => $tree->id,
                'request_id' => $allocation->request_id,
                'status' => $status,
            ]);

            $this->assertDatabaseHas('adoption_requests', [
                'id' => $request->id,
                'status' => 'withdrawn',
            ]);

            try {
                app(CreateAdoptionRequestAction::class)->handle(
                    $citizen,
                    [
                        'tree_id' => $tree->id,
                        'reason' => 'Mencoba mengajukan kembali pohon.',
                    ]
                );

                $this->fail(
                    "Pohon dengan allocation {$status} seharusnya tetap tidak tersedia."
                );
            } catch (ValidationException $exception) {
                $this->assertArrayHasKey(
                    'tree_id',
                    $exception->errors()
                );
            }
        }
    }

    private function citizen(): User
    {
        return User::factory()->create([
            'role' => UserRole::Citizen,
            'is_active' => true,
        ]);
    }

    private function manager(): User
    {
        return User::factory()->create([
            'role' => UserRole::Manager,
            'is_active' => true,
        ]);
    }

    private function adoptionRequest(
        User $requester,
        Tree $tree,
        string $status = 'submitted'
    ): AdoptionRequest {
        return AdoptionRequest::query()->create([
            'tree_id' => $tree->id,
            'requester_id' => $requester->id,
            'reason' => 'Saya ingin membantu merawat pohon.',
            'status' => $status,
        ]);
    }

    private function createAllocation(
        User $adopter,
        Tree $tree,
        string $allocationStatus
    ): TreeAdoptionAllocation {
        $request = $this->adoptionRequest(
            $adopter,
            $tree,
            'approved'
        );

        $agreement = AdoptionAgreement::query()->create([
            'request_id' => $request->id,
            'tree_id' => $tree->id,
            'adopter_id' => $adopter->id,
            'terms' => 'Ketentuan adopsi.',
            'starts_on' => '2026-11-01',
            'status' => $allocationStatus,
        ]);

        return TreeAdoptionAllocation::query()->create([
            'tree_id' => $tree->id,
            'request_id' => $request->id,
            'agreement_id' => $agreement->id,
            'status' => $allocationStatus,
            'reserved_at' => now(),
        ]);
    }

    private function tree(): Tree
    {
        $this->treeSequence++;

        $suffix = $this->treeSequence;
        $manager = $this->manager();

        $program = Program::query()->create([
            'name' => "Program Tes Adopsi {$suffix}",
            'objective' => 'Pengujian penarikan permohonan adopsi.',
            'starts_on' => '2026-11-01',
            'ends_on' => '2027-11-01',
            'status' => 'active',
            'created_by' => $manager->id,
        ]);

        $site = Site::query()->create([
            'program_id' => $program->id,
            'name' => "Lokasi Tes {$suffix}",
            'address' => 'Lokasi pengujian.',
        ]);

        $plot = Plot::query()->create([
            'site_id' => $site->id,
            'code' => "PLOT-TEST-{$suffix}",
            'capacity' => 10,
            'status' => 'open',
        ]);

        $species = TreeSpecies::query()->create([
            'name' => "Pohon Tes {$suffix}",
            'care_notes' => 'Perawatan rutin.',
            'is_active' => true,
        ]);

        return Tree::query()->create([
            'species_id' => $species->id,
            'plot_id' => $plot->id,
            'code' => "TREE-TEST-{$suffix}",
            'initial_condition' => 'healthy',
        ]);
    }
}
