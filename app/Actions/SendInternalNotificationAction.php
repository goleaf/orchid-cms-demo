<?php

namespace App\Actions;

use App\Models\NotificationActivity;
use App\Models\NotificationDelivery;
use App\Models\NotificationMessage;
use App\Models\NotificationRecipient;
use App\Models\User;
use App\Notifications\InternalCommunicationNotification;

class SendInternalNotificationAction
{
    public function handle(NotificationMessage $message, ?User $sender = null): NotificationMessage
    {
        $message->loadMissing(['recipients.user', 'channel']);
        $sent = 0;

        foreach ($message->recipients as $recipient) {
            $delivery = $this->delivery($message, $recipient, NotificationDelivery::STATUS_SENT, 'database');

            if ($recipient->user === null) {
                app(MarkNotificationFailedAction::class)->handle($delivery, tkey('notifications.validation.internal_recipient_required'));

                continue;
            }

            $recipient->user->notify(new InternalCommunicationNotification(
                $message->subject ?: tkey('notifications.messages.fallback_subject', locale: $recipient->locale),
                $message->body,
                ['notification_message_id' => $message->id, 'sender_id' => $sender?->id],
            ));

            $recipient->forceFill(['status' => NotificationRecipient::STATUS_SENT])->save();
            $sent++;

            NotificationActivity::query()->create([
                'message_id' => $message->id,
                'recipient_id' => $recipient->id,
                'delivery_id' => $delivery->id,
                'user_id' => $sender?->id,
                'activity_type' => NotificationActivity::TYPE_SENT,
                'description' => tkey('notifications.activities.message_sent'),
                'occurred_at' => now(),
                'metadata' => ['channel' => $message->channel?->code],
            ]);
        }

        $message->forceFill([
            'status' => $sent > 0 ? NotificationMessage::STATUS_SENT : NotificationMessage::STATUS_FAILED,
            'sent_at' => $sent > 0 ? now() : null,
            'failed_at' => $sent > 0 ? null : now(),
        ])->save();

        return $message->refresh()->loadMissing(['recipients', 'deliveries']);
    }

    private function delivery(
        NotificationMessage $message,
        NotificationRecipient $recipient,
        string $status,
        string $provider,
    ): NotificationDelivery {
        return NotificationDelivery::query()->create([
            'message_id' => $message->id,
            'recipient_id' => $recipient->id,
            'channel_id' => $message->channel_id,
            'status' => $status,
            'provider' => $provider,
            'attempt_no' => 1,
            'sent_at' => now(),
        ]);
    }
}
