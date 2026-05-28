<?php

namespace App\Actions\Analytics;

use App\Actions\Analytics\Concerns\ReadsAnalyticsDataSafely;
use App\Enums\AnalyticsDashboardAudience;
use App\Models\AnalyticsDashboard;
use App\Models\DashboardWidget;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class GenerateOwnerDashboardAction
{
    use ReadsAnalyticsDataSafely;

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function handle(array $filters = [], ?User $user = null): array
    {
        $this->authorizeAnalyticsAccess($user, 'analytics.dashboard.view');

        $summary = app(CalculateDashboardSummaryAction::class)->handle($filters);
        $dashboard = $this->ownerDashboard();
        $widgetData = $dashboard instanceof AnalyticsDashboard
            ? $this->widgetData($dashboard, $filters)
            : [];

        $payload = [
            'dashboard' => $dashboard,
            'summary' => $summary,
            'widgets' => $widgetData,
            'generated_at' => now()->toISOString(),
        ];

        app(RefreshAnalyticsCacheAction::class)->handle(
            'owner_dashboard.summary.'.sha1(json_encode($summary['filters'], JSON_THROW_ON_ERROR)),
            [
                'summary' => $summary,
                'widgets' => $widgetData,
            ],
            'dashboard',
            15,
            ['analytics', 'dashboard', 'owner'],
            $user,
        );

        return $payload;
    }

    private function ownerDashboard(): ?AnalyticsDashboard
    {
        if (! $this->analyticsTableExists(AnalyticsDashboard::class)) {
            return null;
        }

        return AnalyticsDashboard::query()
            ->active()
            ->forAudience(AnalyticsDashboardAudience::Owner)
            ->with(['widgets' => fn (Builder $query): Builder => $query->active()->ordered()])
            ->default()
            ->ordered()
            ->first()
            ?: AnalyticsDashboard::query()
                ->active()
                ->forAudience(AnalyticsDashboardAudience::Owner)
                ->with(['widgets' => fn (Builder $query): Builder => $query->active()->ordered()])
                ->ordered()
                ->first();
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<int, array<string, mixed>>
     */
    private function widgetData(AnalyticsDashboard $dashboard, array $filters): array
    {
        if (! $this->analyticsTableExists(DashboardWidget::class)) {
            return [];
        }

        return $dashboard->widgets
            ->map(fn (DashboardWidget $widget): array => app(GenerateDashboardWidgetDataAction::class)->handle($widget, $filters))
            ->values()
            ->all();
    }
}
