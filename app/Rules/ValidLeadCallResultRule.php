<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidLeadCallResultRule implements ValidationRule
{
    public const VALUES = [
        'reached',
        'no_answer',
        'wrong_number',
        'call_back_later',
        'thinking',
        'ready_to_pay',
        'refused',
    ];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (filled($value) && in_array((string) $value, self::VALUES, true)) {
            return;
        }

        $fail(tkey('crm.leads.validation.invalid_call_result'));
    }
}
