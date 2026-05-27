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
        $lead->fill([
            'responsible_manager_id' => $data['responsible_manager_id'] ?? null,
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
            'rejection_reason' => $data['rejection_reason'] ?? null,
            'message' => $data['message'] ?? null,
        ])->save();

        return $lead->refresh();
    }
}
