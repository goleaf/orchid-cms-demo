<?php

namespace App\Actions;

use App\Models\MarketingLead;
use App\Models\MarketingLeadActivity;
use App\Models\User;

class RecordLeadActivityAction
{
    /**
     * @param  array<string, mixed>|null  $meta
     */
    public function handle(
        MarketingLead $lead,
        ?User $user,
        string $type,
        ?string $title = null,
        ?string $body = null,
        ?string $oldValue = null,
        ?string $newValue = null,
        ?array $meta = null,
    ): MarketingLeadActivity {
        return $lead->activities()->create([
            'user_id' => $user?->id,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'meta' => $meta,
        ]);
    }
}
