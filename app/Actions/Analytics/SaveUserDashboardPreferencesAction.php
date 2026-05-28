<?php

namespace App\Actions\Analytics;

use App\Models\User;
use App\Models\UserDashboardPreference;

class SaveUserDashboardPreferencesAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(User $user, array $data, string $dashboard = 'owner'): UserDashboardPreference
    {
        return UserDashboardPreference::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'dashboard' => $dashboard,
            ],
            [
                'visible_widget_codes' => $data['visible_widget_codes'] ?? [],
                'widget_order' => $data['widget_order'] ?? [],
                'filters' => $data['filters'] ?? [],
                'refresh_interval_seconds' => (int) ($data['refresh_interval_seconds'] ?? 300),
                'timezone' => $data['timezone'] ?? config('app.timezone'),
                'is_default' => (bool) ($data['is_default'] ?? true),
                'settings' => $data['settings'] ?? [],
            ],
        );
    }
}
