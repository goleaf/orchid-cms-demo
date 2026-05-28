<?php

namespace Database\Factories;

use App\Models\EnrollmentStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EnrollmentStatus>
 */
class EnrollmentStatusFactory extends Factory
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
            'sort_order' => 0,
            'is_system' => false,
            'is_default' => false,
            'is_active' => true,
            'is_final' => false,
            'is_success' => false,
            'is_cancelled' => false,
            'is_waiting_documents' => false,
            'is_waiting_payment' => false,
            'is_in_progress' => false,
        ];
    }

    public function draft(): static
    {
        return $this->dictionaryState('draft', ['ru' => 'Черновик', 'en' => 'Draft', 'lt' => 'Juodraštis', 'pl' => 'Szkic'], '#64748b');
    }

    public function waitingDocuments(): static
    {
        return $this->dictionaryState(
            'waiting_documents',
            ['ru' => 'Ждёт документы', 'en' => 'Waiting for documents', 'lt' => 'Laukiama dokumentų', 'pl' => 'Oczekuje na dokumenty'],
            '#ca8a04',
            ['is_default' => true, 'is_waiting_documents' => true],
        );
    }

    public function waitingPayment(): static
    {
        return $this->dictionaryState(
            'waiting_payment',
            ['ru' => 'Ждёт оплату', 'en' => 'Waiting for payment', 'lt' => 'Laukiama apmokėjimo', 'pl' => 'Oczekuje na płatność'],
            '#ea580c',
            ['is_waiting_payment' => true],
        );
    }

    public function waitingStart(): static
    {
        return $this->dictionaryState('waiting_start', ['ru' => 'Ждёт старта', 'en' => 'Waiting for start', 'lt' => 'Laukiama pradžios', 'pl' => 'Oczekuje na start'], '#0891b2');
    }

    public function active(): static
    {
        return $this->dictionaryState('active', ['ru' => 'Активное обучение', 'en' => 'Active training', 'lt' => 'Aktyvus mokymas', 'pl' => 'Aktywne szkolenie'], '#16a34a', ['is_in_progress' => true]);
    }

    public function theory(): static
    {
        return $this->dictionaryState('theory', ['ru' => 'Теория', 'en' => 'Theory', 'lt' => 'Teorija', 'pl' => 'Teoria'], '#2563eb', ['is_in_progress' => true]);
    }

    public function practice(): static
    {
        return $this->dictionaryState('practice', ['ru' => 'Практика', 'en' => 'Practice', 'lt' => 'Praktika', 'pl' => 'Praktyka'], '#7c3aed', ['is_in_progress' => true]);
    }

    public function readyInternalExam(): static
    {
        return $this->dictionaryState('ready_internal_exam', ['ru' => 'Готов к внутреннему экзамену', 'en' => 'Ready for internal exam', 'lt' => 'Pasiruošęs vidiniam egzaminui', 'pl' => 'Gotowy do egzaminu wewnętrznego'], '#0f766e');
    }

    public function readyStateExam(): static
    {
        return $this->dictionaryState('ready_state_exam', ['ru' => 'Готов к госэкзамену', 'en' => 'Ready for state exam', 'lt' => 'Pasiruošęs valstybiniam egzaminui', 'pl' => 'Gotowy do egzaminu państwowego'], '#0f766e');
    }

    public function completed(): static
    {
        return $this->dictionaryState('completed', ['ru' => 'Завершено', 'en' => 'Completed', 'lt' => 'Baigta', 'pl' => 'Ukończone'], '#15803d', ['is_final' => true, 'is_success' => true]);
    }

    public function paused(): static
    {
        return $this->dictionaryState('paused', ['ru' => 'Пауза', 'en' => 'Paused', 'lt' => 'Pauzė', 'pl' => 'Wstrzymane'], '#f97316');
    }

    public function cancelled(): static
    {
        return $this->dictionaryState('cancelled', ['ru' => 'Отменено', 'en' => 'Cancelled', 'lt' => 'Atšaukta', 'pl' => 'Anulowane'], '#dc2626', ['is_final' => true, 'is_cancelled' => true]);
    }

    public function expelled(): static
    {
        return $this->dictionaryState('expelled', ['ru' => 'Исключён', 'en' => 'Expelled', 'lt' => 'Pašalintas', 'pl' => 'Wydalony'], '#991b1b', ['is_final' => true, 'is_cancelled' => true]);
    }

    public function archived(): static
    {
        return $this->dictionaryState('archived', ['ru' => 'Архив', 'en' => 'Archived', 'lt' => 'Archyvas', 'pl' => 'Archiwum'], '#475569', ['is_final' => true]);
    }

    public function default(): static
    {
        return $this->state(fn (): array => ['is_default' => true]);
    }

    public function translated(): static
    {
        return $this->dictionaryState(
            'translated_enrollment_status',
            ['ru' => 'Переведенный статус', 'en' => 'Translated status', 'lt' => 'Išversta būsena', 'pl' => 'Przetłumaczony status'],
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
            'description_translations' => $translations,
            'color' => $color,
            'sort_order' => 0,
            'is_system' => true,
            'is_default' => false,
            'is_active' => true,
            'is_final' => false,
            'is_success' => false,
            'is_cancelled' => false,
            'is_waiting_documents' => false,
            'is_waiting_payment' => false,
            'is_in_progress' => false,
            ...$flags,
        ]);
    }
}
