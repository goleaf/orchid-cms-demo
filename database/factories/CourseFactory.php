<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\CourseCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Course>
 */
class CourseFactory extends Factory
{
    protected $model = Course::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = 'Category '.$this->faker->randomElement(['A', 'B', 'C']).' '.$this->faker->randomElement(['Standard', 'Premium', 'Intensive']);
        $priceCents = $this->faker->numberBetween(80_000, 160_000);

        return [
            'uuid' => (string) Str::uuid(),
            'course_category_id' => CourseCategory::factory(),
            'code' => strtoupper($this->faker->unique()->bothify('COURSE-####')),
            'title' => $title,
            'title_translations' => [
                'ru' => $title,
                'en' => $title,
            ],
            'name_translations' => [
                'ru' => $title,
                'en' => $title,
            ],
            'slug' => $this->faker->unique()->slug(3),
            'license_category' => 'B',
            'transmission' => $this->faker->randomElement(['manual', 'automatic']),
            'theory_hours' => $this->faker->numberBetween(20, 45),
            'practice_hours' => $this->faker->numberBetween(10, 35),
            'duration_weeks' => $this->faker->numberBetween(6, 16),
            'duration_translations' => [
                'ru' => '8 недель',
                'en' => '8 weeks',
            ],
            'format' => $this->faker->randomElement(['offline', 'online', 'hybrid', 'individual', 'group']),
            'available_languages' => ['ru', 'en'],
            'price_cents' => $priceCents,
            'old_price_cents' => null,
            'price' => $priceCents / 100,
            'old_price' => null,
            'currency' => 'EUR',
            'description' => $this->faker->paragraph(),
            'short_description' => $this->faker->sentence(12),
            'short_description_translations' => [
                'ru' => $this->faker->sentence(12),
                'en' => $this->faker->sentence(12),
            ],
            'description_translations' => [
                'ru' => $this->faker->paragraph(),
                'en' => $this->faker->paragraph(),
            ],
            'program_summary_translations' => [
                'ru' => $this->faker->paragraph(),
                'en' => $this->faker->paragraph(),
            ],
            'required_documents' => ['ID card', 'Medical certificate'],
            'admission_requirements' => $this->faker->sentence(12),
            'requirements_translations' => [
                'ru' => $this->faker->sentence(12),
                'en' => $this->faker->sentence(12),
            ],
            'included_items' => $this->faker->sentence(12),
            'included_items_translations' => [
                'ru' => $this->faker->sentence(12),
                'en' => $this->faker->sentence(12),
            ],
            'includes_translations' => [
                'ru' => $this->faker->sentence(12),
                'en' => $this->faker->sentence(12),
            ],
            'extra_costs' => $this->faker->sentence(10),
            'extra_costs_translations' => [
                'ru' => $this->faker->sentence(10),
                'en' => $this->faker->sentence(10),
            ],
            'excludes_translations' => [
                'ru' => $this->faker->sentence(10),
                'en' => $this->faker->sentence(10),
            ],
            'theory_program' => $this->faker->sentence(12),
            'theory_program_translations' => [
                'ru' => $this->faker->sentence(12),
                'en' => $this->faker->sentence(12),
            ],
            'practice_program' => $this->faker->sentence(12),
            'practice_program_translations' => [
                'ru' => $this->faker->sentence(12),
                'en' => $this->faker->sentence(12),
            ],
            'is_active' => true,
            'is_visible_on_site' => true,
            'is_featured' => false,
            'seo_title' => null,
            'seo_title_translations' => null,
            'meta_description' => null,
            'seo_description_translations' => null,
            'canonical_url' => null,
            'open_graph_image' => null,
            'og_image' => null,
            'image_path' => null,
            'icon' => null,
            'og_title' => null,
            'og_title_translations' => null,
            'og_description' => null,
            'og_description_translations' => null,
            'structured_data' => null,
            'sort_order' => 0,
            'created_by_id' => null,
            'updated_by_id' => null,
        ];
    }
}
