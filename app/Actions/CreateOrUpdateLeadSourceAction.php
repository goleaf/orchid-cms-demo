<?php

namespace App\Actions;

use App\Models\LeadSource;

class CreateOrUpdateLeadSourceAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(LeadSource|int|string|null $source, array $data): LeadSource
    {
        $record = $source instanceof LeadSource ? $source->getKey() : $source;

        /** @var LeadSource $model */
        $model = app(SaveLeadDictionaryAction::class)->handle('sources', $record, $data);

        return $model;
    }
}
