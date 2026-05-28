<?php

namespace App\Actions;

use App\Models\MarketingLead;

class PrepareLeadConversionDataAction
{
    /**
     * @return array{student: array<string, mixed>, enrollment: array<string, mixed>}
     */
    public function handle(MarketingLead $lead): array
    {
        return [
            'student' => [
                'full_name' => $lead->fullName(),
                'first_name' => $lead->first_name,
                'last_name' => $lead->last_name,
                'middle_name' => $lead->middle_name,
                'phone' => $lead->phone,
                'normalized_phone' => $lead->normalized_phone,
                'email' => $lead->email,
                'preferred_messenger' => $lead->preferred_messenger,
                'city' => $lead->city,
                'locale' => $lead->locale,
                'branch_id' => $lead->branch_id,
                'consent_accepted' => $lead->consent_accepted,
                'consent_accepted_at' => $lead->consent_accepted_at,
                'consent_text_version' => $lead->consent_text_version,
                'source_lead_id' => $lead->id,
                'manager_id' => $lead->responsible_manager_id,
                'source_label' => $lead->source,
                'comment' => $lead->message,
                'internal_comment' => $lead->internal_comment,
            ],
            'enrollment' => [
                'lead_id' => $lead->id,
                'training_program_id' => $lead->training_program_id,
                'course_id' => $lead->training_program_id,
                'course_category_id' => $lead->course_category_id,
                'branch_id' => $lead->branch_id,
                'training_group_id' => $lead->training_group_id,
                'start_date' => $lead->desired_start_date,
                'preferred_time' => $lead->preferred_time,
                'training_language' => $lead->preferred_training_language,
                'gearbox_type' => $lead->preferred_gearbox,
                'price' => $lead->budget,
                'manager_id' => $lead->responsible_manager_id,
            ],
        ];
    }
}
