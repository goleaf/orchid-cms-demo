<?php

namespace App\Actions;

use App\Models\LeadLostReason;

class CreateOrUpdateLeadLostReasonAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(LeadLostReason|int|string|null $reason, array $data): LeadLostReason
    {
        $record = $reason instanceof LeadLostReason ? $reason->getKey() : $reason;

        /** @var LeadLostReason $model */
        $model = app(SaveLeadDictionaryAction::class)->handle('lost-reasons', $record, $data);

        return $model;
    }
}
