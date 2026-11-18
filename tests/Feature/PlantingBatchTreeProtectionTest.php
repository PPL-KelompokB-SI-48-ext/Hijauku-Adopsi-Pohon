<?php

namespace Tests\Feature;

use App\Actions\PlantingBatches\RemoveTreeFromPlantingBatch;
use App\Actions\PlantingBatches\UpdatePlantingBatch;
use App\Enums\UserRole;
use App\Models\ActivityLog;
use App\Models\PlantingBatch;
use App\Models\Plot;
use App\Models\Tree;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PlantingBatchTreeProtectionTest extends TestCase
{
    use RefreshDatabase;

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

    public function test_updating_batch_metadata_preserves_tree_relationships(): void
    {
        $manager = $this->manager();
        $plot = Plot::factory()->create();
        $batch = PlantingBatch::factory()->create([
            'plot_id' => $plot->getKey(),
            'code' => 'BATCH-ALPHA',
        ]);

        $trees = Tree::factory()->count(3)->create([
            'plot_id' => $plot->getKey(),
            'batch_id' => $batch->getKey(),
        ]);

        $action = app(UpdatePlantingBatch::class);
        $action->handle($manager, $batch, [
            'code' => 'BATCH-ALPHA-REV',
            'notes' => 'Pembaruan catatan tanpa mengganggu pohon.',
            'reason' => 'Koreksi penamaan kelompok.',
        ]);

        // Verifikasi seluruh relasi pohon tetap utuh
        $this->assertSame(3, $batch->trees()->count());
        foreach ($trees as $tree) {
            $this->assertDatabaseHas('trees', [
                'id' => $tree->getKey(),
                'batch_id' => $batch->getKey(),
                'plot_id' => $plot->getKey(),
            ]);
        }
    }

    public function test_cannot_change_batch_plot_when_batch_contains_trees(): void
    {
        $manager = $this->manager();
        $plotA = Plot::factory()->create();
        $plotB = Plot::factory()->create();

        $batch = PlantingBatch::factory()->create([
            'plot_id' => $plotA->getKey(),
            'code' => 'BATCH-PLOT-A',
        ]);

        Tree::factory()->create([
            'plot_id' => $plotA->getKey(),
            'batch_id' => $batch->getKey(),
        ]);

        $action = app(UpdatePlantingBatch::class);

        $this->expectException(ValidationException::class);
        try {
            $action->handle($manager, $batch, [
                'plot_id' => $plotB->getKey(),
                'reason' => 'Mencoba memindahkan petak kelompok yang sudah ada pohon.',
            ]);
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('plot_id', $e->errors());
            $this->assertDatabaseHas('planting_batches', [
                'id' => $batch->getKey(),
                'plot_id' => $plotA->getKey(),
            ]);
            throw $e;
        }
    }

    public function test_tree_detachment_requires_valid_batch_association_and_records_audit(): void
    {
        $manager = $this->manager();
        $plot = Plot::factory()->create();
        $batch = PlantingBatch::factory()->create([
            'plot_id' => $plot->getKey(),
        ]);

        $tree = Tree::factory()->create([
            'plot_id' => $plot->getKey(),
            'batch_id' => $batch->getKey(),
        ]);

        $action = app(RemoveTreeFromPlantingBatch::class);
        $action->handle($manager, $batch, $tree, 'Pohon dialihkan untuk perawatan isolasi.');

        $this->assertNull($tree->fresh()->batch_id);

        $log = ActivityLog::query()
            ->where('subject_type', PlantingBatch::class)
            ->where('subject_id', $batch->getKey())
            ->where('action', 'planting_batch.tree_removed')
            ->first();

        $this->assertNotNull($log);
        $this->assertSame($manager->getKey(), $log->actor_id);
        $this->assertSame($tree->getKey(), $log->metadata['removed_tree_id'] ?? null);
        $this->assertSame('Pohon dialihkan untuk perawatan isolasi.', $log->metadata['reason'] ?? null);
    }
}
