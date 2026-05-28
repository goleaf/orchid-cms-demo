<?php

namespace App\Rules;

use App\Models\PermissionRegistryItem;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidPermissionRiskLevelRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (is_string($value) && in_array($value, PermissionRegistryItem::RISK_LEVELS, true)) {
            return;
        }

        $fail(tkey('security.validation.invalid_permission_risk_level'));
    }
}
