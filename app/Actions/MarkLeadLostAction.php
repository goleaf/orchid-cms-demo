<?php

namespace App\Actions;

use App\Enums\LeadStatus;
use App\Models\MarketingLead;
use App\Models\User;

class MarkLeadLostAction
{
    public function __construct(private readonly MoveLeadToStatusAction $moveLead) {}

    public function handle(
        MarketingLead $lead,
        string $lostReasonCode,
        ?string $comment,
        ?User $user,
    ): MarketingLead {
        $lead->fill([
            'lost_reason_code' => $lostReasonCode,
            'rejection_reason' => $comment,
        ])->save();

        $lead = $this->moveLead->handle($lead->refresh(), LeadStatus::Lost, $user, $comment);

        app(RecordLeadActivityAction::class)->handle(
            $lead,
            $user,
            'marked_lost',
            tkey('crm.activities.types.marked_lost'),
            $comment,
            null,
            $lostReasonCode,
        );

        return $lead->refresh();
    }
}
