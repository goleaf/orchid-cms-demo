<?php

namespace App\Rules;

use App\Enums\LeadTaskPriority;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidLeadPriorityRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (filled($value) && in_array((string) $value, array_map(fn (LeadTaskPriority $priority): string => $priority->value, LeadTaskPriority::cases()), true)) {
            return;
        }

        $fail(tkey('crm.leads.validation.invalid_priority'));
    }
}
