<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\PricingPackage;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<PricingPackage>
 */
class PricingPackageFactory extends Factory
{
    protected $model = PricingPackage::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $nameTranslations = $this->translations('Пакет обучения', 'Training package', 'Mokymo paketas', 'Pakiet szkoleniowy');
        $descriptionTranslations = $this->translations(
            'Пакет для страницы цен автошколы.',
            'Package for the driving school pricing page.',
            'Paketas vairavimo mokyklos kainu puslapiui.',
            'Pakiet dla cennika szkoly jazdy.'
        );

        return [
            'uuid' => (string) Str::uuid(),
            'course_id' => Course::factory(),
            'course_category_id' => CourseCategory::factory(),
            'code' => strtoupper($this->faker->unique()->bothify('PRICE-####')),
            'slug' => $this->faker->unique()->slug(2),
            'name_translations' => $nameTranslations,
            'description_translations' => $descriptionTranslations,
            'features_translations' => $this->translationLists(
                ['Теория', 'Практика'],
                ['Theory', 'Practice'],
                ['Teorija', 'Praktika'],
                ['Teoria', 'Praktyka']
            ),
            'price' => $this->faker->randomFloat(2, 390, 1590),
            'old_price' => null,
            'currency' => 'EUR',
            'theory_hours' => $this->faker->numberBetween(0, 45),
            'practice_hours' => $this->faker->numberBetween(0, 35),
            'is_active' => true,
            'is_visible_on_site' => true,
            'is_featured' => false,
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

    public function visibleOnSite(): static
    {
        return $this->state(fn (): array => ['is_visible_on_site' => true]);
    }

    public function hiddenFromSite(): static
    {
        return $this->state(fn (): array => ['is_visible_on_site' => false]);
    }

    public function featured(): static
    {
        return $this->state(fn (): array => ['is_featured' => true]);
    }

    public function standard(): static
    {
        return $this->state(fn (): array => [
            'code' => 'standard',
            'slug' => 'standard',
            'name_translations' => $this->translations('Стандарт', 'Standard', 'Standartas', 'Pakiet standardowy'),
            'price' => 1290.00,
            'sort_order' => 10,
        ]);
    }

    public function premium(): static
    {
        return $this->state(fn (): array => [
            'code' => 'premium',
            'slug' => 'premium',
            'name_translations' => $this->translations('Премиум', 'Premium', 'Premium paketas', 'Pakiet premium'),
            'price' => 1490.00,
            'is_featured' => true,
            'sort_order' => 20,
        ]);
    }

    public function intensive(): static
    {
        return $this->state(fn (): array => [
            'code' => 'intensive',
            'slug' => 'intensive',
            'name_translations' => $this->translations('Интенсив', 'Intensive', 'Intensyvus', 'Intensywny'),
            'price' => 1590.00,
            'sort_order' => 30,
        ]);
    }

    public function extraLessons(): static
    {
        return $this->state(fn (): array => [
            'code' => 'extra_lessons',
            'slug' => 'extra-lessons',
            'name_translations' => $this->translations('Дополнительные уроки', 'Extra Lessons', 'Papildomos pamokos', 'Dodatkowe lekcje'),
            'price' => 45.00,
            'theory_hours' => 0,
            'practice_hours' => 1,
            'sort_order' => 40,
        ]);
    }

    public function translated(): static
    {
        return $this->state(fn (): array => [
            'name_translations' => $this->translations('Пакет обучения', 'Training package', 'Mokymo paketas', 'Pakiet szkoleniowy'),
            'description_translations' => $this->translations('Пакет для страницы цен автошколы.', 'Package for the driving school pricing page.', 'Paketas vairavimo mokyklos kainu puslapiui.', 'Pakiet dla cennika szkoly jazdy.'),
            'features_translations' => $this->translationLists(['Теория', 'Практика'], ['Theory', 'Practice'], ['Teorija', 'Praktika'], ['Teoria', 'Praktyka']),
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
     * @param  array<int, string>  $ru
     * @param  array<int, string>|null  $en
     * @param  array<int, string>|null  $lt
     * @param  array<int, string>|null  $pl
     * @return array<string, array<int, string>>
     */
    private function translationLists(array $ru, ?array $en = null, ?array $lt = null, ?array $pl = null): array
    {
        return [
            'ru' => $ru,
            'en' => $en ?? $ru,
            'lt' => $lt ?? $en ?? $ru,
            'pl' => $pl ?? $en ?? $ru,
        ];
    }
}
