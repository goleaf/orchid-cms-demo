<?php

namespace App\Http\Requests\Marketing;

class StoreLeadLostReasonRequest extends LeadDictionaryRequest
{
    public function dictionaryName(): string
    {
        return 'lost-reasons';
    }
}
