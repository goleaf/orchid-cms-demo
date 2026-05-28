<?php

namespace App\Actions;

use App\Models\NotificationActivity;
use App\Models\NotificationDelivery;
use App\Models\NotificationMessage;
use App\Models\NotificationRecipient;

class MarkNotificationFailedAction
{
    public function handle(NotificationDelivery $delivery, ?string $errorMessage = null): NotificationDelivery
    {
        $delivery->forceFill([
            'status' => NotificationDelivery::STATUS_FAILED,
            'failed_at' => now(),
            'error_message' => $errorMessage,
        ])->save();

        $delivery->recipient?->forceFill([
            'status' => NotificationRecipient::STATUS_FAILED,
        ])->save();

        $delivery->message?->forceFill([
            'status' => NotificationMessage::STATUS_FAILED,
            'failed_at' => now(),
        ])->save();

        NotificationActivity::query()->create([
            'message_id' => $delivery->message_id,
            'recipient_id' => $delivery->recipient_id,
            'delivery_id' => $delivery->id,
            'activity_type' => NotificationActivity::TYPE_FAILED,
            'description' => $errorMessage,
            'occurred_at' => now(),
        ]);

        return $delivery->refresh();
    }
}
