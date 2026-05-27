<?php

namespace Database\Factories;

use App\Enums\ArticleStatus;
use App\Models\KnowledgeArticle;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<KnowledgeArticle>
 */
class KnowledgeArticleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->faker->sentence(5);

        return [
            'title' => $title,
            'slug' => Str::slug($title).'-'.$this->faker->unique()->numberBetween(100, 999),
            'category' => $this->faker->randomElement(['exam', 'theory', 'practice', 'news']),
            'excerpt' => $this->faker->sentence(18),
            'body' => $this->faker->paragraphs(5, true),
            'status' => ArticleStatus::Published,
            'published_at' => now()->subDays($this->faker->numberBetween(1, 60)),
            'seo_title' => $title,
            'meta_description' => $this->faker->sentence(16),
            'canonical_url' => null,
            'open_graph_image' => null,
            'structured_data' => null,
        ];
    }
}
