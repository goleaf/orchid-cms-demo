<?php

namespace Tests\Feature;

use App\Models\LandingPage;
use App\Models\Language;
use Database\Seeders\LandingPageHomeSeeder;
use Database\Seeders\LanguageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_renders_published_content(): void
    {
        LandingPage::factory()
            ->home()
            ->published()
            ->create([
                'hero_title' => 'Editable driving school platform',
                'hero_title_translations' => [
                    'en' => 'Editable driving school platform',
                    'ru' => 'Редактируемый сайт автошколы',
                ],
                'about_heading' => 'Prepared content flow',
                'about_heading_translations' => [
                    'en' => 'Prepared content flow',
                    'ru' => 'Готовый поток контента',
                ],
            ]);

        $response = $this->get('/');

        $response
            ->assertOk()
            ->assertSee('Editable driving school platform')
            ->assertSee('Prepared content flow');
    }

    public function test_homepage_does_not_render_unpublished_content(): void
    {
        LandingPage::factory()
            ->home()
            ->create([
                'hero_title' => 'Draft homepage',
                'published_at' => null,
            ]);

        $this->get('/')->assertNotFound();
    }

    public function test_home_content_seeder_populates_all_active_languages(): void
    {
        $this->seed(LanguageSeeder::class);
        $this->seed(LandingPageHomeSeeder::class);

        $page = LandingPage::query()
            ->where('slug', 'home')
            ->firstOrFail();

        foreach (Language::activeCodes() as $locale) {
            foreach ($this->homeContentFields() as $field) {
                $translations = $page->getTranslations($field);

                $this->assertArrayHasKey($locale, $translations);
                $this->assertNotSame('', trim((string) $translations[$locale]));
            }
        }

        $this->assertNotNull($page->published_at);
    }

    /**
     * @return array<int, string>
     */
    private function homeContentFields(): array
    {
        return [
            'title',
            'eyebrow',
            'hero_title',
            'hero_summary',
            'about_heading',
            'about_body',
            'offer_one_title',
            'offer_one_body',
            'offer_two_title',
            'offer_two_body',
            'offer_three_title',
            'offer_three_body',
        ];
    }
}
