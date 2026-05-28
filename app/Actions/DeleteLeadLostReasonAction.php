<?php

namespace App\Actions;

use App\Models\LeadLostReason;

class DeleteLeadLostReasonAction
{
    public function handle(LeadLostReason|int|string $reason): void
    {
        $record = $reason instanceof LeadLostReason ? $reason->getKey() : $reason;

        app(DeleteLeadDictionaryAction::class)->handle('lost-reasons', $record);
    }
}
