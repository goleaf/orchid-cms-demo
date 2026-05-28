<?php

namespace Database\Factories;

use App\Models\ExamResultStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ExamResultStatus>
 */
class ExamResultStatusFactory extends Factory
{
    public function definition(): array
    {
        $code = $this->faker->unique()->slug(2);
        $name = str($code)->replace('-', ' ')->title()->toString();

        return $this->attributes($code, $name, '#64748b');
    }

    public function pending(): static
    {
        return $this->state(fn (): array => $this->attributes('pending', 'Pending', '#64748b'));
    }

    public function passed(): static
    {
        return $this->state(fn (): array => $this->attributes('passed', 'Passed', '#16a34a'));
    }

    public function failed(): static
    {
        return $this->state(fn (): array => $this->attributes('failed', 'Failed', '#dc2626'));
    }

    public function needsRetake(): static
    {
        return $this->state(fn (): array => $this->attributes('needs_retake', 'Needs retake', '#f97316'));
    }

    public function cancelled(): static
    {
        return $this->state(fn (): array => $this->attributes('cancelled', 'Cancelled', '#64748b'));
    }

    public function translated(): static
    {
        return $this->state(fn (): array => [
            'name' => 'Translated result status',
            'name_translations' => [
                'ru' => 'Переведенный статус результата',
                'en' => 'Translated result status',
                'lt' => 'Isversta rezultato busena',
                'pl' => 'Przetlumaczony status wyniku',
            ],
            'description_translations' => [
                'ru' => 'Переведенное описание статуса результата',
                'en' => 'Translated result status description',
                'lt' => 'Isverstas rezultato busenos aprasymas',
                'pl' => 'Przetlumaczony opis statusu wyniku',
            ],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function attributes(string $code, string $name, string $color): array
    {
        return [
            'code' => $code,
            'name' => $name,
            'name_translations' => ['ru' => $name, 'en' => $name, 'lt' => $name, 'pl' => $name],
            'description_translations' => ['ru' => $name, 'en' => $name, 'lt' => $name, 'pl' => $name],
            'color' => $color,
            'sort_order' => 0,
            'is_system' => true,
            'is_active' => true,
        ];
    }
}
