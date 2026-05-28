<?php

namespace App\Actions;

use App\Models\CommunicationReminder;
use App\Models\CommunicationTemplate;
use App\Models\NotificationChannel;
use App\Models\Student;
use App\Models\StudentCommunication;
use App\Models\StudentEnrollment;
use App\Models\User;
use Illuminate\Support\Carbon;

class LogStudentCommunicationAction
{
    /**
     * @param  array<string, mixed>|null  $metadata
     */
    public function handle(
        Student $student,
        ?User $user,
        NotificationChannel|string $channel,
        string $direction,
        ?string $subject,
        ?string $body,
        ?array $metadata = null,
        ?CommunicationTemplate $template = null,
        ?CommunicationReminder $reminder = null,
        ?StudentEnrollment $enrollment = null,
        ?Carbon $communicatedAt = null,
        ?Carbon $clientRepliedAt = null,
        ?Carbon $callbackRequiredAt = null,
    ): StudentCommunication {
        $channelModel = $channel instanceof NotificationChannel ? $channel : null;
        $channelCode = $channel instanceof NotificationChannel ? $channel->code : $channel;

        $communication = $student->communications()->create([
            'student_enrollment_id' => $enrollment?->id,
            'marketing_lead_id' => $student->source_lead_id,
            'user_id' => $user?->id,
            'notification_channel_id' => $channelModel?->id ?? $template?->notification_channel_id,
            'communication_template_id' => $template?->id,
            'communication_reminder_id' => $reminder?->id,
            'channel' => $channelCode,
            'direction' => $direction,
            'subject' => filled($subject) ? $subject : $template?->subject(),
            'body' => filled($body) ? $body : $template?->body(),
            'communicated_at' => $communicatedAt ?? now(),
            'client_replied_at' => $clientRepliedAt,
            'callback_required_at' => $callbackRequiredAt,
            'metadata' => $this->metadata($metadata, $template, $reminder),
        ]);

        app(RecordStudentActivityAction::class)->handle(
            $student->refresh(),
            $user,
            'communication_logged',
            tkey('communication.activities.student_communication_logged'),
            $communication->subject ?: $communication->body,
            null,
            null,
            [
                'student_communication_id' => $communication->id,
                'channel' => $channelCode,
                'direction' => $direction,
            ],
            $enrollment,
            $student->sourceLead,
        );

        return $communication->refresh();
    }

    /**
     * @param  array<string, mixed>|null  $metadata
     * @return array<string, mixed>|null
     */
    private function metadata(?array $metadata, ?CommunicationTemplate $template, ?CommunicationReminder $reminder): ?array
    {
        if ($template === null && $reminder === null) {
            return $metadata;
        }

        return [
            ...($metadata ?? []),
            'communication_template_id' => $template?->id,
            'communication_reminder_id' => $reminder?->id,
        ];
    }
}
