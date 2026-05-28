<?php

namespace App\Rules;

use App\Enums\LeadTaskStatus;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidLeadTaskStatusRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (filled($value) && in_array((string) $value, array_map(fn (LeadTaskStatus $status): string => $status->value, LeadTaskStatus::cases()), true)) {
            return;
        }

        $fail(tkey('crm.leads.validation.invalid_task_status'));
    }
}
