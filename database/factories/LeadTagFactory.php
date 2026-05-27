<?php

namespace Database\Factories;

use App\Models\LeadTag;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LeadTag>
 */
class LeadTagFactory extends Factory
{
    public function definition(): array
    {
        $slug = $this->faker->unique()->slug(2);
        $name = str($slug)->replace('-', ' ')->title()->toString();

        return [
            'slug' => $slug,
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

    public function hot(): static
    {
        return $this->dictionaryState('hot', ['ru' => 'Горячий', 'en' => 'Hot', 'lt' => 'Karstas', 'pl' => 'Goracy'], '#dc2626');
    }

    public function vip(): static
    {
        return $this->dictionaryState('vip', ['ru' => 'VIP', 'en' => 'VIP', 'lt' => 'VIP', 'pl' => 'VIP'], '#7c3aed');
    }

    public function needsCall(): static
    {
        return $this->dictionaryState('needs_call', ['ru' => 'Нужен звонок', 'en' => 'Needs call', 'lt' => 'Reikia skambucio', 'pl' => 'Wymaga telefonu'], '#0891b2');
    }

    public function readyToPay(): static
    {
        return $this->dictionaryState('ready_to_pay', ['ru' => 'Готов к оплате', 'en' => 'Ready to pay', 'lt' => 'Pasiruoses moketi', 'pl' => 'Gotowy do platnosci'], '#16a34a');
    }

    public function needsDocuments(): static
    {
        return $this->dictionaryState('needs_documents', ['ru' => 'Нужны документы', 'en' => 'Needs documents', 'lt' => 'Reikia dokumentu', 'pl' => 'Wymaga dokumentow'], '#ca8a04');
    }

    public function repeatRequest(): static
    {
        return $this->dictionaryState('repeat_request', ['ru' => 'Повторное обращение', 'en' => 'Repeat request', 'lt' => 'Pakartotine uzklausa', 'pl' => 'Ponowne zgloszenie'], '#64748b');
    }

    public function problematic(): static
    {
        return $this->dictionaryState('problematic', ['ru' => 'Проблемный', 'en' => 'Problematic', 'lt' => 'Probleminis', 'pl' => 'Problemowy'], '#991b1b');
    }

    public function thinking(): static
    {
        return $this->dictionaryState('thinking', ['ru' => 'Думает', 'en' => 'Thinking', 'lt' => 'Svarsto', 'pl' => 'Zastanawia sie'], '#f97316');
    }

    public function urgent(): static
    {
        return $this->dictionaryState('urgent', ['ru' => 'Срочно', 'en' => 'Urgent', 'lt' => 'Skubu', 'pl' => 'Pilne'], '#ef4444');
    }

    public function individualSchedule(): static
    {
        return $this->dictionaryState('individual_schedule', ['ru' => 'Индивидуальный график', 'en' => 'Individual schedule', 'lt' => 'Individualus grafikas', 'pl' => 'Indywidualny grafik'], '#4f46e5');
    }

    public function wantsAutomatic(): static
    {
        return $this->dictionaryState('wants_automatic', ['ru' => 'Хочет автомат', 'en' => 'Wants automatic', 'lt' => 'Nori automato', 'pl' => 'Chce automat'], '#0d9488');
    }

    public function wantsManual(): static
    {
        return $this->dictionaryState('wants_manual', ['ru' => 'Хочет механику', 'en' => 'Wants manual', 'lt' => 'Nori mechanines', 'pl' => 'Chce manual'], '#0f766e');
    }

    public function eveningTraining(): static
    {
        return $this->dictionaryState('evening_training', ['ru' => 'Вечернее обучение', 'en' => 'Evening training', 'lt' => 'Vakarinis mokymas', 'pl' => 'Szkolenie wieczorne'], '#4338ca');
    }

    public function weekendTraining(): static
    {
        return $this->dictionaryState('weekend_training', ['ru' => 'Обучение по выходным', 'en' => 'Weekend training', 'lt' => 'Savaitgalio mokymai', 'pl' => 'Szkolenie weekendowe'], '#6366f1');
    }

    public function corporateClient(): static
    {
        return $this->dictionaryState('corporate_client', ['ru' => 'Корпоративный клиент', 'en' => 'Corporate client', 'lt' => 'Imones klientas', 'pl' => 'Klient firmowy'], '#334155');
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
        return $this->dictionaryState('translated_tag', ['ru' => 'Переведенный тег', 'en' => 'Translated tag', 'lt' => 'Isversta zyma', 'pl' => 'Przetlumaczony tag'], '#2563eb');
    }

    /**
     * @param  array<string, string>  $translations
     */
    private function dictionaryState(string $slug, array $translations, string $color): static
    {
        return $this->state(fn (): array => [
            'slug' => $slug,
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
