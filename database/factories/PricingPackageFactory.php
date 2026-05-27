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
        $name = $this->faker->randomElement(['Standard', 'Premium', 'Intensive', 'Extra Lessons']);

        return [
            'uuid' => (string) Str::uuid(),
            'course_id' => Course::factory(),
            'course_category_id' => CourseCategory::factory(),
            'code' => strtoupper($this->faker->unique()->bothify('PRICE-####')),
            'slug' => $this->faker->unique()->slug(2),
            'name_translations' => [
                'ru' => $name,
                'en' => $name,
            ],
            'description_translations' => [
                'ru' => $this->faker->sentence(12),
                'en' => $this->faker->sentence(12),
            ],
            'features_translations' => [
                'ru' => [$this->faker->sentence(6), $this->faker->sentence(6)],
                'en' => [$this->faker->sentence(6), $this->faker->sentence(6)],
            ],
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
}
