<?php

namespace Tests\Feature;

use App\Actions\Adoptions\TerminateAdoptionAgreementAction;
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
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class EndAdoptionAgreementTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_terminate_active_agreement_and_release_tree(): void
    {
        $agreement = $this->activeAgreement();
        $manager = User::factory()->create([
            'role' => UserRole::Manager,
            'is_active' => true,
        ]);

        $result = app(TerminateAdoptionAgreementAction::class)->handle(
            $manager,
            $agreement,
            ['reason' => 'Pengadopsi tidak menjalankan kewajibannya.']
        );

        $this->assertSame('terminated', $result->status);

        $this->assertDatabaseHas('tree_adoption_allocations', [
            'agreement_id' => $agreement->id,
            'status' => 'released',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'subject_type' => AdoptionAgreement::class,
            'subject_id' => $agreement->id,
            'actor_id' => $manager->id,
            'action' => 'adoption_agreement.terminated',
            'from_status' => 'active',
            'to_status' => 'terminated',
        ]);
    }

    public function test_citizen_cannot_terminate_agreement(): void
    {
        $agreement = $this->activeAgreement();
        $citizen = User::query()->findOrFail($agreement->adopter_id);

        try {
            app(TerminateAdoptionAgreementAction::class)->handle(
                $citizen,
                $agreement,
                ['reason' => 'Alasan pengujian penghentian.']
            );
        } catch (AuthorizationException) {
            $this->assertSame('active', $agreement->fresh()->status);

            return;
        }

        $this->fail('Warga tidak boleh menghentikan kesepakatan.');
    }

    public function test_termination_requires_a_reason(): void
    {
        $agreement = $this->activeAgreement();
        $manager = User::factory()->create([
            'role' => UserRole::Manager,
            'is_active' => true,
        ]);

        try {
            app(TerminateAdoptionAgreementAction::class)->handle(
                $manager,
                $agreement,
                ['reason' => '   ']
            );
        } catch (ValidationException) {
            $this->assertSame('active', $agreement->fresh()->status);

            return;
        }

        $this->fail('Alasan penghentian wajib diisi.');
    }

    public function test_expired_agreement_is_completed_automatically(): void
    {
        $agreement = $this->activeAgreement('2026-11-18');

        Carbon::setTestNow('2026-11-19 01:00:00');

        try {
            Artisan::call('adoptions:complete-expired');

            $this->assertSame('completed', $agreement->fresh()->status);

            $this->assertDatabaseHas('tree_adoption_allocations', [
                'agreement_id' => $agreement->id,
                'status' => 'released',
            ]);

            $this->assertDatabaseHas('activity_logs', [
                'subject_type' => AdoptionAgreement::class,
                'subject_id' => $agreement->id,
                'action' => 'adoption_agreement.completed',
                'from_status' => 'active',
                'to_status' => 'completed',
            ]);
        } finally {
            Carbon::setTestNow();
        }
    }

    private function activeAgreement(string $endsOn = '2027-11-20'): AdoptionAgreement
    {
        $citizen = User::factory()->create([
            'role' => UserRole::Citizen,
            'is_active' => true,
        ]);

        $manager = User::factory()->create([
            'role' => UserRole::Manager,
            'is_active' => true,
        ]);

        $program = Program::query()->create([
            'name' => 'Program Tes HAP-39',
            'objective' => 'Pengujian penghentian adopsi.',
            'starts_on' => '2026-11-01',
            'ends_on' => '2027-11-01',
            'status' => 'active',
            'created_by' => $manager->id,
        ]);

        $site = Site::query()->create([
            'program_id' => $program->id,
            'name' => 'Lokasi Tes HAP-39',
            'address' => 'Lokasi pengujian.',
        ]);

        $plot = Plot::query()->create([
            'site_id' => $site->id,
            'code' => 'PLOT-HAP39',
            'capacity' => 10,
            'status' => 'open',
        ]);

        $species = TreeSpecies::query()->create([
            'name' => 'Pohon Tes HAP-39',
            'care_notes' => 'Perawatan rutin.',
            'is_active' => true,
        ]);

        $tree = Tree::query()->create([
            'species_id' => $species->id,
            'plot_id' => $plot->id,
            'code' => 'TREE-HAP39',
            'initial_condition' => 'healthy',
        ]);

        $request = AdoptionRequest::query()->create([
            'tree_id' => $tree->id,
            'requester_id' => $citizen->id,
            'reason' => 'Saya ingin merawat pohon.',
            'status' => 'approved',
        ]);

        $agreement = AdoptionAgreement::query()->create([
            'request_id' => $request->id,
            'tree_id' => $tree->id,
            'adopter_id' => $citizen->id,
            'terms' => 'Pengadopsi wajib merawat pohon.',
            'starts_on' => '2026-11-01',
            'ends_on' => $endsOn,
            'status' => 'active',
            'accepted_at' => now(),
        ]);

        TreeAdoptionAllocation::query()->create([
            'tree_id' => $tree->id,
            'request_id' => $request->id,
            'agreement_id' => $agreement->id,
            'status' => 'active',
            'reserved_at' => now(),
        ]);

        return $agreement;
    }
}
