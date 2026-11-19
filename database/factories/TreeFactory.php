<?php

namespace Database\Factories;

use App\Models\Plot;
use App\Models\Tree;
use App\Models\TreeSpecies;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tree>
 */
class TreeFactory extends Factory
{
    protected $model = Tree::class;

    public function definition(): array
    {
        return [
            'species_id' => TreeSpecies::factory(),
            'plot_id' => Plot::factory(),
            'code' => 'PHN-' . $this->faker->unique()->randomNumber(5),
            'planted_on' => now()->toDateString(),
            'initial_condition' => 'baik',
        ];
    }
}
