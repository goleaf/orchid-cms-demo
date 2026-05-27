<?php

namespace Database\Seeders;

use App\Models\SitePage;
use Illuminate\Database\Seeder;

class WebsitePageSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            'home' => [
                'factory' => 'home',
                'type' => 'home',
                'slug' => 'home',
                'title' => ['ru' => 'Главная', 'en' => 'Home', 'lt' => 'Pradzia', 'pl' => 'Strona glowna'],
            ],
            'pricing' => [
                'factory' => 'pricing',
                'type' => 'pricing',
                'slug' => 'pricing',
                'title' => ['ru' => 'Цены', 'en' => 'Pricing', 'lt' => 'Kainos', 'pl' => 'Ceny'],
            ],
            'contacts' => [
                'factory' => 'contacts',
                'type' => 'contacts',
                'slug' => 'contacts',
                'title' => ['ru' => 'Контакты', 'en' => 'Contacts', 'lt' => 'Kontaktai', 'pl' => 'Kontakty'],
            ],
            'thank-you' => [
                'factory' => 'thankYou',
                'type' => 'thank_you',
                'slug' => 'thank-you',
                'title' => ['ru' => 'Спасибо', 'en' => 'Thank you', 'lt' => 'Aciu', 'pl' => 'Dziekujemy'],
                'is_indexable' => false,
            ],
            'privacy-policy' => [
                'factory' => 'privacyPolicy',
                'type' => 'privacy_policy',
                'slug' => 'privacy-policy',
                'title' => ['ru' => 'Политика конфиденциальности', 'en' => 'Privacy policy', 'lt' => 'Privatumo politika', 'pl' => 'Polityka prywatnosci'],
            ],
            'terms' => [
                'factory' => null,
                'type' => 'terms',
                'slug' => 'terms',
                'title' => ['ru' => 'Условия обучения', 'en' => 'Training terms', 'lt' => 'Mokymo salygos', 'pl' => 'Warunki szkolenia'],
            ],
        ];

        foreach ($pages as $page) {
            $factory = SitePage::factory()->published()->active();

            if ($page['factory'] !== null) {
                $factory = $factory->{$page['factory']}();
            }

            $payload = $factory
                ->make([
                    'type' => $page['type'],
                    'slug' => $page['slug'],
                    'title_translations' => $page['title'],
                    'content_translations' => [
                        'ru' => 'Демонстрационный контент публичной страницы автошколы. TODO: review copy with a human translator.',
                        'en' => 'Demo driving school public page content. TODO: review copy with a human translator.',
                        'lt' => 'Demonstracinis vairavimo mokyklos puslapio turinys. TODO: review copy with a human translator.',
                        'pl' => 'Przykladowa tresc strony szkoly jazdy. TODO: review copy with a human translator.',
                    ],
                    'excerpt_translations' => [
                        'ru' => 'Публичная страница автошколы.',
                        'en' => 'Public driving school page.',
                        'lt' => 'Viesas vairavimo mokyklos puslapis.',
                        'pl' => 'Publiczna strona szkoly jazdy.',
                    ],
                    'seo_title_translations' => $page['title'],
                    'is_indexable' => $page['is_indexable'] ?? true,
                ])
                ->only((new SitePage)->getFillable());

            SitePage::query()->updateOrCreate(['slug' => $page['slug']], $payload);
        }
    }
}
