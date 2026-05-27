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
        $summary = $this->faker->sentence(12);
        $body = $this->faker->paragraphs(2, true);

        return [
            'uuid' => (string) Str::uuid(),
            'type' => 'custom',
            'slug' => $this->faker->unique()->slug(3),
            'title_translations' => $this->translations($title),
            'subtitle_translations' => $this->translations($summary),
            'content_translations' => $this->translations($body),
            'excerpt_translations' => $this->translations($summary),
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

    public function home(): static
    {
        return $this->state(fn (): array => [
            'type' => 'home',
            'slug' => 'home',
            'template' => 'home',
            'title_translations' => $this->translations('Главная', 'Home', 'Pradzia', 'Strona glowna'),
        ]);
    }

    public function pricing(): static
    {
        return $this->state(fn (): array => [
            'type' => 'pricing',
            'slug' => 'pricing',
            'template' => 'pricing',
            'title_translations' => $this->translations('Цены', 'Pricing', 'Kainos', 'Ceny'),
        ]);
    }

    public function contacts(): static
    {
        return $this->state(fn (): array => [
            'type' => 'contacts',
            'slug' => 'contacts',
            'template' => 'contacts',
            'title_translations' => $this->translations('Контакты', 'Contacts', 'Kontaktai', 'Kontakty'),
        ]);
    }

    public function thankYou(): static
    {
        return $this->state(fn (): array => [
            'type' => 'thank_you',
            'slug' => 'thank-you',
            'template' => 'thank-you',
            'is_indexable' => false,
            'title_translations' => $this->translations('Спасибо', 'Thank you', 'Aciu', 'Dziekujemy'),
        ]);
    }

    public function privacyPolicy(): static
    {
        return $this->state(fn (): array => [
            'type' => 'privacy_policy',
            'slug' => 'privacy-policy',
            'template' => 'legal',
            'title_translations' => $this->translations('Политика конфиденциальности', 'Privacy policy', 'Privatumo politika', 'Polityka prywatnosci'),
        ]);
    }

    public function published(): static
    {
        return $this->state(fn (): array => [
            'is_active' => true,
            'published_at' => now(),
        ]);
    }

    public function unpublished(): static
    {
        return $this->state(fn (): array => [
            'is_active' => false,
            'published_at' => null,
        ]);
    }

    public function active(): static
    {
        return $this->state(fn (): array => ['is_active' => true]);
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }

    public function translated(): static
    {
        return $this->state(fn (): array => [
            'title_translations' => $this->translations('Страница сайта', 'Website page', 'Svetaines puslapis', 'Strona witryny'),
            'subtitle_translations' => $this->translations('Краткое описание страницы.', 'Short page summary.', 'Trumpa puslapio santrauka.', 'Krotki opis strony.'),
            'content_translations' => $this->translations('Контент страницы для автошколы.', 'Driving school page content.', 'Vairavimo mokyklos puslapio turinys.', 'Tresc strony szkoly jazdy.'),
            'excerpt_translations' => $this->translations('Краткий фрагмент страницы.', 'Short page excerpt.', 'Trumpa puslapio istrauka.', 'Krotki fragment strony.'),
        ]);
    }

    public function indexable(): static
    {
        return $this->state(fn (): array => ['is_indexable' => true]);
    }

    public function noindex(): static
    {
        return $this->state(fn (): array => ['is_indexable' => false]);
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
