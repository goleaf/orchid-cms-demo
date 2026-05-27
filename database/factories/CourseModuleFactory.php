<?php

namespace Database\Factories;

use App\Models\CourseModule;
use App\Models\TrainingProgram;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CourseModule>
 */
class CourseModuleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'training_program_id' => TrainingProgram::factory(),
            'title' => $this->faker->sentence(4),
            'module_type' => $this->faker->randomElement(['theory', 'practice']),
            'sort_order' => $this->faker->numberBetween(1, 12),
            'duration_minutes' => $this->faker->randomElement([45, 60, 90]),
            'is_required' => true,
        ];
    }
}
