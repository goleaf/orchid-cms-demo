<?php

namespace Database\Factories;

use App\Models\LeadSource;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LeadSource>
 */
class LeadSourceFactory extends Factory
{
    public function definition(): array
    {
        $code = $this->faker->unique()->slug(2);
        $name = str($code)->replace('-', ' ')->title()->toString();

        return [
            'code' => $code,
            'name' => $name,
            'name_translations' => [
                'ru' => $name,
                'en' => $name,
                'lt' => $name,
                'pl' => $name,
            ],
            'description_translations' => null,
            'color' => '#2563eb',
            'is_system' => false,
            'is_active' => true,
            'sort_order' => 0,
        ];
    }

    public function website(): static
    {
        return $this->dictionaryState('website', ['ru' => 'Сайт', 'en' => 'Website', 'lt' => 'Svetaine', 'pl' => 'Strona'], '#2563eb');
    }

    public function callback(): static
    {
        return $this->dictionaryState('callback', ['ru' => 'Обратный звонок', 'en' => 'Callback', 'lt' => 'Perskambinimas', 'pl' => 'Oddzwonienie'], '#0891b2');
    }

    public function contactForm(): static
    {
        return $this->dictionaryState('contact_form', ['ru' => 'Контактная форма', 'en' => 'Contact form', 'lt' => 'Kontaktine forma', 'pl' => 'Formularz kontaktowy'], '#0d9488');
    }

    public function phone(): static
    {
        return $this->dictionaryState('phone', ['ru' => 'Телефон', 'en' => 'Phone', 'lt' => 'Telefonas', 'pl' => 'Telefon'], '#16a34a');
    }

    public function office(): static
    {
        return $this->dictionaryState('office', ['ru' => 'Офис', 'en' => 'Office', 'lt' => 'Biuras', 'pl' => 'Biuro'], '#4f46e5');
    }

    public function googleAds(): static
    {
        return $this->dictionaryState('google_ads', ['ru' => 'Google Ads', 'en' => 'Google Ads', 'lt' => 'Google Ads', 'pl' => 'Google Ads'], '#ea4335');
    }

    public function facebook(): static
    {
        return $this->dictionaryState('facebook', ['ru' => 'Facebook', 'en' => 'Facebook', 'lt' => 'Facebook', 'pl' => 'Facebook'], '#1877f2');
    }

    public function instagram(): static
    {
        return $this->dictionaryState('instagram', ['ru' => 'Instagram', 'en' => 'Instagram', 'lt' => 'Instagram', 'pl' => 'Instagram'], '#c13584');
    }

    public function tiktok(): static
    {
        return $this->dictionaryState('tiktok', ['ru' => 'TikTok', 'en' => 'TikTok', 'lt' => 'TikTok', 'pl' => 'TikTok'], '#111827');
    }

    public function telegram(): static
    {
        return $this->dictionaryState('telegram', ['ru' => 'Telegram', 'en' => 'Telegram', 'lt' => 'Telegram', 'pl' => 'Telegram'], '#229ed9');
    }

    public function whatsapp(): static
    {
        return $this->dictionaryState('whatsapp', ['ru' => 'WhatsApp', 'en' => 'WhatsApp', 'lt' => 'WhatsApp', 'pl' => 'WhatsApp'], '#22c55e');
    }

    public function referral(): static
    {
        return $this->dictionaryState('referral', ['ru' => 'Рекомендация', 'en' => 'Referral', 'lt' => 'Rekomendacija', 'pl' => 'Polecenie'], '#a855f7');
    }

    public function partner(): static
    {
        return $this->dictionaryState('partner', ['ru' => 'Партнёр', 'en' => 'Partner', 'lt' => 'Partneris', 'pl' => 'Partner'], '#9333ea');
    }

    public function other(): static
    {
        return $this->dictionaryState('other', ['ru' => 'Другое', 'en' => 'Other', 'lt' => 'Kita', 'pl' => 'Inne'], '#64748b');
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
        return $this->dictionaryState('translated_source', ['ru' => 'Переведенный источник', 'en' => 'Translated source', 'lt' => 'Isverstas saltinis', 'pl' => 'Przetlumaczone zrodlo'], '#2563eb');
    }

    /**
     * @param  array<string, string>  $translations
     */
    private function dictionaryState(string $code, array $translations, string $color): static
    {
        return $this->state(fn (): array => [
            'code' => $code,
            'name' => $translations['ru'],
            'name_translations' => $translations,
            'description_translations' => [
                'ru' => $translations['ru'],
                'en' => $translations['en'],
                'lt' => $translations['lt'],
                'pl' => $translations['pl'],
            ],
            'color' => $color,
            'is_system' => true,
            'is_active' => true,
        ]);
    }
}
