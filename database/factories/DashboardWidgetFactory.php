<?php

namespace Database\Factories;

use App\Models\DashboardWidget;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DashboardWidget>
 */
class DashboardWidgetFactory extends Factory
{
    protected $model = DashboardWidget::class;

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
            'description_translations' => $this->translations($this->faker->sentence(8)),
            'widget_type' => 'metric',
            'metric_code' => str($code)->replace('-', '_')->toString(),
            'component' => null,
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
