<?php

namespace Database\Factories;

use App\Models\AnalyticsDashboard;
use App\Models\User;
use App\Models\UserDashboardPreference;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserDashboardPreference>
 */
class UserDashboardPreferenceFactory extends Factory
{
    protected $model = UserDashboardPreference::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'analytics_dashboard_id' => AnalyticsDashboard::factory(),
            'dashboard' => 'owner',
            'layout' => [
                'widgets' => [
                    ['code' => 'open_leads', 'width' => 3, 'height' => 1, 'sort_order' => 10],
                    ['code' => 'active_students', 'width' => 3, 'height' => 1, 'sort_order' => 20],
                    ['code' => 'paid_revenue', 'width' => 3, 'height' => 1, 'sort_order' => 30],
                ],
            ],
            'visible_widget_codes' => ['open_leads', 'active_students', 'paid_revenue'],
            'widget_order' => ['open_leads', 'active_students', 'paid_revenue'],
            'filters' => [],
            'refresh_interval_seconds' => 300,
            'timezone' => config('app.timezone'),
            'is_default' => true,
            'settings' => [],
        ];
    }

    public function forDashboard(AnalyticsDashboard $dashboard): static
    {
        return $this->state(fn (): array => [
            'analytics_dashboard_id' => $dashboard->id,
            'dashboard' => $dashboard->code,
        ]);
    }
}
