<?php

namespace Database\Factories;

use App\Models\TreeSpecies;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TreeSpecies>
 */
class TreeSpeciesFactory extends Factory
{
    protected $model = TreeSpecies::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->words(2, true),
            'care_notes' => $this->faker->optional()->sentence(),
            'is_active' => true,
        ];
    }
}
