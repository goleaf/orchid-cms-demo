<?php

namespace App\Actions;

use App\Support\Crm\LeadDictionaryRegistry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class DeleteLeadDictionaryAction
{
    public function handle(string $dictionary, int|string $record): void
    {
        $definition = LeadDictionaryRegistry::definition($dictionary);

        /** @var class-string<Model> $modelClass */
        $modelClass = $definition['model'];
        $item = $modelClass::query()->findOrFail($record);

        if ((bool) $item->getAttribute('is_system')) {
            throw ValidationException::withMessages([
                'record' => tkey('crm.validation.dictionary_system_record_locked'),
            ]);
        }

        $item->delete();
    }
}
