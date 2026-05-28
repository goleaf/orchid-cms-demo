<?php

namespace App\Actions;

use App\Models\NotificationActivity;
use App\Models\NotificationDelivery;
use App\Models\NotificationMessage;
use App\Models\NotificationRecipient;
use App\Notifications\NotificationMessageMailNotification;
use Illuminate\Support\Facades\Notification;

class SendEmailNotificationAction
{
    public function handle(NotificationMessage $message): NotificationMessage
    {
        $message->loadMissing(['recipients', 'channel']);
        $sent = 0;

        foreach ($message->recipients as $recipient) {
            $delivery = NotificationDelivery::query()->create([
                'message_id' => $message->id,
                'recipient_id' => $recipient->id,
                'channel_id' => $message->channel_id,
                'status' => NotificationDelivery::STATUS_SENT,
                'provider' => config('mail.default', 'mail'),
                'attempt_no' => 1,
                'sent_at' => now(),
            ]);

            if (! filled($recipient->email)) {
                app(MarkNotificationFailedAction::class)->handle($delivery, tkey('notifications.validation.email_recipient_required'));

                continue;
            }

            Notification::route('mail', $recipient->email)
                ->notify(new NotificationMessageMailNotification($message, $recipient));

            $recipient->forceFill(['status' => NotificationRecipient::STATUS_SENT])->save();
            $sent++;

            NotificationActivity::query()->create([
                'message_id' => $message->id,
                'recipient_id' => $recipient->id,
                'delivery_id' => $delivery->id,
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
}
