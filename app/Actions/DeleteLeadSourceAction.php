<?php

namespace App\Actions;

use App\Models\LeadSource;

class DeleteLeadSourceAction
{
    public function handle(LeadSource|int|string $source): void
    {
        $record = $source instanceof LeadSource ? $source->getKey() : $source;

        app(DeleteLeadDictionaryAction::class)->handle('sources', $record);
    }
}
