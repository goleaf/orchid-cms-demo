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
        $slug = $this->faker->unique()->slug(3);
        $title = $this->faker->sentence(3);
        $summary = $this->faker->sentence(12);
        $body = $this->faker->paragraphs(2, true);
        $titleTranslations = $this->translations($title);
        $summaryTranslations = $this->translations($summary);

        return [
            'uuid' => (string) Str::uuid(),
            'type' => 'custom',
            'slug' => $slug,
            'title_translations' => $titleTranslations,
            'subtitle_translations' => $summaryTranslations,
            'content_translations' => $this->translations($body),
            'excerpt_translations' => $summaryTranslations,
            'seo_title_translations' => $titleTranslations,
            'seo_description_translations' => $summaryTranslations,
            'og_title_translations' => $titleTranslations,
            'og_description_translations' => $summaryTranslations,
            'og_image' => 'images/driving-school-hero.png',
            'canonical_url' => url('/pages/'.$slug),
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
            'canonical_url' => url('/'),
            ...$this->contentState(
                'Главная',
                'Home',
                'Pradžia',
                'Strona główna',
                'Курсы, группы, цены и заявки автошколы в одном месте.',
                'Driving school courses, groups, prices, and applications in one place.',
                'Vairavimo mokyklos kursai, grupės, kainos ir registracija vienoje vietoje.',
                'Kursy, grupy, ceny i zapisy szkoły jazdy w jednym miejscu.',
            ),
        ]);
    }

    public function pricing(): static
    {
        return $this->state(fn (): array => [
            'type' => 'pricing',
            'slug' => 'pricing',
            'template' => 'pricing',
            'canonical_url' => url('/pricing'),
            ...$this->contentState(
                'Цены',
                'Pricing',
                'Kainos',
                'Ceny',
                'Пакеты обучения, состав курса и условия оплаты для учеников.',
                'Training packages, course contents, and payment terms for students.',
                'Mokymo paketai, kurso sudėtis ir apmokėjimo sąlygos mokiniams.',
                'Pakiety szkoleniowe, zakres kursu i warunki płatności dla uczniów.',
            ),
        ]);
    }

    public function contacts(): static
    {
        return $this->state(fn (): array => [
            'type' => 'contacts',
            'slug' => 'contacts',
            'template' => 'contacts',
            'canonical_url' => url('/contacts'),
            ...$this->contentState(
                'Контакты',
                'Contacts',
                'Kontaktai',
                'Kontakty',
                'Филиалы, телефоны, рабочее время и формы связи автошколы.',
                'Branches, phone numbers, opening hours, and driving school contact forms.',
                'Filialai, telefonai, darbo laikas ir vairavimo mokyklos kontaktų formos.',
                'Oddziały, telefony, godziny pracy i formularze kontaktowe szkoły jazdy.',
            ),
        ]);
    }

    public function thankYou(): static
    {
        return $this->state(fn (): array => [
            'type' => 'thank_you',
            'slug' => 'thank-you',
            'template' => 'thank-you',
            'canonical_url' => url('/thank-you'),
            'is_indexable' => false,
            ...$this->contentState(
                'Спасибо',
                'Thank you',
                'Ačiū',
                'Dziękujemy',
                'Подтверждение отправки заявки или сообщения в автошколу.',
                'Confirmation after sending an application or message to the driving school.',
                'Patvirtinimas išsiuntus registraciją arba žinutę vairavimo mokyklai.',
                'Potwierdzenie wysłania zgłoszenia albo wiadomości do szkoły jazdy.',
            ),
        ]);
    }

    public function privacyPolicy(): static
    {
        return $this->state(fn (): array => [
            'type' => 'privacy_policy',
            'slug' => 'privacy-policy',
            'template' => 'legal',
            'canonical_url' => url('/pages/privacy-policy'),
            ...$this->contentState(
                'Политика конфиденциальности',
                'Privacy policy',
                'Privatumo politika',
                'Polityka prywatności',
                'Как автошкола обрабатывает заявки, контакты, документы и учебные данные.',
                'How the driving school handles applications, contacts, documents, and training data.',
                'Kaip vairavimo mokykla tvarko paraiškas, kontaktus, dokumentus ir mokymo duomenis.',
                'Jak szkoła jazdy przetwarza zgłoszenia, kontakty, dokumenty i dane szkoleniowe.',
            ),
        ]);
    }

    public function terms(): static
    {
        return $this->state(fn (): array => [
            'type' => 'terms',
            'slug' => 'terms',
            'template' => 'legal',
            'canonical_url' => url('/pages/terms'),
            ...$this->contentState(
                'Условия обучения',
                'Training terms',
                'Mokymo sąlygos',
                'Warunki szkolenia',
                'Правила записи, оплаты, посещения занятий и переноса обучения.',
                'Rules for enrollment, payment, attendance, and rescheduling training.',
                'Registracijos, apmokėjimo, pamokų lankymo ir mokymo perkėlimo taisyklės.',
                'Zasady zapisów, płatności, obecności i przenoszenia szkolenia.',
            ),
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
            'title_translations' => $this->translations('Страница сайта', 'Website page', 'Svetainės puslapis', 'Strona witryny'),
            'subtitle_translations' => $this->translations('Краткое описание страницы.', 'Short page summary.', 'Trumpa puslapio santrauka.', 'Krótki opis strony.'),
            'content_translations' => $this->translations('Контент страницы для автошколы.', 'Driving school page content.', 'Vairavimo mokyklos puslapio turinys.', 'Tresc strony szkoly jazdy.'),
            'excerpt_translations' => $this->translations('Краткий фрагмент страницы.', 'Short page excerpt.', 'Trumpa puslapio ištrauka.', 'Krótki fragment strony.'),
            'seo_title_translations' => $this->translations('Страница сайта', 'Website page', 'Svetainės puslapis', 'Strona witryny'),
            'seo_description_translations' => $this->translations('Краткое описание страницы.', 'Short page summary.', 'Trumpa puslapio santrauka.', 'Krótki opis strony.'),
            'og_title_translations' => $this->translations('Страница сайта', 'Website page', 'Svetainės puslapis', 'Strona witryny'),
            'og_description_translations' => $this->translations('Краткое описание страницы.', 'Short page summary.', 'Trumpa puslapio santrauka.', 'Krótki opis strony.'),
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

    /**
     * @return array<string, array<string, string>>
     */
    private function contentState(
        string $titleRu,
        string $titleEn,
        string $titleLt,
        string $titlePl,
        string $summaryRu,
        string $summaryEn,
        string $summaryLt,
        string $summaryPl,
    ): array {
        $title = $this->translations($titleRu, $titleEn, $titleLt, $titlePl);
        $summary = $this->translations($summaryRu, $summaryEn, $summaryLt, $summaryPl);

        return [
            'title_translations' => $title,
            'subtitle_translations' => $summary,
            'content_translations' => $summary,
            'excerpt_translations' => $summary,
            'seo_title_translations' => $title,
            'seo_description_translations' => $summary,
            'og_title_translations' => $title,
            'og_description_translations' => $summary,
        ];
    }
}
