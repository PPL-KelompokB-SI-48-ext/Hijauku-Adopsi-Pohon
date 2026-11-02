<?php

namespace Database\Factories;

use App\Models\Program;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Program>
 */
class ProgramFactory extends Factory
{
    protected $model = Program::class;

    public function definition(): array
    {
        return [
            'created_by' => User::factory(),
            'name' => $this->faker->words(3, true),
            'objective' => $this->faker->sentence(),
            'starts_on' => now()->toDateString(),
            'ends_on' => now()->addMonths(6)->toDateString(),
            'status' => 'draft',
        ];
    }
}
