<?php

namespace App\Rules;

use App\Models\AnalyticsDashboard;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ActiveAnalyticsDashboardRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (is_int($value) || (is_string($value) && ctype_digit($value))) {
            if (AnalyticsDashboard::query()->active()->whereKey((int) $value)->exists()) {
                return;
            }
        }

        if (is_string($value) && filled($value)) {
            if (AnalyticsDashboard::query()
                ->active()
                ->where(fn ($query) => $query->where('code', $value)->orWhere('audience', $value))
                ->exists()) {
                return;
            }
        }

        $fail(tkey('analytics.validation.invalid_dashboard'));
    }
}
