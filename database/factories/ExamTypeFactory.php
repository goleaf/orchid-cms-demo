<?php

namespace Database\Factories;

use App\Models\ExamType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ExamType>
 */
class ExamTypeFactory extends Factory
{
    public function definition(): array
    {
        $code = $this->faker->unique()->slug(2);
        $name = str($code)->replace('-', ' ')->title()->toString();

        return [
            'code' => $code,
            'name' => $name,
            'name_translations' => $this->translations($name),
            'description_translations' => $this->translations($name.' description'),
            'is_internal' => true,
            'is_official' => false,
            'is_theory' => true,
            'is_practical' => false,
            'is_active' => true,
            'sort_order' => 0,
        ];
    }

    public function internalTheory(): static
    {
        return $this->examTypeState('internal_theory', 'Internal theory exam', true, false, true, false);
    }

    public function internalPractical(): static
    {
        return $this->examTypeState('internal_practical', 'Internal practical exam', true, false, false, true);
    }

    public function officialTheory(): static
    {
        return $this->examTypeState('official_theory_placeholder', 'Official theory placeholder', false, true, true, false);
    }

    public function officialPractical(): static
    {
        return $this->examTypeState('official_practical_placeholder', 'Official practical placeholder', false, true, false, true);
    }

    public function stateTheory(): static
    {
        return $this->examTypeState('state_theory', 'Official theory exam', false, true, true, false);
    }

    public function statePractical(): static
    {
        return $this->examTypeState('state_practical', 'Official practical exam', false, true, false, true);
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
            'name' => 'Translated exam type',
            'name_translations' => [
                'ru' => 'Переведенный тип экзамена',
                'en' => 'Translated exam type',
                'lt' => 'Isverstas egzamino tipas',
                'pl' => 'Przetlumaczony typ egzaminu',
            ],
            'description_translations' => [
                'ru' => 'Переведенное описание типа экзамена',
                'en' => 'Translated exam type description',
                'lt' => 'Isverstas egzamino tipo aprasymas',
                'pl' => 'Przetlumaczony opis typu egzaminu',
            ],
        ]);
    }

    private function examTypeState(string $code, string $name, bool $internal, bool $official, bool $theory, bool $practical): static
    {
        return $this->state(fn (): array => [
            'code' => $code,
            'name' => $name,
            'name_translations' => $this->translations($name),
            'description_translations' => $this->translations($name.' foundation'),
            'is_internal' => $internal,
            'is_official' => $official,
            'is_theory' => $theory,
            'is_practical' => $practical,
            'is_active' => true,
        ]);
    }

    /**
     * @return array<string, string>
     */
    private function translations(string $value): array
    {
        return [
            'ru' => $value,
            'en' => $value,
            'lt' => $value,
            'pl' => $value,
        ];
    }
}
