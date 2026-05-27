<?php

namespace App\Actions;

use App\Models\LeadTag;

class CreateOrUpdateLeadTagAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(LeadTag|int|string|null $tag, array $data): LeadTag
    {
        $record = $tag instanceof LeadTag ? $tag->getKey() : $tag;

        /** @var LeadTag $model */
        $model = app(SaveLeadDictionaryAction::class)->handle('tags', $record, $data);

        return $model;
    }
}
