<?php

namespace Database\Factories;

use App\Enums\ExamStatus;
use App\Models\Enrollment;
use App\Models\Exam;
use App\Models\Instructor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Exam>
 */
class ExamFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'enrollment_id' => Enrollment::factory(),
            'instructor_id' => Instructor::factory(),
            'exam_type' => $this->faker->randomElement(['theory', 'practice']),
            'status' => ExamStatus::Scheduled,
            'scheduled_at' => now()->addDays($this->faker->numberBetween(7, 45)),
            'attempt_number' => $this->faker->numberBetween(1, 3),
            'score' => null,
            'notes' => null,
        ];
    }
}
