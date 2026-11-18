<?php

namespace Tests\Feature;

use App\Actions\PlantingBatches\DeletePlantingBatch;
use App\Actions\PlantingBatches\UpdatePlantingBatch;
use App\Enums\UserRole;
use App\Models\PlantingBatch;
use App\Models\Plot;
use App\Models\Program;
use App\Models\Site;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PlantingBatchCorrectionAndAuditTest extends TestCase
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
        $plot = $this->createPlot();

        $batch = PlantingBatch::factory()->create([
            'plot_id' => $plot->id,
            'code' => 'BATCH-OLD',
            'planted_on' => now()->subDays(5)->toDateString(),
            'notes' => 'Catatan awal',
        ]);

        $action = app(UpdatePlantingBatch::class);

        $updatedBatch = $action->handle($manager, $batch, [
            'code' => 'BATCH-KOREKSI-01',
            'planted_on' => now()->subDays(2)->toDateString(),
            'notes' => 'Koreksi tanggal dan kode sesuai berita acara penanaman.',
            'reason' => 'Koreksi kode dan penyesuaian tanggal riil lapangan.',
        ]);

        $this->assertEquals('BATCH-KOREKSI-01', $updatedBatch->code);
        $this->assertEquals(now()->subDays(2)->toDateString(), $updatedBatch->planted_on->toDateString());
        $this->assertEquals('Koreksi tanggal dan kode sesuai berita acara penanaman.', $updatedBatch->notes);

        $this->assertDatabaseHas('activity_logs', [
            'subject_type' => PlantingBatch::class,
            'subject_id' => $batch->id,
            'actor_id' => $manager->id,
            'action' => 'planting_batch.updated',
        ]);

        $log = $batch->activityLogs()->latest('id')->first();
        $this->assertNotNull($log);
        $this->assertEquals('Koreksi kode dan penyesuaian tanggal riil lapangan.', $log->metadata['reason'] ?? null);
        $this->assertEquals('BATCH-OLD', $log->metadata['old']['code'] ?? null);
        $this->assertEquals('BATCH-KOREKSI-01', $log->metadata['new']['code'] ?? null);
    }

    public function test_update_fails_when_reason_is_missing_or_blank(): void
    {
        $manager = $this->manager();
        $plot = $this->createPlot();

        $batch = PlantingBatch::factory()->create([
            'plot_id' => $plot->id,
            'code' => 'BATCH-TEST',
            'planted_on' => now()->subDays(3)->toDateString(),
        ]);

        $action = app(UpdatePlantingBatch::class);

        $this->expectException(ValidationException::class);

        $action->handle($manager, $batch, [
            'code' => 'BATCH-UPDATED',
            'reason' => '   ',
        ]);
    }

    public function test_update_validates_unique_code_per_plot_and_date_rules(): void
    {
        $manager = $this->manager();
        $plot = $this->createPlot();

        PlantingBatch::factory()->create([
            'plot_id' => $plot->id,
            'code' => 'BATCH-EXISTING',
            'planted_on' => now()->subDays(5)->toDateString(),
        ]);

        $batch = PlantingBatch::factory()->create([
            'plot_id' => $plot->id,
            'code' => 'BATCH-TO-EDIT',
            'planted_on' => now()->subDays(3)->toDateString(),
        ]);

        $action = app(UpdatePlantingBatch::class);

        // Code duplicate in same plot must fail
        try {
            $action->handle($manager, $batch, [
                'code' => 'BATCH-EXISTING',
                'reason' => 'Mencoba pakai kode sama di petak sama',
            ]);
            $this->fail('Harusnya gagal karena duplikasi kode di petak yang sama.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('code', $e->errors());
        }

        // Future planting date must fail
        try {
            $action->handle($manager, $batch, [
                'planted_on' => now()->addDays(5)->toDateString(),
                'reason' => 'Tanggal tanam di masa depan',
            ]);
            $this->fail('Harusnya gagal karena tanggal tanam di masa depan.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('planted_on', $e->errors());
        }
    }

    public function test_non_manager_cannot_delete_planting_batch(): void
    {
        $fieldStaff = $this->fieldStaff();
        $plot = $this->createPlot();

        $batch = PlantingBatch::factory()->create([
            'plot_id' => $plot->id,
            'code' => 'BATCH-EMPTY',
            'planted_on' => now()->subDays(2)->toDateString(),
        ]);

        $action = app(DeletePlantingBatch::class);

        $this->expectException(AuthorizationException::class);
        $action->handle($fieldStaff, $batch, 'Petugas mencoba hapus batch');
    }
}
