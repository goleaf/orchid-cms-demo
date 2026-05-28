<?php

namespace App\Rules;

use App\Models\PermissionRegistryItem;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SystemPermissionCodeProtectedRule implements ValidationRule
{
    public function __construct(private readonly ?PermissionRegistryItem $item = null) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->item?->exists && $this->item->is_system && (string) $value !== $this->item->code) {
            $fail(tkey('security.validation.system_permission_code_protected'));
        }
    }
}
