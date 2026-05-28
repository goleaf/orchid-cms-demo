<?php

namespace App\Actions;

use App\Models\LeadTag;

class DeleteLeadTagAction
{
    public function handle(LeadTag|int|string $tag): void
    {
        $record = $tag instanceof LeadTag ? $tag->getKey() : $tag;

        app(DeleteLeadDictionaryAction::class)->handle('tags', $record);
    }
}
