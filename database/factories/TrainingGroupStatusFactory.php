<?php

namespace Database\Factories;

use App\Models\TrainingGroupStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TrainingGroupStatus>
 */
class TrainingGroupStatusFactory extends Factory
{
    protected $model = TrainingGroupStatus::class;

    public function definition(): array
    {
        $code = $this->faker->unique()->slug(2);
        $name = str($code)->replace('-', ' ')->title()->toString();

        return [
            'code' => $code,
            'name' => $name,
            'name_translations' => $this->translations($name, $name, $name, $name),
            'description_translations' => null,
            'color' => '#2563eb',
            'sort_order' => 0,
            'is_system' => false,
            'is_default' => false,
            'is_active' => true,
            'is_public' => false,
            'accepts_enrollments' => false,
            'is_in_progress' => false,
            'is_final' => false,
            'is_success' => false,
            'is_cancelled' => false,
        ];
    }

    public function planned(): static
    {
        return $this->dictionaryState('planned', $this->translations('Запланирована', 'Planned', 'Suplanuota', 'Zaplanowana'), '#64748b', ['is_default' => true, 'is_public' => true, 'accepts_enrollments' => true]);
    }

    public function recruiting(): static
    {
        return $this->dictionaryState('recruiting', $this->translations('Набор открыт', 'Recruiting', 'Registracija atidaryta', 'Nabor otwarty'), '#16a34a', ['is_public' => true, 'accepts_enrollments' => true]);
    }

    public function open(): static
    {
        return $this->dictionaryState('open', $this->translations('Открыта', 'Open', 'Atvira', 'Otwarta'), '#0f766e', ['is_public' => true, 'accepts_enrollments' => true]);
    }

    public function almostFull(): static
    {
        return $this->dictionaryState('almost_full', $this->translations('Почти заполнена', 'Almost full', 'Beveik pilna', 'Prawie pelna'), '#f59e0b', ['is_public' => true, 'accepts_enrollments' => true]);
    }

    public function active(): static
    {
        return $this->dictionaryState('active', $this->translations('Идет обучение', 'Active', 'Vyksta mokymai', 'Aktywna'), '#2563eb', ['is_in_progress' => true]);
    }

    public function completed(): static
    {
        return $this->dictionaryState('completed', $this->translations('Завершена', 'Completed', 'Baigta', 'Ukonczona'), '#475569', ['is_final' => true, 'is_success' => true]);
    }

    public function cancelled(): static
    {
        return $this->dictionaryState('cancelled', $this->translations('Отменена', 'Cancelled', 'Atsaukta', 'Anulowana'), '#dc2626', ['is_final' => true, 'is_cancelled' => true]);
    }

    public function closed(): static
    {
        return $this->dictionaryState('closed', $this->translations('Закрыта', 'Closed', 'Uzdaryta', 'Zamknieta'), '#64748b', ['is_final' => true]);
    }

    public function translated(): static
    {
        return $this->recruiting();
    }

    /**
     * @param  array<string, string>  $translations
     * @param  array<string, mixed>  $flags
     */
    private function dictionaryState(string $code, array $translations, string $color, array $flags = []): static
    {
        return $this->state(fn (): array => [
            'code' => $code,
            'name' => $translations['en'],
            'name_translations' => $translations,
            'description_translations' => $translations,
            'color' => $color,
            'sort_order' => 0,
            'is_system' => true,
            'is_default' => false,
            'is_active' => true,
            'is_public' => false,
            'accepts_enrollments' => false,
            'is_in_progress' => false,
            'is_final' => false,
            'is_success' => false,
            'is_cancelled' => false,
            ...$flags,
        ]);
    }

    /**
     * @return array<string, string>
     */
    private function translations(string $ru, string $en, string $lt, string $pl): array
    {
        return compact('ru', 'en', 'lt', 'pl');
    }
}
