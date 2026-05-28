<?php

namespace Database\Factories;

use App\Models\NotificationChannel;
use App\Models\User;
use App\Models\UserNotificationPreference;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserNotificationPreference>
 */
class UserNotificationPreferenceFactory extends Factory
{
    protected $model = UserNotificationPreference::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'notification_channel_id' => NotificationChannel::factory(),
            'event' => 'all',
            'is_enabled' => true,
            'quiet_hours_start' => null,
            'quiet_hours_end' => null,
            'send_reminder_before_minutes' => 60,
            'settings' => null,
        ];
    }
}
