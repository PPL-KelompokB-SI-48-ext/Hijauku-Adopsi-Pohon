<?php

namespace Tests\Feature;

use App\Actions\PlantingBatches\DeletePlantingBatch;
use App\Actions\PlantingBatches\UpdatePlantingBatch;
use App\Enums\UserRole;
use App\Models\ActivityLog;
use App\Models\PlantingBatch;
use App\Models\Plot;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PlantingBatchCorrectionAndAuditTest extends TestCase
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

    private function citizen(): User
    {
        return User::factory()->create([
            'role' => UserRole::Citizen,
            'is_active' => true,
        ]);
    }

    public function test_manager_can_update_planting_batch_with_reason_and_audit_log(): void
    {
        $manager = $this->manager();
        $plot = Plot::factory()->create();
        $batch = PlantingBatch::factory()->create([
            'plot_id' => $plot->getKey(),
            'code' => 'BATCH-OLD',
            'planted_on' => '2026-11-10',
            'notes' => 'Catatan lama.',
        ]);

        $action = app(UpdatePlantingBatch::class);
        $updated = $action->handle($manager, $batch, [
            'code' => 'BATCH-NEW',
            'planted_on' => '2026-11-12',
            'notes' => 'Catatan baru telah dikoreksi.',
            'reason' => 'Perbaikan kode dan penyesuaian tanggal aktual.',
        ]);

        $this->assertSame('BATCH-NEW', $updated->code);
        $this->assertSame('2026-11-12', $updated->planted_on->format('Y-m-d'));
        $this->assertSame('Catatan baru telah dikoreksi.', $updated->notes);

        $this->assertDatabaseHas('planting_batches', [
            'id' => $batch->getKey(),
            'code' => 'BATCH-NEW',
            'notes' => 'Catatan baru telah dikoreksi.',
        ]);

        $log = ActivityLog::query()
            ->where('subject_type', PlantingBatch::class)
            ->where('subject_id', $batch->getKey())
            ->where('action', 'planting_batch.updated')
            ->first();

        $this->assertNotNull($log, 'Audit log pembaruan kelompok wajib tercatat.');
        $this->assertSame($manager->getKey(), $log->actor_id);
        $this->assertSame('Perbaikan kode dan penyesuaian tanggal aktual.', $log->metadata['reason'] ?? null);
        $this->assertSame('BATCH-OLD', $log->metadata['old']['code'] ?? null);
        $this->assertSame('BATCH-NEW', $log->metadata['new']['code'] ?? null);
    }

    public function test_update_fails_when_reason_is_missing_or_blank(): void
    {
        $manager = $this->manager();
        $batch = PlantingBatch::factory()->create();

        $action = app(UpdatePlantingBatch::class);

        $this->expectException(ValidationException::class);
        $action->handle($manager, $batch, [
            'code' => 'BATCH-XYZ',
            'reason' => '',
        ]);
    }

    public function test_update_validates_unique_code_per_plot_and_date_rules(): void
    {
        $manager = $this->manager();
        $plot = Plot::factory()->create();
        $batch1 = PlantingBatch::factory()->create([
            'plot_id' => $plot->getKey(),
            'code' => 'BATCH-001',
        ]);
        $batch2 = PlantingBatch::factory()->create([
            'plot_id' => $plot->getKey(),
            'code' => 'BATCH-002',
        ]);

        $action = app(UpdatePlantingBatch::class);

        // Uji keunikan kode pada petak yang sama
        try {
            $action->handle($manager, $batch2, [
                'code' => 'BATCH-001',
                'reason' => 'Mencoba menduplikasi kode.',
            ]);
            $this->fail('Harusnya melempar ValidationException untuk kode duplikat pada petak yang sama.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('code', $e->errors());
        }

        // Uji tanggal tidak boleh di masa depan
        try {
            $action->handle($manager, $batch2, [
                'planted_on' => now()->addDays(5)->toDateString(),
                'reason' => 'Tanggal masa depan.',
            ]);
            $this->fail('Harusnya melempar ValidationException untuk tanggal masa depan.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('planted_on', $e->errors());
        }
    }

    public function test_non_manager_cannot_delete_planting_batch(): void
    {
        $fieldStaff = $this->fieldStaff();
        $citizen = $this->citizen();
        $batch = PlantingBatch::factory()->create();

        $action = app(DeletePlantingBatch::class);

        $this->expectException(AuthorizationException::class);
        $action->handle($fieldStaff, $batch, 'Alasan coba hapus kelompok');
    }
}
