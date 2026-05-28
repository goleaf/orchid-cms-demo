<?php

namespace Database\Factories;

use App\Enums\CourseFormat;
use App\Enums\TransmissionType;
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
        $shortDescription = $this->faker->sentence(12);
        $description = $this->faker->paragraph();
        $requirements = $this->faker->sentence(14);
        $includedItems = $this->faker->sentence(12);
        $extraCosts = $this->faker->sentence(10);
        $theoryProgram = $this->faker->sentence(12);
        $practiceProgram = $this->faker->sentence(12);

        return [
            'uuid' => (string) Str::uuid(),
            'course_category_id' => null,
            'code' => strtoupper($this->faker->unique()->bothify('PROGRAM-####')),
            'title' => $title,
            'title_translations' => $this->translations($title),
            'name_translations' => $this->translations($title),
            'slug' => $this->faker->unique()->slug(3),
            'license_category' => 'B',
            'transmission' => $this->faker->randomElement(TransmissionType::values()),
            'theory_hours' => $this->faker->numberBetween(30, 45),
            'practice_hours' => $this->faker->numberBetween(20, 35),
            'duration_weeks' => $this->faker->numberBetween(8, 16),
            'duration_translations' => $this->translations('8-16 недель', '8-16 weeks', '8-16 savaiciu', '8-16 tygodni'),
            'format' => $this->faker->randomElement(CourseFormat::values()),
            'available_languages' => ['ru', 'en', 'lt', 'pl'],
            'price_cents' => $priceCents,
            'old_price_cents' => null,
            'price' => $priceCents / 100,
            'old_price' => null,
            'currency' => 'EUR',
            'description' => $description,
            'short_description' => $shortDescription,
            'short_description_translations' => $this->translations($shortDescription),
            'description_translations' => $this->translations($description),
            'program_summary_translations' => $this->translations($description),
            'required_documents' => ['ID card', 'Medical certificate'],
            'admission_requirements' => $requirements,
            'requirements_translations' => $this->translations($requirements),
            'included_items' => $includedItems,
            'included_items_translations' => $this->translations($includedItems),
            'includes_translations' => $this->translations($includedItems),
            'extra_costs' => $extraCosts,
            'extra_costs_translations' => $this->translations($extraCosts),
            'excludes_translations' => $this->translations($extraCosts),
            'theory_program' => $theoryProgram,
            'theory_program_translations' => $this->translations($theoryProgram),
            'practice_program' => $practiceProgram,
            'practice_program_translations' => $this->translations($practiceProgram),
            'is_active' => true,
            'is_visible_on_site' => true,
            'is_indexable' => true,
            'is_featured' => false,
            'seo_title' => $title,
            'seo_title_translations' => $this->translations($title),
            'meta_description' => $shortDescription,
            'seo_description_translations' => $this->translations($shortDescription),
            'canonical_url' => null,
            'open_graph_image' => null,
            'og_image' => null,
            'image_path' => null,
            'icon' => null,
            'og_title' => $title,
            'og_title_translations' => $this->translations($title),
            'og_description' => $shortDescription,
            'og_description_translations' => $this->translations($shortDescription),
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
        $title = $this->legacyTranslations($translations, 'title');
        $shortDescription = $this->legacyTranslations($translations, 'short_description');
        $description = $this->legacyTranslations($translations, 'description');
        $includedItems = $this->legacyTranslations($translations, 'included_items');
        $extraCosts = $this->legacyTranslations($translations, 'extra_costs');
        $theoryProgram = $this->legacyTranslations($translations, 'theory_program');
        $practiceProgram = $this->legacyTranslations($translations, 'practice_program');
        $seoTitle = $this->legacyTranslations($translations, 'seo_title', $title);
        $seoDescription = $this->legacyTranslations($translations, 'seo_description', $shortDescription);

        return $this->state(fn (): array => [
            'title' => $title['ru'],
            'title_translations' => $title,
            'name_translations' => $title,
            'short_description' => $shortDescription['ru'],
            'short_description_translations' => $shortDescription,
            'description' => $description['ru'],
            'description_translations' => $description,
            'program_summary_translations' => $description,
            'included_items' => $includedItems['ru'],
            'included_items_translations' => $includedItems,
            'includes_translations' => $includedItems,
            'extra_costs' => $extraCosts['ru'],
            'extra_costs_translations' => $extraCosts,
            'excludes_translations' => $extraCosts,
            'theory_program' => $theoryProgram['ru'],
            'theory_program_translations' => $theoryProgram,
            'practice_program' => $practiceProgram['ru'],
            'practice_program_translations' => $practiceProgram,
            'seo_title' => $seoTitle['ru'],
            'seo_title_translations' => $seoTitle,
            'meta_description' => $seoDescription['ru'],
            'seo_description_translations' => $seoDescription,
            'og_title' => $seoTitle['ru'],
            'og_title_translations' => $seoTitle,
            'og_description' => $seoDescription['ru'],
            'og_description_translations' => $seoDescription,
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

    /**
     * @param  array<string, string>  $translations
     * @param  array<string, string>|null  $fallback
     * @return array<string, string>
     */
    private function legacyTranslations(array $translations, string $key, ?array $fallback = null): array
    {
        $ru = $translations[$key] ?? $fallback['ru'] ?? '';
        $en = $translations[$key.'_en'] ?? $fallback['en'] ?? $ru;
        $lt = $translations[$key.'_lt'] ?? $fallback['lt'] ?? $en;
        $pl = $translations[$key.'_pl'] ?? $fallback['pl'] ?? $en;

        return $this->translations($ru, $en, $lt, $pl);
    }
}
