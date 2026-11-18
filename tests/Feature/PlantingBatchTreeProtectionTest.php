<?php

namespace Tests\Feature;

use App\Actions\PlantingBatches\RemoveTreeFromPlantingBatch;
use App\Actions\PlantingBatches\UpdatePlantingBatch;
use App\Enums\UserRole;
use App\Models\PlantingBatch;
use App\Models\Plot;
use App\Models\Program;
use App\Models\Site;
use App\Models\Tree;
use App\Models\TreeSpecies;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PlantingBatchTreeProtectionTest extends TestCase
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

    public function test_updating_batch_metadata_preserves_tree_relationships(): void
    {
        $manager = $this->manager();
        $plot = $this->createPlot('PLT-A');
        $species = TreeSpecies::factory()->create(['is_active' => true]);

        $batch = PlantingBatch::factory()->create([
            'plot_id' => $plot->id,
            'code' => 'BATCH-A-01',
            'planted_on' => now()->subDays(4)->toDateString(),
        ]);

        $tree1 = Tree::factory()->create([
            'plot_id' => $plot->id,
            'species_id' => $species->id,
            'batch_id' => $batch->id,
            'code' => 'TREE-A-001',
        ]);

        $tree2 = Tree::factory()->create([
            'plot_id' => $plot->id,
            'species_id' => $species->id,
            'batch_id' => $batch->id,
            'code' => 'TREE-A-002',
        ]);

        $this->assertCount(2, $batch->fresh()->trees);

        $action = app(UpdatePlantingBatch::class);
        $action->handle($manager, $batch, [
            'code' => 'BATCH-A-RENAMED',
            'notes' => 'Pembaruan catatan tanpa mengganggu pohon.',
            'reason' => 'Perapihan kode batch administratif.',
        ]);

        $freshBatch = $batch->fresh();
        $this->assertEquals('BATCH-A-RENAMED', $freshBatch->code);
        $this->assertCount(2, $freshBatch->trees);
        $this->assertEquals($batch->id, $tree1->fresh()->batch_id);
        $this->assertEquals($batch->id, $tree2->fresh()->batch_id);
    }

    public function test_cannot_change_batch_plot_when_batch_contains_trees(): void
    {
        $manager = $this->manager();
        $plotA = $this->createPlot('PLT-A');
        $plotB = $this->createPlot('PLT-B');
        $species = TreeSpecies::factory()->create(['is_active' => true]);

        $batch = PlantingBatch::factory()->create([
            'plot_id' => $plotA->id,
            'code' => 'BATCH-A-02',
            'planted_on' => now()->subDays(2)->toDateString(),
        ]);

        Tree::factory()->create([
            'plot_id' => $plotA->id,
            'species_id' => $species->id,
            'batch_id' => $batch->id,
            'code' => 'TREE-A-003',
        ]);

        $action = app(UpdatePlantingBatch::class);

        $this->expectException(ValidationException::class);

        $action->handle($manager, $batch, [
            'plot_id' => $plotB->id,
            'reason' => 'Mencoba memindahkan kelompok ke petak lain padahal sudah ada pohon',
        ]);
    }

    public function test_tree_detachment_requires_valid_batch_association_and_records_audit(): void
    {
        $manager = $this->manager();
        $plot = $this->createPlot('PLT-A');
        $species = TreeSpecies::factory()->create(['is_active' => true]);

        $batch = PlantingBatch::factory()->create([
            'plot_id' => $plot->id,
            'code' => 'BATCH-A-03',
            'planted_on' => now()->subDays(1)->toDateString(),
        ]);

        $tree = Tree::factory()->create([
            'plot_id' => $plot->id,
            'species_id' => $species->id,
            'batch_id' => $batch->id,
            'code' => 'TREE-A-004',
        ]);

        $otherBatch = PlantingBatch::factory()->create([
            'plot_id' => $plot->id,
            'code' => 'BATCH-A-04',
            'planted_on' => now()->subDays(1)->toDateString(),
        ]);

        $action = app(RemoveTreeFromPlantingBatch::class);

        // Detaching from wrong batch must fail
        try {
            $action->handle($manager, $otherBatch, $tree, 'Alasan salah batch');
            $this->fail('Harusnya gagal karena pohon bukan anggota otherBatch.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('tree', $e->errors());
        }

        // Detaching with reason
        $action->handle($manager, $batch, $tree, 'Pohon dialihkan ke penanaman susulan.');

        $this->assertNull($tree->fresh()->batch_id);

        $this->assertDatabaseHas('activity_logs', [
            'subject_type' => PlantingBatch::class,
            'subject_id' => $batch->id,
            'actor_id' => $manager->id,
            'action' => 'planting_batch.tree_removed',
        ]);

        $log = $batch->activityLogs()->latest('id')->first();
        $this->assertEquals('Pohon dialihkan ke penanaman susulan.', $log->metadata['reason'] ?? null);
        $this->assertEquals('TREE-A-004', $log->metadata['code'] ?? null);
    }
}
