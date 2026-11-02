<?php

namespace Database\Factories;

use App\Models\Plot;
use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Plot>
 */
class PlotFactory extends Factory
{
    protected $model = Plot::class;

    public function definition(): array
    {
        return [
            'site_id' => Site::factory(),
            'code' => 'PLT-' . $this->faker->unique()->randomNumber(5),
            'capacity' => 50,
            'status' => 'open',
        ];
    }
}
