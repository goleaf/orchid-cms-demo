<?php

namespace App\Rules;

use App\Models\PermissionRegistryItem;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;

class CriticalPermissionRequiresSuperadminRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ((string) $value !== PermissionRegistryItem::RISK_CRITICAL) {
            return;
        }

        $user = Auth::user();

        if ($user instanceof User && $user->isSuperadmin()) {
            return;
        }

        $fail(tkey('security.validation.critical_permission_requires_superadmin'));
    }
}
