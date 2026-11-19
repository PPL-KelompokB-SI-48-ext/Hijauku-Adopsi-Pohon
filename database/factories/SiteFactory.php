<?php

namespace Database\Factories;

use App\Models\Program;
use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Site>
 */
class SiteFactory extends Factory
{
    protected $model = Site::class;

    public function definition(): array
    {
        return [
            'program_id' => Program::factory(),
            'name' => $this->faker->city(),
            'address' => $this->faker->address(),
            'latitude' => -6.917464,
            'longitude' => 107.619123,
        ];
    }
}
