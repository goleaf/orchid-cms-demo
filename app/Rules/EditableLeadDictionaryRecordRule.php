<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class EditableLeadDictionaryRecordRule implements ValidationRule
{
    public function __construct(private readonly string $dictionary) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        (new DictionaryItemCanBeDeletedRule($this->dictionary))->validate($attribute, $value, $fail);
    }
}
