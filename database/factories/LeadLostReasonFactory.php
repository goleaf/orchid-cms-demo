<?php

namespace Database\Factories;

use App\Models\LeadLostReason;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LeadLostReason>
 */
class LeadLostReasonFactory extends Factory
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
            'color' => '#dc2626',
            'is_system' => false,
            'is_active' => true,
            'sort_order' => 0,
        ];
    }

    public function price(): static
    {
        return $this->dictionaryState('price', ['ru' => 'Цена', 'en' => 'Price', 'lt' => 'Kaina', 'pl' => 'Cena']);
    }

    public function schedule(): static
    {
        return $this->dictionaryState('schedule', ['ru' => 'Расписание', 'en' => 'Schedule', 'lt' => 'Tvarkarastis', 'pl' => 'Termin']);
    }

    public function location(): static
    {
        return $this->dictionaryState('location', ['ru' => 'Локация', 'en' => 'Location', 'lt' => 'Vieta', 'pl' => 'Lokalizacja']);
    }

    public function competitor(): static
    {
        return $this->dictionaryState('competitor', ['ru' => 'Конкурент', 'en' => 'Competitor', 'lt' => 'Konkurentas', 'pl' => 'Konkurencja']);
    }

    public function noResponse(): static
    {
        return $this->dictionaryState('no_response', ['ru' => 'Не отвечает', 'en' => 'No response', 'lt' => 'Neatsako', 'pl' => 'Brak odpowiedzi']);
    }

    public function changedMind(): static
    {
        return $this->dictionaryState('changed_mind', ['ru' => 'Передумал', 'en' => 'Changed mind', 'lt' => 'Persigalvojo', 'pl' => 'Zmienil zdanie']);
    }

    public function documents(): static
    {
        return $this->dictionaryState('documents', ['ru' => 'Документы', 'en' => 'Documents', 'lt' => 'Dokumentai', 'pl' => 'Dokumenty']);
    }

    public function payment(): static
    {
        return $this->dictionaryState('payment', ['ru' => 'Оплата', 'en' => 'Payment', 'lt' => 'Mokejimas', 'pl' => 'Platnosc']);
    }

    public function language(): static
    {
        return $this->dictionaryState('language', ['ru' => 'Язык', 'en' => 'Language', 'lt' => 'Kalba', 'pl' => 'Jezyk']);
    }

    public function carType(): static
    {
        return $this->dictionaryState('car_type', ['ru' => 'Тип авто', 'en' => 'Car type', 'lt' => 'Automobilio tipas', 'pl' => 'Typ auta']);
    }

    public function duplicate(): static
    {
        return $this->dictionaryState('duplicate', ['ru' => 'Дубликат', 'en' => 'Duplicate', 'lt' => 'Dublikatas', 'pl' => 'Duplikat']);
    }

    public function spam(): static
    {
        return $this->dictionaryState('spam', ['ru' => 'Спам', 'en' => 'Spam', 'lt' => 'Slamstas', 'pl' => 'Niechciane']);
    }

    public function other(): static
    {
        return $this->dictionaryState('other', ['ru' => 'Другое', 'en' => 'Other', 'lt' => 'Kita', 'pl' => 'Inne']);
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
        return $this->dictionaryState('translated_reason', ['ru' => 'Переведенная причина', 'en' => 'Translated reason', 'lt' => 'Isversta priezastis', 'pl' => 'Przetlumaczony powod']);
    }

    /**
     * @param  array<string, string>  $translations
     */
    private function dictionaryState(string $code, array $translations, string $color = '#dc2626'): static
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
