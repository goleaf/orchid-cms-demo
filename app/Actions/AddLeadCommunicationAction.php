<?php

namespace App\Actions;

use App\Enums\LeadTaskPriority;
use App\Models\MarketingLead;
use App\Models\MarketingLeadCommunication;
use App\Models\MarketingMessageTemplate;
use App\Models\User;
use Illuminate\Support\Carbon;

class AddLeadCommunicationAction
{
    /**
     * @param  array<string, mixed>|null  $metadata
     */
    public function handle(
        MarketingLead $lead,
        ?User $user,
        string $channel,
        string $direction,
        ?string $subject,
        ?string $body,
        ?array $metadata = null,
        ?MarketingMessageTemplate $template = null,
        bool $clientReplied = false,
        bool $callbackRequired = false,
        ?Carbon $callbackRequiredAt = null,
        ?string $callRecordingUrl = null,
        ?string $callRecordingReference = null,
        ?string $callResult = null,
        ?int $durationSeconds = null,
    ): MarketingLeadCommunication {
        $callbackAt = $callbackRequired
            ? ($callbackRequiredAt ?? now()->addDay())
            : null;
        $clientRepliedAt = $clientReplied ? now() : null;

        $communication = $lead->communications()->create([
            'user_id' => $user?->id,
            'marketing_message_template_id' => $template?->id,
            'channel' => $channel,
            'direction' => $direction,
            'subject' => filled($subject) ? $subject : $template?->subject,
            'body' => filled($body) ? $body : $template?->body,
            'communicated_at' => now(),
            'client_replied_at' => $clientRepliedAt,
            'callback_required_at' => $callbackAt,
            'call_recording_url' => $callRecordingUrl,
            'call_recording_reference' => $callRecordingReference,
            'call_result' => $callResult,
            'duration_seconds' => $durationSeconds,
            'metadata' => $this->metadata($metadata, $template),
        ]);

        $this->applyLeadFollowUpState($lead, $user, $clientRepliedAt, $callbackAt);

        app(RecordLeadActivityAction::class)->handle(
            $lead->refresh(),
            $user,
            $this->activityType($channel),
            tkey('crm.activities.titles.communication_logged'),
            $communication->subject ?: $communication->body,
            null,
            null,
            [
                'communication_id' => $communication->id,
                'channel' => $channel,
                'direction' => $direction,
                'call_result' => $callResult,
            ],
        );

        return $communication;
    }

    /**
     * @param  array<string, mixed>|null  $metadata
     * @return array<string, mixed>|null
     */
    private function metadata(?array $metadata, ?MarketingMessageTemplate $template): ?array
    {
        if ($template === null) {
            return $metadata;
        }

        return [
            ...($metadata ?? []),
            'message_template' => [
                'id' => $template->id,
                'name' => $template->name,
                'channel' => $template->channel,
            ],
        ];
    }

    private function applyLeadFollowUpState(
        MarketingLead $lead,
        ?User $user,
        ?Carbon $clientRepliedAt,
        ?Carbon $callbackAt,
    ): void {
        $updates = [];

        if ($clientRepliedAt !== null && $lead->contacted_at === null) {
            $updates['contacted_at'] = $clientRepliedAt;
        }

        if ($callbackAt !== null) {
            $updates['next_follow_up_at'] = $callbackAt;
        }

        if ($updates !== []) {
            $lead->fill($updates)->save();
        }

        if ($callbackAt !== null) {
            app(CreateLeadTaskAction::class)->handle(
                $lead->refresh(),
                $user,
                tkey('crm.tasks.system_titles.call_back', ['name' => $lead->fullName()]),
                $callbackAt,
                $lead->is_hot ? LeadTaskPriority::High : LeadTaskPriority::Normal,
                tkey('crm.tasks.system_notes.callback_reminder'),
            );
        }
    }

    private function activityType(string $channel): string
    {
        return match ($channel) {
            'phone' => 'call_logged',
            'email' => 'email_logged',
            'telegram', 'whatsapp', 'messenger', 'sms' => 'messenger_logged',
            default => 'communication_logged',
        };
    }
}
