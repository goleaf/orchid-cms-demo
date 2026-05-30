<?php

namespace App\Rules;

use App\Models\UserSecuritySession;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UserSecuritySessionCanBeRevokedRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $session = $value instanceof UserSecuritySession
            ? $value
            : UserSecuritySession::query()->find($value);

        if ($session instanceof UserSecuritySession && $session->can_be_revoked) {
            return;
        }

        $fail(tkey('security.validation.session_cannot_be_revoked'));
    }
}
