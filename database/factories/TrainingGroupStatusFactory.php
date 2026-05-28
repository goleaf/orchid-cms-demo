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
            'is_open_for_enrollment' => false,
            'is_in_progress' => false,
            'is_final' => false,
            'is_success' => false,
            'is_cancelled' => false,
            'is_archived' => false,
        ];
    }

    public function draft(): static
    {
        return $this->dictionaryState('draft', $this->translations('Черновик', 'Draft', 'Juodrastis', 'Szkic'), '#64748b', ['is_default' => true]);
    }

    public function planned(): static
    {
        return $this->dictionaryState('planned', $this->translations('Запланирована', 'Planned', 'Suplanuota', 'Zaplanowana'), '#64748b', ['is_public' => true, 'accepts_enrollments' => true]);
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

    public function full(): static
    {
        return $this->dictionaryState('full', $this->translations('Заполнена', 'Full', 'Pilna', 'Pelna'), '#dc2626', ['is_public' => true]);
    }

    public function scheduled(): static
    {
        return $this->dictionaryState('scheduled', $this->translations('Запланирована', 'Scheduled', 'Suplanuota', 'Zaplanowana'), '#64748b', ['is_public' => true]);
    }

    public function active(): static
    {
        return $this->dictionaryState('active', $this->translations('Идет обучение', 'Active', 'Vyksta mokymai', 'Aktywna'), '#2563eb', ['is_in_progress' => true]);
    }

    public function inProgress(): static
    {
        return $this->dictionaryState('in_progress', $this->translations('В процессе', 'In progress', 'Vyksta', 'W trakcie'), '#2563eb', ['is_in_progress' => true]);
    }

    public function completed(): static
    {
        return $this->dictionaryState('completed', $this->translations('Завершена', 'Completed', 'Baigta', 'Ukonczona'), '#475569', ['is_final' => true, 'is_success' => true]);
    }

    public function finished(): static
    {
        return $this->dictionaryState('finished', $this->translations('Окончена', 'Finished', 'Baigta', 'Zakonczona'), '#475569', ['is_final' => true, 'is_success' => true]);
    }

    public function paused(): static
    {
        return $this->dictionaryState('paused', $this->translations('Приостановлена', 'Paused', 'Pristabdyta', 'Wstrzymana'), '#9333ea', ['is_in_progress' => true]);
    }

    public function cancelled(): static
    {
        return $this->dictionaryState('cancelled', $this->translations('Отменена', 'Cancelled', 'Atsaukta', 'Anulowana'), '#dc2626', ['is_final' => true, 'is_cancelled' => true]);
    }

    public function closed(): static
    {
        return $this->dictionaryState('closed', $this->translations('Закрыта', 'Closed', 'Uzdaryta', 'Zamknieta'), '#64748b', ['is_final' => true]);
    }

    public function archived(): static
    {
        return $this->dictionaryState('archived', $this->translations('Архив', 'Archived', 'Archyvas', 'Archiwum'), '#334155', ['is_final' => true, 'is_archived' => true]);
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
        $flags['is_open_for_enrollment'] = (bool) ($flags['is_open_for_enrollment'] ?? $flags['accepts_enrollments'] ?? false);
        $flags['accepts_enrollments'] = $flags['is_open_for_enrollment'];

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
            'is_open_for_enrollment' => false,
            'is_in_progress' => false,
            'is_final' => false,
            'is_success' => false,
            'is_cancelled' => false,
            'is_archived' => false,
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
