<?php

namespace App\Rules;

use App\Models\KpiMetric;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ActiveKpiMetricRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (filled($value) && KpiMetric::query()->active()->whereKey($value)->exists()) {
            return;
        }

        $fail(tkey('analytics.validation.inactive_metric'));
    }
}
