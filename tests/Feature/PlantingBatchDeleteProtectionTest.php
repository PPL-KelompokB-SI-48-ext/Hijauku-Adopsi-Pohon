<?php

namespace Tests\Feature;

use App\Actions\PlantingBatches\DeletePlantingBatch;
use App\Enums\UserRole;
use App\Models\PlantingBatch;
use App\Models\Plot;
use App\Models\Program;
use App\Models\Site;
use App\Models\Tree;
use App\Models\TreeSpecies;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PlantingBatchDeleteProtectionTest extends TestCase
{
    use RefreshDatabase;

    private function createPlot(string $code = 'PLT-01'): Plot
    {
        $program = Program::factory()->create();
        $site = Site::factory()->create(['program_id' => $program->id]);

        return Plot::factory()->create([
            'site_id' => $site->id,
            'code' => $code,
        ]);
    }

    private function manager(): User
    {
        return User::factory()->create([
            'role' => UserRole::Manager,
            'is_active' => true,
        ]);
    }

    private function fieldStaff(): User
    {
        return User::factory()->create([
            'role' => UserRole::FieldStaff,
            'is_active' => true,
        ]);
    }

    public function test_manager_cannot_delete_batch_that_contains_trees(): void
    {
        $manager = $this->manager();
        $plot = $this->createPlot();
        $species = TreeSpecies::factory()->create(['is_active' => true]);

        $batch = PlantingBatch::factory()->create([
            'plot_id' => $plot->id,
            'code' => 'BATCH-WITH-TREES',
            'planted_on' => now()->subDays(5)->toDateString(),
        ]);

        Tree::factory()->create([
            'plot_id' => $plot->id,
            'species_id' => $species->id,
            'batch_id' => $batch->id,
            'code' => 'TREE-DEL-001',
        ]);

        // Policy gate must forbid delete when batch has trees
        $this->assertFalse(Gate::forUser($manager)->allows('delete', $batch));

        // Action must throw exception
        $action = app(DeletePlantingBatch::class);

        $this->expectException(AuthorizationException::class);
        $action->handle($manager, $batch, 'Mencoba hapus batch yang ada pohonnya');
    }

    public function test_manager_can_delete_empty_batch(): void
    {
        $manager = $this->manager();
        $plot = $this->createPlot();

        $batch = PlantingBatch::factory()->create([
            'plot_id' => $plot->id,
            'code' => 'BATCH-EMPTY-01',
            'planted_on' => now()->subDays(2)->toDateString(),
        ]);

        // Policy gate allows delete for empty batch
        $this->assertTrue(Gate::forUser($manager)->allows('delete', $batch));

        $action = app(DeletePlantingBatch::class);
        $action->handle($manager, $batch, 'Kelompok dibatalkan karena salah input petak.');

        $this->assertDatabaseMissing('planting_batches', [
            'id' => $batch->id,
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'subject_type' => PlantingBatch::class,
            'subject_id' => $batch->id,
            'actor_id' => $manager->id,
            'action' => 'planting_batch.deleted',
        ]);

        $log = $batch->activityLogs()->latest('id')->first();
        $this->assertNotNull($log);
        $this->assertEquals('Kelompok dibatalkan karena salah input petak.', $log->metadata['reason'] ?? null);
        $this->assertEquals('BATCH-EMPTY-01', $log->metadata['code'] ?? null);
    }

    public function test_non_manager_cannot_delete_batch_even_if_empty(): void
    {
        $fieldStaff = $this->fieldStaff();
        $plot = $this->createPlot();

        $batch = PlantingBatch::factory()->create([
            'plot_id' => $plot->id,
            'code' => 'BATCH-EMPTY-02',
            'planted_on' => now()->subDays(1)->toDateString(),
        ]);

        $this->assertFalse(Gate::forUser($fieldStaff)->allows('delete', $batch));

        $action = app(DeletePlantingBatch::class);

        $this->expectException(AuthorizationException::class);
        $action->handle($fieldStaff, $batch);
    }
}
