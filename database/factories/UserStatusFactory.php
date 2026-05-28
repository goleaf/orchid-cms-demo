<?php

namespace Database\Factories;

use App\Models\UserStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserStatus>
 */
class UserStatusFactory extends Factory
{
    protected $model = UserStatus::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $code = $this->faker->unique()->slug(2);
        $name = str($code)->replace('-', ' ')->title()->toString();

        return [
            'code' => $code,
            'name_translations' => $this->translations($name, $name, $name, $name),
            'description_translations' => null,
            'color' => '#2563eb',
            'sort_order' => 0,
            'is_default' => false,
            'is_active' => true,
            'is_blocked' => false,
            'is_archived' => false,
            'is_final' => false,
        ];
    }

    public function active(): static
    {
        return $this->dictionaryState(
            'active',
            ['ru' => 'Активен', 'en' => 'Active', 'lt' => 'Aktyvus', 'pl' => 'Aktywny'],
            '#16a34a',
            ['is_default' => true],
        );
    }

    public function inactive(): static
    {
        return $this->dictionaryState(
            'inactive',
            ['ru' => 'Неактивен', 'en' => 'Inactive', 'lt' => 'Neaktyvus', 'pl' => 'Nieaktywny'],
            '#64748b',
        );
    }

    public function blocked(): static
    {
        return $this->dictionaryState(
            'blocked',
            ['ru' => 'Заблокирован', 'en' => 'Blocked', 'lt' => 'Užblokuotas', 'pl' => 'Zablokowany'],
            '#dc2626',
            ['is_blocked' => true, 'is_final' => true],
        );
    }

    public function archived(): static
    {
        return $this->dictionaryState(
            'archived',
            ['ru' => 'Архив', 'en' => 'Archived', 'lt' => 'Archyvas', 'pl' => 'Archiwum'],
            '#475569',
            ['is_archived' => true, 'is_final' => true],
        );
    }

    public function default(): static
    {
        return $this->state(fn (): array => [
            'is_default' => true,
            'is_active' => true,
        ]);
    }

    public function translated(): static
    {
        return $this->dictionaryState(
            'translated_user_status',
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
            'name_translations' => $translations,
            'description_translations' => $translations,
            'color' => $color,
            'sort_order' => 0,
            'is_default' => false,
            'is_active' => true,
            'is_blocked' => false,
            'is_archived' => false,
            'is_final' => false,
            ...$flags,
        ]);
    }

    /**
     * @return array<string, string>
     */
    private function translations(string $ru, string $en, string $lt, string $pl): array
    {
        return [
            'ru' => $ru,
            'en' => $en,
            'lt' => $lt,
            'pl' => $pl,
        ];
    }
}
