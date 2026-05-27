<?php

namespace App\Http\Requests\Marketing;

class StoreLeadStatusRequest extends LeadDictionaryRequest
{
    public function dictionaryName(): string
    {
        return 'statuses';
    }
}
