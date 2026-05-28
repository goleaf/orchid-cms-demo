<?php

namespace App\Actions;

use App\Models\NotificationActivity;
use App\Models\NotificationDelivery;
use App\Models\NotificationMessage;
use App\Models\NotificationRecipient;

class MarkNotificationDeliveredAction
{
    public function handle(NotificationDelivery $delivery): NotificationDelivery
    {
        $delivery->forceFill([
            'status' => NotificationDelivery::STATUS_DELIVERED,
            'delivered_at' => now(),
            'failed_at' => null,
            'error_message' => null,
        ])->save();

        $delivery->recipient?->forceFill([
            'status' => NotificationRecipient::STATUS_DELIVERED,
        ])->save();

        NotificationActivity::query()->create([
            'message_id' => $delivery->message_id,
            'recipient_id' => $delivery->recipient_id,
            'delivery_id' => $delivery->id,
            'activity_type' => NotificationActivity::TYPE_DELIVERED,
            'description' => tkey('notifications.activities.message_delivered'),
            'occurred_at' => now(),
        ]);

        if (! $delivery->message?->deliveries()->where('status', '<>', NotificationDelivery::STATUS_DELIVERED)->exists()) {
            $delivery->message?->forceFill(['status' => NotificationMessage::STATUS_DELIVERED])->save();
        }

        return $delivery->refresh();
    }
}
