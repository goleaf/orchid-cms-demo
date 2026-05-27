<?php

namespace Database\Factories;

use App\Enums\LessonStatus;
use App\Models\Branch;
use App\Models\DrivingLesson;
use App\Models\Enrollment;
use App\Models\Instructor;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DrivingLesson>
 */
class DrivingLessonFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'branch_id' => Branch::factory(),
            'enrollment_id' => Enrollment::factory(),
            'instructor_id' => Instructor::factory(),
            'vehicle_id' => Vehicle::factory(),
            'lesson_type' => $this->faker->randomElement(['theory', 'practice', 'simulator']),
            'status' => LessonStatus::Scheduled,
            'starts_at' => now()->addDays($this->faker->numberBetween(1, 21))->setTime($this->faker->numberBetween(8, 17), 0),
            'ends_at' => now()->addDays($this->faker->numberBetween(1, 21))->setTime($this->faker->numberBetween(9, 18), 0),
            'topic' => $this->faker->randomElement(['City driving', 'Parking', 'Junctions', 'Exam route']),
            'location' => $this->faker->streetName(),
            'notes' => null,
        ];
    }
}
