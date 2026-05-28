<?php

namespace Database\Factories;

use App\Models\LeadStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LeadStatus>
 */
class LeadStatusFactory extends Factory
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
            'is_public' => false,
            'is_default' => false,
            'is_final' => false,
            'is_success' => false,
            'is_lost' => false,
            'is_duplicate' => false,
            'is_spam' => false,
            'sort_order' => 0,
        ];
    }

    public function newStatus(): static
    {
        return $this->dictionaryState(
            'new',
            ['ru' => 'Новая заявка', 'en' => 'New lead', 'lt' => 'Nauja uzklausa', 'pl' => 'Nowy lead'],
            '#2563eb',
            ['is_default' => true],
        );
    }

    public function noAnswer(): static
    {
        return $this->dictionaryState(
            'no_answer',
            ['ru' => 'Не дозвонились', 'en' => 'No answer', 'lt' => 'Neatsiliepe', 'pl' => 'Brak odpowiedzi'],
            '#f97316',
        );
    }

    public function contacted(): static
    {
        return $this->dictionaryState(
            'contacted',
            ['ru' => 'Связались', 'en' => 'Contacted', 'lt' => 'Susisiekta', 'pl' => 'Skontaktowano'],
            '#0891b2',
        );
    }

    public function consultation(): static
    {
        return $this->dictionaryState(
            'consultation',
            ['ru' => 'Консультация', 'en' => 'Consultation', 'lt' => 'Konsultacija', 'pl' => 'Konsultacja'],
            '#7c3aed',
        );
    }

    public function waitingDocuments(): static
    {
        return $this->dictionaryState(
            'waiting_documents',
            ['ru' => 'Ждёт документы', 'en' => 'Waiting for documents', 'lt' => 'Laukiama dokumentu', 'pl' => 'Oczekuje na dokumenty'],
            '#ca8a04',
        );
    }

    public function waitingPayment(): static
    {
        return $this->dictionaryState(
            'waiting_payment',
            ['ru' => 'Ждёт оплату', 'en' => 'Waiting for payment', 'lt' => 'Laukiama apmokejimo', 'pl' => 'Oczekuje na platnosc'],
            '#ea580c',
        );
    }

    public function readyToEnroll(): static
    {
        return $this->dictionaryState(
            'ready_to_enroll',
            ['ru' => 'Готов к записи', 'en' => 'Ready to enroll', 'lt' => 'Pasiruoses registracijai', 'pl' => 'Gotowy do zapisu'],
            '#16a34a',
        );
    }

    public function enrolled(): static
    {
        return $this->dictionaryState(
            'enrolled',
            ['ru' => 'Записан', 'en' => 'Enrolled', 'lt' => 'Uzregistruotas', 'pl' => 'Zapisany'],
            '#15803d',
            ['is_final' => true, 'is_success' => true],
        );
    }

    public function lost(): static
    {
        return $this->dictionaryState(
            'lost',
            ['ru' => 'Потерян', 'en' => 'Lost', 'lt' => 'Prarasta', 'pl' => 'Utracony'],
            '#dc2626',
            ['is_final' => true, 'is_lost' => true],
        );
    }

    public function duplicate(): static
    {
        return $this->dictionaryState(
            'duplicate',
            ['ru' => 'Дубль', 'en' => 'Duplicate', 'lt' => 'Dublikatas', 'pl' => 'Duplikat'],
            '#64748b',
            ['is_final' => true, 'is_lost' => true, 'is_duplicate' => true],
        );
    }

    public function spam(): static
    {
        return $this->dictionaryState(
            'spam',
            ['ru' => 'Спам', 'en' => 'Spam', 'lt' => 'Slamstas', 'pl' => 'Niechciane'],
            '#991b1b',
            ['is_final' => true, 'is_lost' => true, 'is_spam' => true],
        );
    }

    public function archived(): static
    {
        return $this->dictionaryState(
            'archived',
            ['ru' => 'Архив', 'en' => 'Archived', 'lt' => 'Archyvas', 'pl' => 'Archiwum'],
            '#475569',
            ['is_final' => true],
        );
    }

    public function default(): static
    {
        return $this->state(fn (): array => ['is_default' => true]);
    }

    public function active(): static
    {
        return $this->state(fn (): array => ['is_active' => true]);
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }

    public function final(): static
    {
        return $this->state(fn (): array => ['is_final' => true]);
    }

    public function success(): static
    {
        return $this->state(fn (): array => [
            'is_final' => true,
            'is_success' => true,
        ]);
    }

    public function lostStatus(): static
    {
        return $this->state(fn (): array => [
            'is_final' => true,
            'is_lost' => true,
        ]);
    }

    public function duplicateStatus(): static
    {
        return $this->state(fn (): array => [
            'is_final' => true,
            'is_lost' => true,
            'is_duplicate' => true,
        ]);
    }

    public function spamStatus(): static
    {
        return $this->state(fn (): array => [
            'is_final' => true,
            'is_lost' => true,
            'is_spam' => true,
        ]);
    }

    public function translated(): static
    {
        return $this->dictionaryState(
            'translated_status',
            ['ru' => 'Переведенный статус', 'en' => 'Translated status', 'lt' => 'Isversta busena', 'pl' => 'Przetlumaczony status'],
            '#2563eb',
        );
    }

    /**
     * @param  array<string, string>  $translations
     * @param  array<string, mixed>  $flags
     */
    private function dictionaryState(string $code, array $translations, string $color, array $flags = []): static
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
            'is_public' => false,
            'is_default' => false,
            'is_final' => false,
            'is_success' => false,
            'is_lost' => false,
            'is_duplicate' => false,
            'is_spam' => false,
            ...$flags,
        ]);
    }
}
