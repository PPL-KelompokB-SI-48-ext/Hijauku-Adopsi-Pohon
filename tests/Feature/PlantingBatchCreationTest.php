<?php

namespace Tests\Feature;

use App\Actions\PlantingBatches\CreatePlantingBatch;
use App\Enums\UserRole;
use App\Models\PlantingBatch;
use App\Models\Plot;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PlantingBatchCreationTest extends TestCase
{
    use RefreshDatabase;

    private function fieldStaff(): User
    {
        return User::factory()->create([
            'role' => UserRole::FieldStaff,
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

    private function citizen(): User
    {
        return User::factory()->create([
            'role' => UserRole::Citizen,
            'is_active' => true,
        ]);
    }

    public function test_field_staff_can_create_a_planting_batch_with_audit_log(): void
    {
        $staff = $this->fieldStaff();
        $plot = Plot::factory()->create();

        $action = app(CreatePlantingBatch::class);
        $batch = $action->handle($staff, [
            'plot_id' => $plot->getKey(),
            'code' => 'BATCH-A01',
            'planted_on' => now()->toDateString(),
            'notes' => 'Penanaman bibit pohon selesai.',
        ]);

        $this->assertInstanceOf(PlantingBatch::class, $batch);
        $this->assertSame('BATCH-A01', $batch->code);
        $this->assertSame($plot->getKey(), $batch->plot_id);

        $this->assertDatabaseHas('planting_batches', [
            'id' => $batch->getKey(),
            'plot_id' => $plot->getKey(),
            'code' => 'BATCH-A01',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'actor_id' => $staff->getKey(),
            'action' => 'planting_batch.created',
            'subject_type' => PlantingBatch::class,
            'subject_id' => $batch->getKey(),
        ]);
    }

    public function test_batch_must_belong_to_a_valid_plot(): void
    {
        $staff = $this->fieldStaff();
        $action = app(CreatePlantingBatch::class);

        // Kasus tanpa plot_id
        try {
            $action->handle($staff, [
                'code' => 'BATCH-NOPLOT',
                'planted_on' => now()->toDateString(),
            ]);
            $this->fail('Seharusnya gagal jika tidak ada plot_id.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('plot_id', $exception->errors());
        }

        // Kasus plot_id tidak ada di database
        try {
            $action->handle($staff, [
                'plot_id' => 999999,
                'code' => 'BATCH-INVALIDPLOT',
                'planted_on' => now()->toDateString(),
            ]);
            $this->fail('Seharusnya gagal jika plot_id tidak valid.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('plot_id', $exception->errors());
        }
    }

    public function test_planting_date_cannot_be_in_the_future(): void
    {
        $staff = $this->fieldStaff();
        $plot = Plot::factory()->create();
        $action = app(CreatePlantingBatch::class);

        $tomorrow = now()->addDay()->toDateString();

        try {
            $action->handle($staff, [
                'plot_id' => $plot->getKey(),
                'code' => 'BATCH-FUTURE',
                'planted_on' => $tomorrow,
            ]);
            $this->fail('Seharusnya gagal jika tanggal tanam di masa depan.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('planted_on', $exception->errors());
        }
    }

    public function test_batch_code_must_be_unique_per_plot(): void
    {
        $staff = $this->fieldStaff();
        $plot = Plot::factory()->create();
        $action = app(CreatePlantingBatch::class);

        // Buat batch pertama
        $action->handle($staff, [
            'plot_id' => $plot->getKey(),
            'code' => 'BATCH-SAME',
            'planted_on' => now()->toDateString(),
        ]);

        // Coba buat batch kedua dengan kode yang sama di petak yang sama
        try {
            $action->handle($staff, [
                'plot_id' => $plot->getKey(),
                'code' => 'BATCH-SAME',
                'planted_on' => now()->toDateString(),
            ]);
            $this->fail('Seharusnya gagal jika kode batch sudah ada pada petak yang sama.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('code', $exception->errors());
        }
    }

    public function test_same_batch_code_allowed_for_different_plots(): void
    {
        $staff = $this->fieldStaff();
        $plotA = Plot::factory()->create();
        $plotB = Plot::factory()->create();
        $action = app(CreatePlantingBatch::class);

        $batchA = $action->handle($staff, [
            'plot_id' => $plotA->getKey(),
            'code' => 'BATCH-COMMON',
            'planted_on' => now()->toDateString(),
        ]);

        $batchB = $action->handle($staff, [
            'plot_id' => $plotB->getKey(),
            'code' => 'BATCH-COMMON',
            'planted_on' => now()->toDateString(),
        ]);

        $this->assertSame('BATCH-COMMON', $batchA->code);
        $this->assertSame('BATCH-COMMON', $batchB->code);
        $this->assertNotEquals($batchA->plot_id, $batchB->plot_id);
    }

    public function test_citizen_cannot_create_planting_batch(): void
    {
        $citizen = $this->citizen();
        $plot = Plot::factory()->create();
        $action = app(CreatePlantingBatch::class);

        $this->expectException(AuthorizationException::class);

        $action->handle($citizen, [
            'plot_id' => $plot->getKey(),
            'code' => 'BATCH-CITIZEN',
            'planted_on' => now()->toDateString(),
        ]);
    }
}

