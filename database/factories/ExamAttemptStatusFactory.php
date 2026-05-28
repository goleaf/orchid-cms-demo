<?php

namespace Database\Factories;

use App\Models\ExamAttemptStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ExamAttemptStatus>
 */
class ExamAttemptStatusFactory extends Factory
{
    public function definition(): array
    {
        $code = $this->faker->unique()->slug(2);
        $name = str($code)->replace('-', ' ')->title()->toString();

        return $this->attributes($code, $name, '#2563eb');
    }

    public function planned(): static
    {
        return $this->state(fn (): array => $this->attributes('planned', 'Planned', '#64748b'));
    }

    public function allowed(): static
    {
        return $this->state(fn (): array => $this->attributes('allowed', 'Allowed', '#16a34a'));
    }

    public function blocked(): static
    {
        return $this->state(fn (): array => $this->attributes('blocked', 'Blocked', '#dc2626'));
    }

    public function inProgress(): static
    {
        return $this->state(fn (): array => $this->attributes('in_progress', 'In progress', '#0f766e'));
    }

    public function passed(): static
    {
        return $this->state(fn (): array => $this->attributes('passed', 'Passed', '#16a34a'));
    }

    public function failed(): static
    {
        return $this->state(fn (): array => $this->attributes('failed', 'Failed', '#dc2626'));
    }

    public function noShow(): static
    {
        return $this->state(fn (): array => $this->attributes('no_show', 'No show', '#f97316'));
    }

    public function cancelled(): static
    {
        return $this->state(fn (): array => $this->attributes('cancelled', 'Cancelled', '#64748b'));
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
            'name_translations' => ['ru' => $name, 'en' => $name, 'lt' => $name, 'pl' => $name],
            'description_translations' => ['ru' => $name, 'en' => $name, 'lt' => $name, 'pl' => $name],
            'color' => $color,
            'sort_order' => 0,
            'is_system' => true,
            'is_active' => true,
        ];
    }
}
