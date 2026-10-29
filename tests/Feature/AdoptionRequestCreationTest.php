<?php

namespace Tests\Feature;

use App\Actions\Adoptions\CreateAdoptionRequestAction;
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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AdoptionRequestCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_citizen_can_submit_an_adoption_request(): void
    {
        $citizen = $this->citizen();
        $tree = $this->tree();

        $request = app(CreateAdoptionRequestAction::class)->handle($citizen, [
            'tree_id' => $tree->id,
            'reason' => 'Saya ingin membantu merawat pohon.',
        ]);

        $this->assertSame($citizen->id, $request->requester_id);
        $this->assertSame($tree->id, $request->tree_id);
        $this->assertSame('submitted', $request->status);
        $this->assertSame(
            'Saya ingin membantu merawat pohon.',
            $request->reason
        );

        $this->assertDatabaseHas('adoption_requests', [
            'id' => $request->id,
            'requester_id' => $citizen->id,
            'tree_id' => $tree->id,
            'status' => 'submitted',
        ]);
    }

    public function test_citizen_cannot_submit_duplicate_active_request(): void
    {
        $citizen = $this->citizen();
        $tree = $this->tree();

        AdoptionRequest::query()->create([
            'tree_id' => $tree->id,
            'requester_id' => $citizen->id,
            'reason' => 'Permohonan pertama.',
            'status' => 'submitted',
        ]);

        try {
            app(CreateAdoptionRequestAction::class)->handle($citizen, [
                'tree_id' => $tree->id,
                'reason' => 'Permohonan kedua.',
            ]);

            $this->fail('Permohonan duplikat seharusnya ditolak.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('tree_id', $exception->errors());
        }

        $this->assertDatabaseCount('adoption_requests', 1);
    }

    public function test_citizen_cannot_request_an_allocated_tree(): void
    {
        $citizen = $this->citizen();
        $tree = $this->tree();

        $request = AdoptionRequest::query()->create([
            'tree_id' => $tree->id,
            'requester_id' => $citizen->id,
            'reason' => 'Permohonan yang telah diproses.',
            'status' => 'submitted',
        ]);

        $agreement = AdoptionAgreement::query()->create([
            'request_id' => $request->id,
            'tree_id' => $tree->id,
            'adopter_id' => $citizen->id,
            'terms' => 'Ketentuan adopsi.',
            'starts_on' => '2026-11-01',
            'status' => 'awaiting_acceptance',
        ]);

        TreeAdoptionAllocation::query()->create([
            'tree_id' => $tree->id,
            'request_id' => $request->id,
            'agreement_id' => $agreement->id,
            'status' => 'awaiting_acceptance',
            'reserved_at' => now(),
        ]);

        try {
            app(CreateAdoptionRequestAction::class)->handle($citizen, [
                'tree_id' => $tree->id,
                'reason' => 'Saya ingin mengajukan pohon ini.',
            ]);

            $this->fail('Pohon yang sudah dialokasikan seharusnya ditolak.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('tree_id', $exception->errors());
        }

        $this->assertDatabaseCount('adoption_requests', 1);
    }

    public function test_citizen_can_only_view_own_adoption_requests(): void
    {
        $firstCitizen = $this->citizen();
        $secondCitizen = $this->citizen();
        $tree = $this->tree();

        $ownRequest = AdoptionRequest::query()->create([
            'tree_id' => $tree->id,
            'requester_id' => $firstCitizen->id,
            'reason' => 'Permohonan milik saya.',
            'status' => 'submitted',
        ]);

        $otherRequest = new AdoptionRequest([
            'tree_id' => $tree->id,
            'requester_id' => $secondCitizen->id,
            'reason' => 'Permohonan warga lain.',
            'status' => 'submitted',
        ]);

        $this->assertTrue($firstCitizen->can('view', $ownRequest));
        $this->assertFalse($firstCitizen->can('view', $otherRequest));
    }

    private function citizen(): User
    {
        return User::factory()->create([
            'role' => UserRole::Citizen,
            'is_active' => true,
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
