<?php

namespace Database\Factories;

use App\Enums\ReviewStatus;
use App\Models\Branch;
use App\Models\Course;
use App\Models\Instructor;
use App\Models\Testimonial;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Testimonial>
 */
class TestimonialFactory extends Factory
{
    protected $model = Testimonial::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->name();
        $body = $this->faker->paragraph();

        return [
            'uuid' => (string) Str::uuid(),
            'student_profile_id' => null,
            'training_program_id' => Course::factory(),
            'training_group_id' => null,
            'branch_id' => Branch::factory(),
            'instructor_id' => Instructor::factory(),
            'author_name' => $name,
            'name_translations' => [
                'ru' => $name,
                'en' => $name,
            ],
            'rating' => $this->faker->numberBetween(4, 5),
            'title' => $this->faker->sentence(5),
            'body' => $body,
            'text_translations' => [
                'ru' => $body,
                'en' => $body,
            ],
            'image' => null,
            'video_url' => null,
            'admin_reply' => null,
            'status' => ReviewStatus::Published,
            'is_active' => true,
            'is_featured' => false,
            'published_at' => now()->subDays($this->faker->numberBetween(1, 60)),
            'sort_order' => 0,
            'created_by_id' => null,
            'updated_by_id' => null,
        ];
    }
}
