<?php

namespace App\Actions;

use App\Enums\LeadStatus;
use App\Models\MarketingLead;
use App\Models\User;

class MarkLeadDuplicateAction
{
    public function __construct(private readonly MoveLeadToStatusAction $moveLead) {}

    public function handle(
        MarketingLead $lead,
        int $originalLeadId,
        ?string $comment,
        ?User $user,
    ): MarketingLead {
        $lead->forceFill(['duplicate_of_id' => $originalLeadId])->save();

        $lead = $this->moveLead->handle($lead->refresh(), LeadStatus::Duplicate, $user, $comment);

        app(RecordLeadActivityAction::class)->handle(
            $lead,
            $user,
            'marked_duplicate',
            tkey('crm.activities.types.marked_duplicate'),
            $comment,
            null,
            (string) $originalLeadId,
        );

        return $lead->refresh();
    }
}
