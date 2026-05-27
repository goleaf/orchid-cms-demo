<?php

namespace Database\Factories;

use App\Models\CourseCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<CourseCategory>
 */
class CourseCategoryFactory extends Factory
{
    protected $model = CourseCategory::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $code = $this->faker->unique()->slug(2);
        $name = str($code)->replace('-', ' ')->title()->toString();

        return [
            'uuid' => (string) Str::uuid(),
            'code' => $code,
            'slug' => $code,
            'name_translations' => [
                'ru' => $name,
                'en' => $name,
            ],
            'description_translations' => [
                'ru' => $this->faker->paragraph(),
                'en' => $this->faker->paragraph(),
            ],
            'short_description_translations' => [
                'ru' => $this->faker->sentence(10),
                'en' => $this->faker->sentence(10),
            ],
            'seo_title_translations' => null,
            'seo_description_translations' => null,
            'image' => null,
            'icon' => null,
            'is_active' => true,
            'is_visible_on_site' => true,
            'sort_order' => 0,
            'created_by_id' => null,
            'updated_by_id' => null,
        ];
    }
}
