<?php

namespace App\Actions;

use App\Models\LeadStatus;

class DeleteLeadStatusAction
{
    public function handle(LeadStatus|int|string $status): void
    {
        $record = $status instanceof LeadStatus ? $status->getKey() : $status;

        app(DeleteLeadDictionaryAction::class)->handle('statuses', $record);
    }
}
