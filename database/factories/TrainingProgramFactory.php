<?php

namespace Database\Factories;

use App\Models\TrainingProgram;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

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
        $title = 'Category B '.$this->faker->randomElement(['Standard', 'Intensive', 'Evening']);
        $priceCents = $this->faker->numberBetween(85000, 150000);

        return [
            'uuid' => (string) Str::uuid(),
            'course_category_id' => null,
            'code' => strtoupper($this->faker->unique()->bothify('PROGRAM-####')),
            'title' => $title,
            'name_translations' => [
                'ru' => $title,
                'en' => $title,
            ],
            'slug' => $this->faker->unique()->slug(3),
            'license_category' => 'B',
            'transmission' => $this->faker->randomElement(['manual', 'automatic']),
            'theory_hours' => $this->faker->numberBetween(30, 45),
            'practice_hours' => $this->faker->numberBetween(20, 35),
            'duration_weeks' => $this->faker->numberBetween(8, 16),
            'duration_translations' => null,
            'format' => $this->faker->randomElement(['offline', 'online', 'mixed']),
            'available_languages' => ['Lithuanian', 'English'],
            'price_cents' => $priceCents,
            'old_price_cents' => null,
            'price' => $priceCents / 100,
            'old_price' => null,
            'currency' => 'EUR',
            'description' => $this->faker->paragraph(),
            'short_description' => $this->faker->sentence(12),
            'program_summary_translations' => null,
            'required_documents' => ['ID card', 'Medical certificate'],
            'admission_requirements' => $this->faker->sentence(14),
            'requirements_translations' => null,
            'included_items' => $this->faker->sentence(12),
            'includes_translations' => null,
            'extra_costs' => $this->faker->sentence(10),
            'excludes_translations' => null,
            'theory_program' => $this->faker->sentence(12),
            'practice_program' => $this->faker->sentence(12),
            'is_active' => true,
            'is_visible_on_site' => true,
            'is_indexable' => true,
            'is_featured' => false,
            'seo_title' => null,
            'meta_description' => null,
            'canonical_url' => null,
            'open_graph_image' => null,
            'og_image' => null,
            'image_path' => null,
            'icon' => null,
            'structured_data' => null,
            'sort_order' => 0,
            'created_by_id' => null,
            'updated_by_id' => null,
        ];
    }

    /**
     * @param  array<string, string>  $translations
     */
    public function publicCatalog(array $translations): static
    {
        return $this->state(fn (): array => [
            'title' => $translations['title'],
            'title_translations' => [
                'ru' => $translations['title'],
                'en' => $translations['title_en'] ?? $translations['title'],
            ],
            'short_description' => $translations['short_description'],
            'short_description_translations' => [
                'ru' => $translations['short_description'],
                'en' => $translations['short_description_en'] ?? $translations['short_description'],
            ],
            'description' => $translations['description'],
            'description_translations' => [
                'ru' => $translations['description'],
                'en' => $translations['description_en'] ?? $translations['description'],
            ],
            'included_items' => $translations['included_items'],
            'included_items_translations' => [
                'ru' => $translations['included_items'],
                'en' => $translations['included_items_en'] ?? $translations['included_items'],
            ],
            'extra_costs' => $translations['extra_costs'],
            'extra_costs_translations' => [
                'ru' => $translations['extra_costs'],
                'en' => $translations['extra_costs_en'] ?? $translations['extra_costs'],
            ],
            'theory_program' => $translations['theory_program'],
            'theory_program_translations' => [
                'ru' => $translations['theory_program'],
                'en' => $translations['theory_program_en'] ?? $translations['theory_program'],
            ],
            'practice_program' => $translations['practice_program'],
            'practice_program_translations' => [
                'ru' => $translations['practice_program'],
                'en' => $translations['practice_program_en'] ?? $translations['practice_program'],
            ],
            'seo_title' => $translations['seo_title'] ?? $translations['title'],
            'seo_title_translations' => [
                'ru' => $translations['seo_title'] ?? $translations['title'],
                'en' => $translations['seo_title_en'] ?? $translations['title_en'] ?? $translations['title'],
            ],
            'meta_description' => $translations['seo_description'] ?? $translations['short_description'],
            'seo_description_translations' => [
                'ru' => $translations['seo_description'] ?? $translations['short_description'],
                'en' => $translations['seo_description_en'] ?? $translations['short_description_en'] ?? $translations['short_description'],
            ],
        ]);
    }
}
