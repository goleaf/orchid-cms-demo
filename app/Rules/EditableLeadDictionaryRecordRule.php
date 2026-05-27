<?php

namespace App\Rules;

use App\Support\Crm\LeadDictionaryRegistry;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Model;

class EditableLeadDictionaryRecordRule implements ValidationRule
{
    public function __construct(private readonly string $dictionary) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! filled($value)) {
            return;
        }

        $definition = LeadDictionaryRegistry::definition($this->dictionary);

        /** @var class-string<Model> $modelClass */
        $modelClass = $definition['model'];
        $item = $modelClass::query()->find($value);

        if ($item === null) {
            $fail(tkey('crm.validation.dictionary_record_unavailable'));

            return;
        }

        if ((bool) $item->getAttribute('is_system')) {
            $fail(tkey('crm.validation.dictionary_system_record_locked'));
        }
    }
}
