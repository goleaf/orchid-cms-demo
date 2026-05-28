<?php

namespace Database\Factories;

use App\Models\StudentStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentStatus>
 */
class StudentStatusFactory extends Factory
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
            'is_blocked' => false,
            'is_archived' => false,
        ];
    }

    public function active(): static
    {
        return $this->dictionaryState(
            'active',
            ['ru' => 'Активный', 'en' => 'Active', 'lt' => 'Aktyvus', 'pl' => 'Aktywny'],
            '#16a34a',
            ['is_default' => true],
        );
    }

    public function inactive(): static
    {
        return $this->dictionaryState(
            'inactive',
            ['ru' => 'Неактивный', 'en' => 'Inactive', 'lt' => 'Neaktyvus', 'pl' => 'Nieaktywny'],
            '#64748b',
        );
    }

    public function blocked(): static
    {
        return $this->dictionaryState(
            'blocked',
            ['ru' => 'Заблокирован', 'en' => 'Blocked', 'lt' => 'Užblokuotas', 'pl' => 'Zablokowany'],
            '#dc2626',
            ['is_blocked' => true],
        );
    }

    public function lead(): static
    {
        return $this->dictionaryState(
            'lead',
            ['ru' => 'Лид', 'en' => 'Lead', 'lt' => 'Užklausa', 'pl' => 'Lead'],
            '#2563eb',
        );
    }

    public function enrolled(): static
    {
        return $this->dictionaryState(
            'enrolled',
            ['ru' => 'Записан', 'en' => 'Enrolled', 'lt' => 'Užregistruotas', 'pl' => 'Zapisany'],
            '#15803d',
        );
    }

    public function graduated(): static
    {
        return $this->dictionaryState(
            'graduated',
            ['ru' => 'Выпускник', 'en' => 'Graduated', 'lt' => 'Baigęs', 'pl' => 'Absolwent'],
            '#0f766e',
            ['is_final' => true],
        );
    }

    public function archived(): static
    {
        return $this->dictionaryState(
            'archived',
            ['ru' => 'Архив', 'en' => 'Archived', 'lt' => 'Archyvas', 'pl' => 'Archiwum'],
            '#475569',
            ['is_final' => true, 'is_archived' => true],
        );
    }

    public function default(): static
    {
        return $this->state(fn (): array => ['is_default' => true]);
    }

    public function translated(): static
    {
        return $this->dictionaryState(
            'translated_student_status',
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
            'is_blocked' => false,
            'is_archived' => false,
            ...$flags,
        ]);
    }
}
