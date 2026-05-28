<?php

namespace App\Rules;

use App\Actions\Security\ImportExistingOrchidPermissionsAction;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Throwable;

class PermissionCodeExistsRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (blank($value)) {
            return;
        }

        try {
            if (in_array((string) $value, app(ImportExistingOrchidPermissionsAction::class)->codes(), true)) {
                return;
            }
        } catch (Throwable) {
        }

        $fail(tkey('security.validation.permission_code_not_found'));
    }
}
