<?php

namespace Database\Factories;

use App\Models\ExamStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ExamStatus>
 */
class ExamStatusFactory extends Factory
{
    public function definition(): array
    {
        $code = $this->faker->unique()->slug(2);
        $name = str($code)->replace('-', ' ')->title()->toString();

        return $this->attributes($code, $name, '#2563eb');
    }

    public function draft(): static
    {
        return $this->state(fn (): array => $this->attributes('draft', 'Draft', '#64748b'));
    }

    public function scheduled(): static
    {
        return $this->state(fn (): array => $this->attributes('scheduled', 'Scheduled', '#2563eb'));
    }

    public function open(): static
    {
        return $this->state(fn (): array => $this->attributes('open', 'Open', '#16a34a'));
    }

    public function inProgress(): static
    {
        return $this->state(fn (): array => $this->attributes('in_progress', 'In progress', '#0f766e'));
    }

    public function completed(): static
    {
        return $this->state(fn (): array => $this->attributes('completed', 'Completed', '#475569'));
    }

    public function cancelled(): static
    {
        return $this->state(fn (): array => $this->attributes('cancelled', 'Cancelled', '#dc2626'));
    }

    public function archived(): static
    {
        return $this->state(fn (): array => $this->attributes('archived', 'Archived', '#334155'));
    }

    /**
     * @return array<string, mixed>
     */
    private function attributes(string $code, string $name, string $color): array
    {
        return [
            'code' => $code,
            'name' => $name,
            'name_translations' => $this->translations($name),
            'description_translations' => $this->translations($name.' status'),
            'color' => $color,
            'sort_order' => 0,
            'is_system' => true,
            'is_active' => true,
        ];
    }

    /**
     * @return array<string, string>
     */
    private function translations(string $value): array
    {
        return ['ru' => $value, 'en' => $value, 'lt' => $value, 'pl' => $value];
    }
}
