<?php

namespace Database\Factories;

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
            'dashboard' => 'owner',
            'visible_widget_codes' => ['open_leads', 'active_students', 'paid_revenue'],
            'widget_order' => ['open_leads', 'active_students', 'paid_revenue'],
            'filters' => [],
            'refresh_interval_seconds' => 300,
            'timezone' => config('app.timezone'),
            'is_default' => true,
            'settings' => [],
        ];
    }
}
