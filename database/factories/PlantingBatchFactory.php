<?php

namespace Database\Factories;

use App\Models\PlantingBatch;
use App\Models\Plot;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PlantingBatch>
 */
class PlantingBatchFactory extends Factory
{
    protected $model = PlantingBatch::class;

    public function definition(): array
    {
        return [
            'plot_id' => Plot::factory(),
            'code' => 'BATCH-' . $this->faker->unique()->randomNumber(5),
            'planted_on' => now()->toDateString(),
            'notes' => 'Catatan pelaksanaan penanaman uji coba.',
        ];
    }
}

