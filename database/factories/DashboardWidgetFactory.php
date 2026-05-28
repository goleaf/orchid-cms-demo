<?php

namespace Database\Factories;

use App\Enums\DashboardWidgetType;
use App\Models\AnalyticsDashboard;
use App\Models\DashboardWidget;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

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
        $metricCode = str($code)->replace('-', '_')->toString();

        return [
            'uuid' => (string) Str::uuid(),
            'analytics_dashboard_id' => AnalyticsDashboard::factory(),
            'code' => $code,
            'title_translations' => $this->translations($name),
            'name_translations' => $this->translations($name),
            'description_translations' => $this->translations($this->faker->sentence(8)),
            'widget_type' => DashboardWidgetType::Counter->value,
            'metric_code' => $metricCode,
            'component' => null,
            'config' => ['metric' => $metricCode],
            'filters' => [],
            'width' => 3,
            'height' => 1,
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

    public function forDashboard(AnalyticsDashboard $dashboard): static
    {
        return $this->state(fn (): array => [
            'analytics_dashboard_id' => $dashboard->id,
        ]);
    }

    public function type(DashboardWidgetType|string $type): static
    {
        $value = $type instanceof DashboardWidgetType ? $type->value : $type;

        return $this->state(fn (): array => [
            'widget_type' => $value,
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
