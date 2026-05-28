<?php

namespace App\Actions;

use App\Models\NotificationActivity;
use App\Models\NotificationMessage;
use Illuminate\Support\Carbon;

class ScheduleNotificationMessageAction
{
    public function handle(NotificationMessage $message, Carbon|string $scheduledAt): NotificationMessage
    {
        $message->forceFill([
            'status' => NotificationMessage::STATUS_SCHEDULED,
            'scheduled_at' => $scheduledAt instanceof Carbon ? $scheduledAt : Carbon::parse($scheduledAt),
        ])->save();

        NotificationActivity::query()->create([
            'message_id' => $message->id,
            'user_id' => $message->created_by_id,
            'activity_type' => 'scheduled',
            'description' => tkey('notifications.activities.message_scheduled'),
            'occurred_at' => now(),
            'metadata' => ['scheduled_at' => $message->scheduled_at?->toISOString()],
        ]);

        return $message->refresh();
    }
}
