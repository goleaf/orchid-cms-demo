<?php

namespace App\Actions;

use App\Enums\LeadStatus;
use App\Models\MarketingLead;
use App\Models\User;

class UpdateLeadAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(MarketingLead $lead, array $data, ?User $user = null): MarketingLead
    {
        $before = $lead->only([
            'status',
            'responsible_manager_id',
            'phone',
            'email',
            'training_program_id',
            'branch_id',
            'training_group_id',
        ]);
        $targetStatus = $data['status'] ?? $lead->status;
        $payload = $this->payload($lead, $data, $user);
        $payload['status'] = $lead->status;

        $lead = app(UpdateMarketingLeadCrmAction::class)->handle($lead, $payload);

        if ((string) ($targetStatus instanceof LeadStatus ? $targetStatus->value : $targetStatus) !== $lead->status->value) {
            $lead = app(ChangeLeadStatusAction::class)->handle(
                $lead,
                $targetStatus instanceof LeadStatus ? $targetStatus : (string) $targetStatus,
                $user,
                tkey('crm.activities.reasons.crm_card_status_update'),
            );
        }

        app(RecordLeadActivityAction::class)->handle(
            $lead->refresh(),
            $user,
            'updated',
            tkey('crm.activities.titles.updated'),
            null,
        );

        $this->recordImportantChanges($lead->refresh(), $before, $user);

        return $lead->refresh();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function payload(MarketingLead $lead, array $data, ?User $user): array
    {
        return [
            'responsible_manager_id' => $data['responsible_manager_id'] ?? $data['manager_id'] ?? $lead->responsible_manager_id,
            'branch_id' => $data['branch_id'] ?? $lead->branch_id,
            'training_program_id' => $data['training_program_id'] ?? $data['course_id'] ?? $lead->training_program_id,
            'training_group_id' => $data['training_group_id'] ?? $lead->training_group_id,
            'instructor_id' => $data['instructor_id'] ?? $lead->instructor_id,
            'first_name' => $data['first_name'] ?? $lead->first_name,
            'middle_name' => $data['middle_name'] ?? $lead->middle_name,
            'last_name' => $data['last_name'] ?? $lead->last_name,
            'email' => $data['email'] ?? $lead->email,
            'phone' => array_key_exists('phone', $data) ? app(NormalizeLeadPhoneAction::class)->handle($data['phone']) : $lead->phone,
            'messenger' => $data['messenger'] ?? $data['preferred_messenger'] ?? $lead->messenger,
            'city' => $data['city'] ?? $lead->city,
            'source' => $data['source'] ?? $lead->source,
            'license_category' => $data['license_category'] ?? $lead->license_category,
            'preferred_format' => $data['preferred_format'] ?? $lead->preferred_format,
            'preferred_language' => $data['preferred_language'] ?? $data['preferred_training_language'] ?? $lead->preferred_language,
            'preferred_time' => $data['preferred_time'] ?? $lead->preferred_time,
            'desired_start_date' => $data['desired_start_date'] ?? $lead->desired_start_date,
            'preferred_gearbox' => $data['preferred_gearbox'] ?? $lead->preferred_gearbox,
            'budget_eur' => $data['budget_eur'] ?? $data['budget'] ?? $lead->budget,
            'is_hot' => $data['is_hot'] ?? $lead->is_hot,
            'priority' => $data['priority'] ?? $lead->priority,
            'lead_score' => $data['lead_score'] ?? $lead->lead_score,
            'next_follow_up_at' => $data['next_follow_up_at'] ?? $lead->next_follow_up_at,
            'last_contacted_at' => $data['last_contacted_at'] ?? $lead->last_contacted_at,
            'rejection_reason' => $data['rejection_reason'] ?? $lead->rejection_reason,
            'lost_reason_code' => $data['lost_reason_code'] ?? $lead->lost_reason_code,
            'message' => $data['message'] ?? $data['comment'] ?? $lead->message,
            'internal_comment' => $data['internal_comment'] ?? $lead->internal_comment,
            'duplicate_of_id' => $data['duplicate_of_id'] ?? $lead->duplicate_of_id,
            'consent_accepted' => $data['consent_accepted'] ?? $lead->consent_accepted,
            'consent_accepted_at' => $data['consent_accepted_at'] ?? $lead->consent_accepted_at,
            'consent_text_version' => $data['consent_text_version'] ?? $lead->consent_text_version,
            'updated_by_user_id' => $data['updated_by_user_id'] ?? $user?->id ?? $lead->updated_by_user_id,
        ];
    }

    /**
     * @param  array<string, mixed>  $before
     */
    private function recordImportantChanges(MarketingLead $lead, array $before, ?User $user): void
    {
        $fields = [
            'status' => 'status_changed',
            'responsible_manager_id' => 'manager_assigned',
            'phone' => 'updated',
            'email' => 'updated',
            'training_program_id' => 'updated',
            'branch_id' => 'updated',
            'training_group_id' => 'updated',
        ];

        foreach ($fields as $field => $type) {
            if ($this->scalarValue($before[$field] ?? null) === $this->scalarValue($lead->getAttribute($field))) {
                continue;
            }

            app(RecordLeadActivityAction::class)->handle(
                $lead,
                $user,
                $type,
                tkey('crm.activities.titles.updated'),
                null,
                $this->scalarValue($before[$field] ?? null),
                $this->scalarValue($lead->getAttribute($field)),
                ['field' => $field],
            );
        }
    }

    private function scalarValue(mixed $value): ?string
    {
        if (! filled($value)) {
            return null;
        }

        if ($value instanceof LeadStatus) {
            return $value->value;
        }

        return (string) $value;
    }
}
