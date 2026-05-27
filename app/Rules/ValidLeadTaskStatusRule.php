<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidLeadTaskStatusRule implements ValidationRule
{
    private const VALUES = ['open', 'in_progress', 'done', 'cancelled'];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (filled($value) && in_array((string) $value, self::VALUES, true)) {
            return;
        }

        $fail(tkey('crm.leads.validation.invalid_task_status'));
    }
}
