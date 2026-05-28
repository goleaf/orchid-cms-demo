<?php

namespace App\Actions;

use App\Models\NotificationPreference;

class UpdateNotificationPreferenceAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data): NotificationPreference
    {
        $keys = [
            'user_id' => $data['user_id'] ?? null,
            'student_id' => $data['student_id'] ?? null,
            'lead_id' => $data['lead_id'] ?? null,
            'channel_id' => $data['channel_id'],
        ];

        return NotificationPreference::query()->updateOrCreate($keys, [
            'enabled' => (bool) ($data['enabled'] ?? true),
            'locale' => $data['locale'] ?? null,
            'settings' => $data['settings'] ?? null,
        ])->refresh();
    }
}
