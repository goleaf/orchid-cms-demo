<?php

namespace App\Rules;

use App\Models\ReportDefinition;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ActiveReportDefinitionRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (filled($value) && ReportDefinition::query()->active()->whereKey($value)->exists()) {
            return;
        }

        $fail(tkey('analytics.validation.inactive_report'));
    }
}
