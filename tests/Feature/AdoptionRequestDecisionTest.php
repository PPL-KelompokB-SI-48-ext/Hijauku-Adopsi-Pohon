<?php

namespace Tests\Feature;

use App\Actions\Adoptions\DecideAdoptionRequestAction;
use App\Enums\UserRole;
use App\Models\ActivityLog;
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

class AdoptionRequestDecisionTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_approve_submitted_request(): void
    {
        $manager = $this->manager();
        $request = $this->adoptionRequest($this->citizen());

        $result = app(DecideAdoptionRequestAction::class)->handle(
            $manager,
            $request,
            ['decision' => 'approved']
        );

        $this->assertSame('approved', $result->status);
        $this->assertSame($manager->id, $result->reviewer_id);
        $this->assertNull($result->decision_reason);

        $this->assertDatabaseHas('adoption_requests', [
            'id' => $request->id,
            'status' => 'approved',
            'reviewer_id' => $manager->id,
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'subject_type' => AdoptionRequest::class,
            'subject_id' => $request->id,
            'actor_id' => $manager->id,
            'from_status' => 'submitted',
            'to_status' => 'approved',
            'action' => 'adoption_request.decided',
        ]);
    }

    public function test_manager_can_reject_request_with_reason(): void
    {
        $manager = $this->manager();
        $request = $this->adoptionRequest($this->citizen());

        $result = app(DecideAdoptionRequestAction::class)->handle(
            $manager,
            $request,
            [
                'decision' => 'rejected',
                'decision_reason' => 'Data permohonan belum memenuhi ketentuan.',
            ]
        );

        $this->assertSame('rejected', $result->status);
        $this->assertSame($manager->id, $result->reviewer_id);
        $this->assertSame(
            'Data permohonan belum memenuhi ketentuan.',
            $result->decision_reason
        );

        $this->assertDatabaseHas('adoption_requests', [
            'id' => $request->id,
            'status' => 'rejected',
            'reviewer_id' => $manager->id,
            'decision_reason' => 'Data permohonan belum memenuhi ketentuan.',
        ]);

        $log = ActivityLog::query()
            ->where('subject_type', AdoptionRequest::class)
            ->where('subject_id', $request->id)
            ->firstOrFail();

        $this->assertSame('submitted', $log->from_status);
        $this->assertSame('rejected', $log->to_status);
        $this->assertSame($manager->id, $log->actor_id);
        $this->assertSame(
            ['decision_reason' => 'Data permohonan belum memenuhi ketentuan.'],
            $log->metadata
        );
    }

    public function test_rejection_requires_a_reason(): void
    {
        $manager = $this->manager();
        $request = $this->adoptionRequest($this->citizen());

        try {
            app(DecideAdoptionRequestAction::class)->handle(
                $manager,
                $request,
                ['decision' => 'rejected']
            );

            $this->fail('Penolakan tanpa alasan seharusnya ditolak.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey(
                'decision_reason',
                $exception->errors()
            );
        }

        $this->assertDatabaseHas('adoption_requests', [
            'id' => $request->id,
            'status' => 'submitted',
            'reviewer_id' => null,
        ]);

        $this->assertDatabaseCount('activity_logs', 0);
    }

    public function test_draft_request_cannot_be_decided(): void
    {
        $manager = $this->manager();
        $request = $this->adoptionRequest(
            $this->citizen(),
            'draft'
        );

        try {
            app(DecideAdoptionRequestAction::class)->handle(
                $manager,
                $request,
                ['decision' => 'approved']
            );

            $this->fail('Permohonan draft seharusnya tidak dapat diputuskan.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('decision', $exception->errors());
        }

        $this->assertDatabaseHas('adoption_requests', [
            'id' => $request->id,
            'status' => 'draft',
            'reviewer_id' => null,
        ]);

        $this->assertDatabaseCount('activity_logs', 0);
    }

    public function test_already_decided_request_cannot_be_decided_again(): void
    {
        $manager = $this->manager();
        $request = $this->adoptionRequest(
            $this->citizen(),
            'approved'
        );

        try {
            app(DecideAdoptionRequestAction::class)->handle(
                $manager,
                $request,
                ['decision' => 'rejected', 'decision_reason' => 'Alasan tes.']
            );

            $this->fail('Permohonan yang sudah diputuskan tidak boleh diputuskan ulang.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('decision', $exception->errors());
        }

        $this->assertDatabaseHas('adoption_requests', [
            'id' => $request->id,
            'status' => 'approved',
        ]);

        $this->assertDatabaseCount('activity_logs', 0);
    }

    public function test_citizen_cannot_decide_adoption_request(): void
    {
        $citizen = $this->citizen();
        $request = $this->adoptionRequest($this->citizen());

        $this->expectException(AuthorizationException::class);

        app(DecideAdoptionRequestAction::class)->handle(
            $citizen,
            $request,
            ['decision' => 'approved']
        );
    }

    public function test_manager_cannot_decide_own_request(): void
    {
        $manager = $this->manager();
        $request = $this->adoptionRequest($manager);

        $this->expectException(AuthorizationException::class);

        app(DecideAdoptionRequestAction::class)->handle(
            $manager,
            $request,
            ['decision' => 'approved']
        );
    }

    private function manager(): User
    {
        return User::factory()->create([
            'role' => UserRole::Manager,
            'is_active' => true,
        ]);
    }

    private function citizen(): User
    {
        return User::factory()->create([
            'role' => UserRole::Citizen,
            'is_active' => true,
        ]);
    }

    private function adoptionRequest(
        User $requester,
        string $status = 'submitted'
    ): AdoptionRequest {
        $tree = $this->tree();

        return AdoptionRequest::query()->create([
            'tree_id' => $tree->id,
            'requester_id' => $requester->id,
            'reason' => 'Saya ingin membantu merawat pohon.',
            'status' => $status,
        ]);
    }

    private function tree(): Tree
    {
        $manager = $this->manager();

        $program = Program::query()->create([
            'name' => 'Program Tes Adopsi',
            'objective' => 'Pengujian permohonan adopsi.',
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
