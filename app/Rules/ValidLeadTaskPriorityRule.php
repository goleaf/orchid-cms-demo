<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidLeadTaskPriorityRule implements ValidationRule
{
    private const VALUES = ['low', 'normal', 'high', 'urgent'];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (filled($value) && in_array((string) $value, self::VALUES, true)) {
            return;
        }

        $fail(tkey('crm.leads.validation.invalid_task_priority'));
    }
}
