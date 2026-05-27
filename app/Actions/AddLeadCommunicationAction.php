<?php

namespace App\Actions;

use App\Models\MarketingLead;
use App\Models\MarketingLeadCommunication;
use App\Models\User;

class AddLeadCommunicationAction
{
    /**
     * @param  array<string, mixed>|null  $metadata
     */
    public function handle(
        MarketingLead $lead,
        ?User $user,
        string $channel,
        string $direction,
        ?string $subject,
        ?string $body,
        ?array $metadata = null,
    ): MarketingLeadCommunication {
        return $lead->communications()->create([
            'user_id' => $user?->id,
            'channel' => $channel,
            'direction' => $direction,
            'subject' => $subject,
            'body' => $body,
            'communicated_at' => now(),
            'metadata' => $metadata,
        ]);
    }
}
