<?php

namespace App\Actions;

use App\Models\NotificationActivity;
use App\Models\NotificationDelivery;
use App\Models\NotificationMessage;
use App\Models\NotificationRecipient;

class SendPlaceholderNotificationAction
{
    public function handle(NotificationMessage $message, string $provider): NotificationMessage
    {
        $message->loadMissing(['recipients', 'channel']);

        foreach ($message->recipients as $recipient) {
            $delivery = NotificationDelivery::query()->create([
                'message_id' => $message->id,
                'recipient_id' => $recipient->id,
                'channel_id' => $message->channel_id,
                'status' => NotificationDelivery::STATUS_QUEUED,
                'provider' => $provider,
                'attempt_no' => 1,
                'metadata' => ['placeholder' => true],
            ]);

            $recipient->forceFill(['status' => NotificationRecipient::STATUS_QUEUED])->save();

            NotificationActivity::query()->create([
                'message_id' => $message->id,
                'recipient_id' => $recipient->id,
                'delivery_id' => $delivery->id,
                'activity_type' => 'queued',
                'description' => tkey('notifications.activities.placeholder_queued'),
                'occurred_at' => now(),
                'metadata' => ['provider' => $provider, 'channel' => $message->channel?->code],
            ]);
        }

        $message->forceFill([
            'status' => NotificationMessage::STATUS_QUEUED,
        ])->save();

        return $message->refresh()->loadMissing(['recipients', 'deliveries']);
    }
}
