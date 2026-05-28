<?php

namespace App\Rules;

use App\Enums\ReportExportFormat;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidReportFormatRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (is_string($value) && in_array($value, ReportExportFormat::values(), true)) {
            return;
        }

        $fail(tkey('analytics.validation.invalid_format'));
    }
}
