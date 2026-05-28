<?php

namespace App\Actions;

use App\Rules\DictionaryItemCanBeDeletedRule;
use App\Support\Crm\LeadDictionaryRegistry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class DeleteLeadDictionaryAction
{
    public function handle(string $dictionary, int|string $record): void
    {
        $definition = LeadDictionaryRegistry::definition($dictionary);

        /** @var class-string<Model> $modelClass */
        $modelClass = $definition['model'];
        $item = $modelClass::query()->findOrFail($record);
        $validator = Validator::make(
            ['record' => $item->getKey()],
            ['record' => [new DictionaryItemCanBeDeletedRule($dictionary)]],
        );

        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->toArray());
        }

        $item->delete();
    }
}
