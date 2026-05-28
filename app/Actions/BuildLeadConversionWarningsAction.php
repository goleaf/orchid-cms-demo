<?php

namespace App\Actions;

use App\Models\MarketingLead;

class BuildLeadConversionWarningsAction
{
    /**
     * @return array<int, string>
     */
    public function handle(MarketingLead $lead): array
    {
        $warnings = [];

        if (blank($lead->training_program_id) && blank($lead->course_category_id)) {
            $warnings[] = 'students.conversion.warnings.no_course';
        }

        if (blank($lead->branch_id)) {
            $warnings[] = 'students.conversion.warnings.no_branch';
        }

        if (blank($lead->training_group_id)) {
            $warnings[] = 'students.conversion.warnings.no_group';
        } elseif ($lead->trainingGroup?->is_full) {
            $warnings[] = 'students.conversion.warnings.group_full';
        }

        if (! $lead->consent_accepted) {
            $warnings[] = 'students.conversion.warnings.no_consent';
        }

        if (blank($lead->phone) || blank($lead->email)) {
            $warnings[] = 'students.conversion.warnings.weak_contact_data';
        }

        if ($lead->created_at !== null && $lead->created_at->lt(now()->subDays(30))) {
            $warnings[] = 'students.conversion.warnings.old_lead';
        }

        if ($lead->overdueTasks()->exists()) {
            $warnings[] = 'students.conversion.warnings.overdue_tasks';
        }

        if ($lead->is_lost) {
            $warnings[] = 'students.conversion.warnings.lead_lost';
        }

        if ($lead->is_duplicate) {
            $warnings[] = 'students.conversion.warnings.lead_duplicate';
        }

        if (app(FindStudentMatchesForLeadAction::class)->handle($lead)->isNotEmpty()) {
            $warnings[] = 'students.conversion.warnings.possible_duplicate_student';
        }

        return array_values(array_unique($warnings));
    }
}
