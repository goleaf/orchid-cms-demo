<?php

namespace App\Rules;

use App\Models\DashboardWidget;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class DashboardWidgetCodeRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (is_string($value) && DashboardWidget::query()->active()->where('code', $value)->exists()) {
            return;
        }

        $fail(tkey('analytics.validation.invalid_widget'));
    }
}
