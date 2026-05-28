<?php

namespace App\Actions\Analytics;

use App\Actions\Analytics\Concerns\ReadsAnalyticsDataSafely;
use App\Models\DashboardWidget;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use InvalidArgumentException;

class GenerateDashboardWidgetDataAction
{
    use ReadsAnalyticsDataSafely;

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function handle(DashboardWidget|string $widget, array $filters = [], ?User $user = null): array
    {
        $this->authorizeAnalyticsAccess($user, 'analytics.dashboard.view');

        $widget = $this->resolveWidget($widget);
        $widgetFilters = array_merge($widget->filters ?? [], $filters);
        $summary = app(CalculateDashboardSummaryAction::class)->handle($widgetFilters);
        $metricCode = $this->metricCodeFor($widget);
        $value = $metricCode !== null ? data_get($summary, 'metrics.'.$metricCode, 0) : null;

        return [
            'widget_id' => $widget->id,
            'code' => $widget->code,
            'widget_type' => $this->analyticsEnumValue($widget->widget_type) ?? 'counter',
            'title' => $widget->displayTitle(),
            'description' => $widget->displayDescription(),
            'metric_code' => $metricCode,
            'value' => $value,
            'data' => [
                'summary' => $summary['metrics'],
                'modules' => $summary['modules'],
            ],
            'filters' => $summary['filters'],
            'calculated_at' => $summary['calculated_at'],
        ];
    }

    private function resolveWidget(DashboardWidget|string $widget): DashboardWidget
    {
        if ($widget instanceof DashboardWidget) {
            return $widget;
        }

        if (! $this->analyticsTableExists(DashboardWidget::class)) {
            throw new InvalidArgumentException(tkey('analytics.validation.invalid_widget'));
        }

        return DashboardWidget::query()
            ->active()
            ->where('code', $widget)
            ->firstOrFail();
    }

    private function metricCodeFor(DashboardWidget $widget): ?string
    {
        foreach ([
            $widget->metric_code,
            data_get($widget->config ?? [], 'metric_code'),
            data_get($widget->config ?? [], 'metric'),
            data_get($widget->settings ?? [], 'metric_code'),
            data_get($widget->settings ?? [], 'metric'),
        ] as $candidate) {
            if (filled($candidate) && is_scalar($candidate)) {
                return (string) $candidate;
            }
        }

        if (filled($widget->code)) {
            return (string) $widget->code;
        }

        throw new ModelNotFoundException;
    }
}
