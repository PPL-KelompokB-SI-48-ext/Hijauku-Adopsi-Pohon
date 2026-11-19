<?php

namespace Tests\Feature;

use App\Actions\PlantingBatches\DeletePlantingBatch;
use App\Enums\UserRole;
use App\Models\ActivityLog;
use App\Models\PlantingBatch;
use App\Models\Plot;
use App\Models\Tree;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PlantingBatchDeleteProtectionTest extends TestCase
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

    public function test_manager_cannot_delete_batch_that_contains_trees(): void
    {
        $manager = $this->manager();
        $plot = Plot::factory()->create();
        $batch = PlantingBatch::factory()->create([
            'plot_id' => $plot->getKey(),
            'code' => 'BATCH-WITH-TREES',
        ]);

        Tree::factory()->create([
            'plot_id' => $plot->getKey(),
            'batch_id' => $batch->getKey(),
        ]);

        $action = app(DeletePlantingBatch::class);

        $this->expectException(ValidationException::class);
        try {
            $action->handle($manager, $batch, 'Alasan hapus batch dengan pohon');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('planting_batch', $e->errors());
            $this->assertDatabaseHas('planting_batches', [
                'id' => $batch->getKey(),
            ]);
            throw $e;
        }
    }

    public function test_manager_cannot_delete_batch_without_reason(): void
    {
        $manager = $this->manager();
        $batch = PlantingBatch::factory()->create([
            'code' => 'BATCH-NO-REASON',
        ]);

        $action = app(DeletePlantingBatch::class);

        $this->expectException(ValidationException::class);
        try {
            $action->handle($manager, $batch, '');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('reason', $e->errors());
            $this->assertDatabaseHas('planting_batches', [
                'id' => $batch->getKey(),
            ]);
            throw $e;
        }
    }

    public function test_manager_can_delete_empty_batch(): void
    {
        $manager = $this->manager();
        $batch = PlantingBatch::factory()->create([
            'code' => 'BATCH-EMPTY',
        ]);

        $action = app(DeletePlantingBatch::class);
        $action->handle($manager, $batch, 'Kelompok dibatalkan karena salah input.');

        $this->assertDatabaseMissing('planting_batches', [
            'id' => $batch->getKey(),
        ]);
    }

    public function test_delete_batch_creates_activity_log_with_batch_code(): void
    {
        $manager = $this->manager();
        $batch = PlantingBatch::factory()->create([
            'code' => 'BATCH-AUDIT',
        ]);

        $action = app(DeletePlantingBatch::class);
        $action->handle($manager, $batch, 'Alasan penghapusan kelompok.');

        $log = ActivityLog::query()
            ->where('subject_type', PlantingBatch::class)
            ->where('subject_id', $batch->getKey())
            ->where('action', 'planting_batch.deleted')
            ->first();

        $this->assertNotNull($log);
        $this->assertSame($manager->getKey(), $log->actor_id);
        $this->assertSame('BATCH-AUDIT', $log->metadata['code'] ?? null);
        $this->assertSame('Alasan penghapusan kelompok.', $log->metadata['reason'] ?? null);
    }

    public function test_policy_authorizes_delete_for_manager_only(): void
    {
        $manager = $this->manager();
        $fieldStaff = $this->fieldStaff();
        $emptyBatch = PlantingBatch::factory()->create();

        // Manager memiliki otorisasi hapus kelompok
        $this->assertTrue(Gate::forUser($manager)->allows('delete', $emptyBatch));

        // Field staff tidak memiliki otorisasi hapus
        $this->assertFalse(Gate::forUser($fieldStaff)->allows('delete', $emptyBatch));
    }
}
