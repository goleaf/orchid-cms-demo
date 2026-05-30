<?php

namespace App\Rules;

use App\Models\User;
use App\Models\UserSecuritySession;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UserCanRevokeSessionRule implements ValidationRule
{
    public function __construct(private readonly ?User $actor) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $session = $value instanceof UserSecuritySession
            ? $value
            : UserSecuritySession::query()->find($value);

        if (! $this->actor instanceof User || ! $session instanceof UserSecuritySession) {
            $fail(tkey('security.validation.user_cannot_revoke_session'));

            return;
        }

        if (
            $this->actor->hasAccess('security.sessions.revoke')
            || ($this->actor->hasAccess('security.sessions.revoke_own') && (int) $session->user_id === (int) $this->actor->getKey())
            || $this->actor->isSuperadmin()
        ) {
            return;
        }

        $fail(tkey('security.validation.user_cannot_revoke_session'));
    }
}
