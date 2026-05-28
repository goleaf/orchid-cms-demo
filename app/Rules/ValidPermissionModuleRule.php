<?php

namespace App\Rules;

use App\Models\PermissionRegistryItem;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidPermissionModuleRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (blank($value) || (is_string($value) && in_array($value, PermissionRegistryItem::MODULES, true))) {
            return;
        }

        $fail(tkey('security.validation.invalid_permission_module'));
    }
}
