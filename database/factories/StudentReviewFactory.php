<?php

namespace Database\Factories;

use App\Enums\ReviewStatus;
use App\Models\Instructor;
use App\Models\StudentReview;
use App\Models\TrainingProgram;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentReview>
 */
class StudentReviewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'student_profile_id' => null,
            'training_program_id' => TrainingProgram::factory(),
            'training_group_id' => null,
            'instructor_id' => Instructor::factory(),
            'author_name' => $this->faker->name(),
            'rating' => $this->faker->numberBetween(4, 5),
            'title' => $this->faker->sentence(5),
            'body' => $this->faker->paragraph(),
            'video_url' => null,
            'admin_reply' => $this->faker->optional()->sentence(),
            'status' => ReviewStatus::Published,
            'published_at' => now()->subDays($this->faker->numberBetween(1, 60)),
        ];
    }
}
