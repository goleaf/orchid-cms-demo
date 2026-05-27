<?php

namespace App\Actions;

use App\Models\MarketingLead;
use App\Models\MarketingLeadComment;
use App\Models\User;

class AddLeadCommentAction
{
    public function handle(MarketingLead $lead, ?User $user, string $body, bool $isInternal = true): MarketingLeadComment
    {
        return $lead->comments()->create([
            'user_id' => $user?->id,
            'body' => $body,
            'is_internal' => $isInternal,
        ]);
    }
}
