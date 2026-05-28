<?php

namespace App\Actions;

use App\Models\CommunicationReminder;
use App\Models\CommunicationTemplate;
use App\Models\NotificationChannel;
use App\Models\NotificationDeliveryLog;
use App\Models\User;
use App\Notifications\InternalCommunicationNotification;

class CreateInternalNotificationAction
{
    /**
     * @param  array<string, mixed>|null  $metadata
     */
    public function handle(
        User $recipient,
        string $title,
        ?string $body = null,
        ?User $createdBy = null,
        ?CommunicationTemplate $template = null,
        ?CommunicationReminder $reminder = null,
        ?array $metadata = null,
    ): NotificationDeliveryLog {
        $recipient->notify(new InternalCommunicationNotification($title, $body, $metadata ?? []));

        $channel = NotificationChannel::query()
            ->active()
            ->where('code', NotificationChannel::CODE_INTERNAL)
            ->first();

        return app(LogNotificationDeliveryAction::class)->handle([
            'user_id' => $recipient->id,
            'notification_channel_id' => $channel?->id,
            'communication_template_id' => $template?->id,
            'communication_reminder_id' => $reminder?->id,
            'direction' => 'outbound',
            'status' => NotificationDeliveryLog::STATUS_SENT,
            'recipient_name' => $recipient->name,
            'recipient_email' => $recipient->email,
            'subject' => $title,
            'body' => $body,
            'provider' => 'database',
            'provider_status' => 'stored',
            'sent_at' => now(),
            'created_by_id' => $createdBy?->id,
            'metadata' => $metadata,
        ], $recipient);
    }
}
