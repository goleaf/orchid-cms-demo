<?php

namespace App\Http\Requests\Marketing;

class StoreLeadTagRequest extends LeadDictionaryRequest
{
    public function dictionaryName(): string
    {
        return 'tags';
    }
}
