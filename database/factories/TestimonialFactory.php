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
            'name_translations' => $this->translations($name),
            'rating' => $this->faker->numberBetween(4, 5),
            'title' => $this->faker->sentence(5),
            'body' => $body,
            'text_translations' => $this->translations($body),
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

    public function active(): static
    {
        return $this->state(fn (): array => ['is_active' => true]);
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }

    public function featured(): static
    {
        return $this->state(fn (): array => ['is_featured' => true]);
    }

    public function published(): static
    {
        return $this->state(fn (): array => [
            'status' => ReviewStatus::Published,
            'published_at' => now()->subDay(),
            'is_active' => true,
        ]);
    }

    public function withRating(int $rating = 5): static
    {
        return $this->state(fn (): array => ['rating' => $rating]);
    }

    public function withVideo(string $url = 'https://video.example.com/review'): static
    {
        return $this->state(fn (): array => ['video_url' => $url]);
    }

    public function translated(): static
    {
        return $this->state(fn (): array => [
            'author_name' => 'Ieva N.',
            'name_translations' => $this->translations('Иева Н.', 'Ieva N.', 'Ieva N.', 'Ieva N.'),
            'body' => 'Great course and helpful instructors.',
            'text_translations' => $this->translations('Отличный курс и внимательные инструкторы.', 'Great course and helpful instructors.', 'Puikus kursas ir demesingi instruktoriai.', 'Swietny kurs i pomocni instruktorzy.'),
        ]);
    }

    /**
     * @return array<string, string>
     */
    private function translations(string $ru, ?string $en = null, ?string $lt = null, ?string $pl = null): array
    {
        return [
            'ru' => $ru,
            'en' => $en ?? $ru,
            'lt' => $lt ?? $en ?? $ru,
            'pl' => $pl ?? $en ?? $ru,
        ];
    }
}
