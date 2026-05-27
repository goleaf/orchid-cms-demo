<?php

namespace App\Actions;

use App\Models\MarketingLead;

class UpdateMarketingLeadCrmAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(MarketingLead $lead, array $data): MarketingLead
    {
        $previousManagerId = $lead->responsible_manager_id;
        $duplicate = app(DetectLeadDuplicateAction::class)->handle($data, $lead);

        $lead->fill([
            'responsible_manager_id' => $data['responsible_manager_id'] ?? null,
            'assigned_by_user_id' => ($previousManagerId !== ($data['responsible_manager_id'] ?? null))
                ? ($data['updated_by_user_id'] ?? null)
                : $lead->assigned_by_user_id,
            'assigned_at' => ($previousManagerId !== ($data['responsible_manager_id'] ?? null))
                ? now()
                : $lead->assigned_at,
            'branch_id' => $data['branch_id'] ?? null,
            'training_program_id' => $data['training_program_id'] ?? null,
            'training_group_id' => $data['training_group_id'] ?? null,
            'instructor_id' => $data['instructor_id'] ?? null,
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'] ?? null,
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'messenger' => $data['messenger'] ?? null,
            'city' => $data['city'] ?? null,
            'source' => $data['source'],
            'status' => $data['status'],
            'license_category' => $data['license_category'] ?? null,
            'preferred_format' => $data['preferred_format'] ?? null,
            'preferred_language' => $data['preferred_language'] ?? null,
            'preferred_time' => $data['preferred_time'] ?? null,
            'budget_cents' => filled($data['budget_eur'] ?? null)
                ? (int) round(((float) $data['budget_eur']) * 100)
                : null,
            'is_hot' => (bool) ($data['is_hot'] ?? $lead->is_hot),
            'priority' => $data['priority'] ?? $lead->priority ?? 'normal',
            'lead_score' => (int) ($data['lead_score'] ?? $lead->lead_score ?? 0),
            'next_follow_up_at' => $data['next_follow_up_at'] ?? $lead->next_follow_up_at,
            'rejection_reason' => $data['rejection_reason'] ?? null,
            'lost_reason_code' => $data['lost_reason_code'] ?? null,
            'message' => $data['message'] ?? null,
            'internal_comment' => $data['internal_comment'] ?? null,
            'duplicate_of_id' => $data['duplicate_of_id'] ?? $duplicate?->id,
            'updated_by_user_id' => $data['updated_by_user_id'] ?? $lead->updated_by_user_id,
        ])->save();

        return $lead->refresh();
    }
}
