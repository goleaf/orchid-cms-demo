<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Orchid\Platform\Models\Role;

class SuperadminRoleProtectedRule implements ValidationRule
{
    public function __construct(private readonly ?Role $role) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->role?->exists && $this->role->slug === 'superadmin' && (string) $value !== 'superadmin') {
            $fail(tkey('security.validation.superadmin_role_protected'));
        }
    }
}
