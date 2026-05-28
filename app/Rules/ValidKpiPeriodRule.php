<?php

namespace App\Rules;

use App\Enums\KpiPeriod;
use App\Rules\Concerns\InteractsWithAnalyticsValidation;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidKpiPeriodRule implements ValidationRule
{
    use InteractsWithAnalyticsValidation;

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $value = $this->enumValue($value);

        if (is_string($value) && in_array($value, KpiPeriod::values(), true)) {
            return;
        }

        $fail(tkey('analytics.validation.invalid_period'));
    }
}
