<?php

namespace App\Actions;

use App\Models\LeadStatus;

class CreateOrUpdateLeadStatusAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(LeadStatus|int|string|null $status, array $data): LeadStatus
    {
        $record = $status instanceof LeadStatus ? $status->getKey() : $status;

        /** @var LeadStatus $model */
        $model = app(SaveLeadDictionaryAction::class)->handle('statuses', $record, $data);

        return $model;
    }
}
