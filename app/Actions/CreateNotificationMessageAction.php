<?php

namespace App\Actions;

use App\Models\NotificationActivity;
use App\Models\NotificationMessage;
use App\Models\NotificationRecipient;
use App\Support\Notifications\NotificationTargetResolver;
use Illuminate\Database\Eloquent\Model;

class CreateNotificationMessageAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data): NotificationMessage
    {
        $message = NotificationMessage::query()->create([
            'message_number' => $data['message_number'] ?? null,
            'channel_id' => $data['channel_id'],
            'template_id' => $data['template_id'] ?? null,
            'template_version_id' => $data['template_version_id'] ?? null,
            'subject' => $data['subject'] ?? null,
            'body' => $data['body'],
            'priority' => $data['priority'] ?? NotificationMessage::PRIORITY_NORMAL,
            'status' => $data['status'] ?? $this->defaultStatus($data),
            'scheduled_at' => $data['scheduled_at'] ?? null,
            'sent_at' => $data['sent_at'] ?? null,
            'failed_at' => $data['failed_at'] ?? null,
            'created_by_id' => $data['created_by_id'] ?? null,
            'metadata' => $data['metadata'] ?? null,
        ]);

        foreach ($data['recipients'] ?? [] as $recipient) {
            $this->createRecipient($message, is_array($recipient) ? $recipient : []);
        }

        NotificationActivity::query()->create([
            'message_id' => $message->id,
            'user_id' => $data['created_by_id'] ?? null,
            'activity_type' => NotificationActivity::TYPE_CREATED,
            'description' => tkey('notifications.activities.message_created'),
            'occurred_at' => now(),
            'metadata' => ['status' => $message->status],
        ]);

        return $message->refresh()->loadMissing(['channel', 'template', 'templateVersion', 'recipients']);
    }

    /**
     * @param  array<string, mixed>  $recipient
     */
    private function createRecipient(NotificationMessage $message, array $recipient): NotificationRecipient
    {
        $target = $this->target($recipient);

        if ($target !== null) {
            $recipient = [
                ...app(NotificationTargetResolver::class)->recipientPayload($target),
                ...$recipient,
            ];
        }

        unset($recipient['target_type'], $recipient['target_id']);

        return $message->recipients()->create([
            'user_id' => $recipient['user_id'] ?? null,
            'student_id' => $recipient['student_id'] ?? null,
            'lead_id' => $recipient['lead_id'] ?? null,
            'email' => $recipient['email'] ?? null,
            'phone' => $recipient['phone'] ?? null,
            'locale' => $recipient['locale'] ?? null,
            'status' => $recipient['status'] ?? NotificationRecipient::STATUS_PENDING,
            'metadata' => $recipient['metadata'] ?? null,
        ]);
    }

    /**
     * @param  array<string, mixed>  $recipient
     */
    private function target(array $recipient): ?Model
    {
        return app(NotificationTargetResolver::class)->resolve(
            is_string($recipient['target_type'] ?? null) ? $recipient['target_type'] : null,
            $recipient['target_id'] ?? null,
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function defaultStatus(array $data): string
    {
        return filled($data['scheduled_at'] ?? null)
            ? NotificationMessage::STATUS_SCHEDULED
            : NotificationMessage::STATUS_DRAFT;
    }
}
