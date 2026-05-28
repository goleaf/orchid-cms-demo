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
        return $this->attributes('pending', 'Pending', '#64748b');
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
