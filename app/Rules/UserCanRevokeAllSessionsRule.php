<?php

namespace App\Rules;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UserCanRevokeAllSessionsRule implements ValidationRule
{
    public function __construct(private readonly ?User $actor) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->actor?->hasAccess('security.sessions.revoke_all') || $this->actor?->isSuperadmin()) {
            return;
        }

        $fail(tkey('security.validation.user_cannot_revoke_all_sessions'));
    }
}
