<?php

namespace App\Actions\Analytics;

use App\Enums\AnalyticsDashboardAudience;
use App\Models\AnalyticsDashboard;
use App\Models\User;
use App\Models\UserDashboardPreference;

class SaveUserDashboardPreferencesAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(User $user, array $data, AnalyticsDashboard|string|null $dashboard = 'owner'): UserDashboardPreference
    {
        $dashboardModel = $this->resolveDashboard($dashboard, $data);
        $dashboardCode = $dashboardModel?->code ?? (is_string($dashboard) ? $dashboard : AnalyticsDashboardAudience::Owner->value);
        $visibleWidgetCodes = $data['visible_widget_codes'] ?? [];
        $widgetOrder = $data['widget_order'] ?? $visibleWidgetCodes;

        return UserDashboardPreference::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'dashboard' => $dashboardCode,
            ],
            [
                'analytics_dashboard_id' => $dashboardModel?->id,
                'layout' => $data['layout'] ?? $this->legacyLayout($widgetOrder),
                'visible_widget_codes' => $visibleWidgetCodes,
                'widget_order' => $widgetOrder,
                'filters' => $data['filters'] ?? [],
                'refresh_interval_seconds' => (int) ($data['refresh_interval_seconds'] ?? 300),
                'timezone' => $data['timezone'] ?? config('app.timezone'),
                'is_default' => (bool) ($data['is_default'] ?? true),
                'settings' => $data['settings'] ?? [],
            ],
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function resolveDashboard(AnalyticsDashboard|string|null $dashboard, array $data): ?AnalyticsDashboard
    {
        if ($dashboard instanceof AnalyticsDashboard) {
            return $dashboard;
        }

        if (isset($data['analytics_dashboard_id'])) {
            $model = AnalyticsDashboard::query()
                ->active()
                ->whereKey($data['analytics_dashboard_id'])
                ->first();

            if ($model !== null) {
                return $model;
            }
        }

        $codeOrAudience = $data['dashboard'] ?? $dashboard ?? AnalyticsDashboardAudience::Owner->value;

        if (is_string($codeOrAudience) && filled($codeOrAudience)) {
            $model = AnalyticsDashboard::query()
                ->active()
                ->where('code', $codeOrAudience)
                ->first();

            if ($model !== null) {
                return $model;
            }

            $model = AnalyticsDashboard::query()
                ->active()
                ->forAudience($codeOrAudience)
                ->default()
                ->ordered()
                ->first();

            if ($model !== null) {
                return $model;
            }
        }

        return AnalyticsDashboard::query()
            ->active()
            ->forAudience(AnalyticsDashboardAudience::Owner)
            ->default()
            ->ordered()
            ->first();
    }

    /**
     * @param  array<int, string>  $widgetOrder
     * @return array<string, mixed>
     */
    private function legacyLayout(array $widgetOrder): array
    {
        return [
            'widgets' => collect($widgetOrder)
                ->map(fn (string $code, int $index): array => [
                    'code' => $code,
                    'sort_order' => ($index + 1) * 10,
                ])
                ->values()
                ->all(),
        ];
    }
}
