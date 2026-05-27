<?php

namespace App\Http\Requests\Marketing;

class StoreLeadSourceRequest extends LeadDictionaryRequest
{
    public function dictionaryName(): string
    {
        return 'sources';
    }
}
