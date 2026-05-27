<?php

namespace App\Actions;

use App\Enums\LeadStatus;
use App\Models\MarketingLead;
use App\Models\User;

class PrepareLeadForEnrollmentAction
{
    public function __construct(private readonly MoveLeadToStatusAction $moveLead) {}

    public function handle(MarketingLead $lead, ?User $user): MarketingLead
    {
        return $this->moveLead->handle(
            $lead,
            LeadStatus::ReadyToEnroll,
            $user,
            tkey('crm.activities.reasons.ready_to_enroll'),
        );
    }
}
