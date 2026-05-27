<?php

namespace App\Actions;

use App\Models\MarketingLead;
use App\Models\MarketingLeadComment;
use App\Models\User;

class AddLeadCommentAction
{
    public function handle(MarketingLead $lead, ?User $user, string $body, bool $isInternal = true): MarketingLeadComment
    {
        $comment = $lead->comments()->create([
            'user_id' => $user?->id,
            'body' => $body,
            'is_internal' => $isInternal,
        ]);

        app(RecordLeadActivityAction::class)->handle(
            $lead,
            $user,
            'note_added',
            tkey('crm.activities.titles.note_added'),
            $body,
            null,
            null,
            ['comment_id' => $comment->id],
        );

        return $comment;
    }
}
