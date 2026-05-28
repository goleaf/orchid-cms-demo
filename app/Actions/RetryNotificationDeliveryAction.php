<?php

namespace App\Actions;

use App\Models\NotificationActivity;
use App\Models\NotificationDelivery;
use App\Models\NotificationMessage;
use App\Models\NotificationRecipient;
use Illuminate\Validation\ValidationException;

class RetryNotificationDeliveryAction
{
    public function handle(NotificationDelivery $delivery): NotificationDelivery
    {
        if ($delivery->status !== NotificationDelivery::STATUS_FAILED) {
            throw ValidationException::withMessages([
                'delivery_id' => tkey('notifications.validation.delivery_cannot_be_retried'),
            ]);
        }

        $retry = NotificationDelivery::query()->create([
            'message_id' => $delivery->message_id,
            'recipient_id' => $delivery->recipient_id,
            'channel_id' => $delivery->channel_id,
            'status' => NotificationDelivery::STATUS_QUEUED,
            'provider' => $delivery->provider,
            'attempt_no' => $delivery->attempt_no + 1,
            'metadata' => [
                ...($delivery->metadata ?? []),
                'retried_from_delivery_id' => $delivery->id,
            ],
        ]);

        $delivery->recipient?->forceFill(['status' => NotificationRecipient::STATUS_QUEUED])->save();
        $delivery->message?->forceFill([
            'status' => NotificationMessage::STATUS_QUEUED,
            'failed_at' => null,
        ])->save();

        NotificationActivity::query()->create([
            'message_id' => $retry->message_id,
            'recipient_id' => $retry->recipient_id,
            'delivery_id' => $retry->id,
            'activity_type' => 'retried',
            'description' => tkey('notifications.activities.delivery_retried'),
            'occurred_at' => now(),
            'metadata' => ['previous_delivery_id' => $delivery->id],
        ]);

        return $retry->refresh();
    }
}
