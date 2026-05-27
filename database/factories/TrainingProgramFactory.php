<?php

namespace Database\Factories;

use App\Models\TrainingProgram;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TrainingProgram>
 */
class TrainingProgramFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => 'Category B '.$this->faker->randomElement(['Standard', 'Intensive', 'Evening']),
            'slug' => $this->faker->unique()->slug(3),
            'license_category' => 'B',
            'transmission' => $this->faker->randomElement(['manual', 'automatic']),
            'theory_hours' => $this->faker->numberBetween(30, 45),
            'practice_hours' => $this->faker->numberBetween(20, 35),
            'price_cents' => $this->faker->numberBetween(85000, 150000),
            'description' => $this->faker->paragraph(),
            'is_active' => true,
        ];
    }
}
