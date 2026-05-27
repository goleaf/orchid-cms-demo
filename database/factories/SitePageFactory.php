<?php

namespace Database\Factories;

use App\Models\SitePage;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<SitePage>
 */
class SitePageFactory extends Factory
{
    protected $model = SitePage::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->faker->sentence(3);

        return [
            'uuid' => (string) Str::uuid(),
            'type' => 'custom',
            'slug' => $this->faker->unique()->slug(3),
            'title_translations' => [
                'ru' => $title,
                'en' => $title,
            ],
            'subtitle_translations' => [
                'ru' => $this->faker->sentence(8),
                'en' => $this->faker->sentence(8),
            ],
            'content_translations' => [
                'ru' => $this->faker->paragraphs(2, true),
                'en' => $this->faker->paragraphs(2, true),
            ],
            'excerpt_translations' => [
                'ru' => $this->faker->sentence(12),
                'en' => $this->faker->sentence(12),
            ],
            'seo_title_translations' => null,
            'seo_description_translations' => null,
            'og_title_translations' => null,
            'og_description_translations' => null,
            'og_image' => null,
            'template' => 'default',
            'is_active' => true,
            'is_indexable' => true,
            'sort_order' => 0,
            'published_at' => now(),
            'created_by_id' => null,
            'updated_by_id' => null,
        ];
    }
}
