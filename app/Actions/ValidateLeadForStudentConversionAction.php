<?php

namespace App\Actions;

use App\Enums\LeadStatus;
use App\Models\MarketingLead;
use App\Models\User;

class ValidateLeadForStudentConversionAction
{
    /**
     * @param  array<string, mixed>  $enrollmentData
     * @return array{can_convert: bool, blocking_errors: array<int, string>, warnings: array<int, string>, suggested_data: array<string, mixed>}
     */
    public function handle(MarketingLead $lead, ?User $user = null, array $enrollmentData = []): array
    {
        $errors = [];
        $override = $user?->hasAnyAccess(['crm.leads.override_status_transition', 'students.override_status_transition']) ?? false;

        if ($lead->trashed()) {
            $errors[] = 'students.conversion.validation.lead_cannot_convert';
        }

        if ($lead->is_converted) {
            $errors[] = 'students.conversion.validation.lead_already_converted';
        }

        if ($lead->is_spam) {
            $errors[] = 'students.conversion.validation.lead_is_spam';
        }

        if ($lead->is_lost && ! $override) {
            $errors[] = 'students.conversion.validation.lead_is_lost';
        }

        if ($lead->is_duplicate && ! $override) {
            $errors[] = 'students.conversion.validation.lead_is_duplicate';
        }

        if (blank($lead->phone) && blank($lead->email)) {
            $errors[] = 'students.conversion.validation.lead_has_no_contact';
        }

        if (blank($lead->full_name) && blank($lead->first_name) && blank($lead->phone) && blank($lead->email)) {
            $errors[] = 'students.conversion.validation.lead_has_no_contact';
        }

        if (blank($lead->training_program_id) && blank($lead->course_category_id) && blank($enrollmentData['training_program_id'] ?? $enrollmentData['course_id'] ?? null) && blank($enrollmentData['course_category_id'] ?? null)) {
            $errors[] = 'students.conversion.validation.course_required';
        }

        if ($this->requiresConsent($lead) && ! $lead->consent_accepted && ! $override) {
            $errors[] = 'students.conversion.validation.consent_required';
        }

        if (! $override && ! in_array($lead->status, $this->allowedStatuses(), true)) {
            $errors[] = 'students.conversion.validation.lead_cannot_convert';
        }

        $warnings = app(BuildLeadConversionWarningsAction::class)->handle($lead);

        return [
            'can_convert' => $errors === [],
            'blocking_errors' => array_values(array_unique($errors)),
            'warnings' => $warnings,
            'suggested_data' => app(PrepareLeadConversionDataAction::class)->handle($lead),
        ];
    }

    /**
     * @return array<int, LeadStatus>
     */
    private function allowedStatuses(): array
    {
        return [
            LeadStatus::WaitingDocuments,
            LeadStatus::WaitingPayment,
            LeadStatus::ReadyToEnroll,
            LeadStatus::Contacted,
        ];
    }

    private function requiresConsent(MarketingLead $lead): bool
    {
        return in_array($lead->source, ['website', 'callback', 'contact_form'], true);
    }
}
