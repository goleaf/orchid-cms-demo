<?php

namespace App\Actions;

use App\Models\MarketingLead;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class PrepareLeadForStudentConversionAction
{
    /**
     * @return array{ready: bool, lead: MarketingLead, message: string}
     */
    public function handle(MarketingLead $lead, ?User $user = null): array
    {
        if (! $lead->can_be_converted) {
            throw ValidationException::withMessages([
                'lead' => tkey('crm.leads.validation.lead_cannot_be_converted'),
            ]);
        }

        $lead = app(PrepareLeadForEnrollmentAction::class)->handle($lead, $user);

        return [
            'ready' => true,
            'lead' => $lead,
            'message' => tkey('crm.leads.messages.student_module_next_block'),
        ];
    }
}
