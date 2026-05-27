<?php

namespace App\Actions;

use App\Enums\LeadStatus;
use App\Models\MarketingLead;
use App\Models\User;

class MarkLeadSpamAction
{
    public function handle(MarketingLead $lead, ?User $user, ?string $comment = null): MarketingLead
    {
        $lead = app(ChangeLeadStatusAction::class)->handle(
            $lead,
            LeadStatus::Spam,
            $user,
            $comment ?? tkey('crm.activities.reasons.marked_spam'),
            allowOverride: true,
        );

        app(RecordLeadActivityAction::class)->handle(
            $lead,
            $user,
            'marked_spam',
            tkey('crm.activities.types.marked_spam'),
            $comment,
        );

        return $lead->refresh();
    }
}
