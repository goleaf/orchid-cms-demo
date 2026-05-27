<?php

namespace Database\Factories;

use App\Enums\EnrollmentStatus;
use App\Models\Enrollment;
use App\Models\Instructor;
use App\Models\StudentProfile;
use App\Models\TrainingProgram;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Enrollment>
 */
class EnrollmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'student_profile_id' => StudentProfile::factory(),
            'training_program_id' => TrainingProgram::factory(),
            'instructor_id' => Instructor::factory(),
            'status' => EnrollmentStatus::Active,
            'started_at' => now()->subDays($this->faker->numberBetween(1, 45)),
            'completed_at' => null,
            'contracted_price_cents' => 120000,
            'paid_cents' => $this->faker->numberBetween(20000, 120000),
        ];
    }
}
