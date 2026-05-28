<?php

namespace Database\Factories;

use App\Models\KpiMetric;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<KpiMetric>
 */
class KpiMetricFactory extends Factory
{
    protected $model = KpiMetric::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $code = $this->faker->unique()->slug(3);
        $name = str($code)->replace('-', ' ')->title()->toString();

        return [
            'code' => $code,
            'name_translations' => $this->translations($name),
            'description_translations' => $this->translations($this->faker->sentence(10)),
            'category' => $this->faker->randomElement(['operations', 'crm', 'education', 'finance', 'exams']),
            'value_type' => 'number',
            'unit' => null,
            'calculation' => null,
            'source' => null,
            'is_system' => false,
            'is_active' => true,
            'sort_order' => $this->faker->numberBetween(1, 100),
            'settings' => [],
            'created_by_id' => null,
            'updated_by_id' => null,
        ];
    }

    public function system(): static
    {
        return $this->state(fn (): array => [
            'is_system' => true,
            'created_by_id' => null,
            'updated_by_id' => null,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }

    public function createdBy(User $user): static
    {
        return $this->state(fn (): array => [
            'created_by_id' => $user->id,
            'updated_by_id' => $user->id,
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
