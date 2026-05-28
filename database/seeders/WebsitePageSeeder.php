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
                    'subtitle_translations' => [
                        'ru' => 'Информация автошколы для учеников и заявок.',
                        'en' => 'Driving school information for students and applications.',
                        'lt' => 'Vairavimo mokyklos informacija mokiniams ir paraiskoms.',
                        'pl' => 'Informacje szkoly jazdy dla uczniow i zgloszen.',
                    ],
                    'content_translations' => [
                        'ru' => 'Публичная страница автошколы с актуальной информацией о курсах, группах, филиалах и заявках.',
                        'en' => 'Public driving school page with current information about courses, groups, branches, and applications.',
                        'lt' => 'Viesas vairavimo mokyklos puslapis su aktualia informacija apie kursus, grupes, filialus ir paraiskas.',
                        'pl' => 'Publiczna strona szkoly jazdy z aktualnymi informacjami o kursach, grupach, oddzialach i zgloszeniach.',
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
