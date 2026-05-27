<?php

namespace App\Actions;

use App\Models\MarketingLead;
use App\Models\MarketingLeadComment;
use App\Models\User;

class AddLeadNoteAction
{
    public function handle(MarketingLead $lead, ?User $user, string $body): MarketingLeadComment
    {
        return app(AddLeadCommentAction::class)->handle($lead, $user, $body, true);
    }
}
