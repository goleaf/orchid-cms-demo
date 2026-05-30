<?php

namespace App\Rules;

use App\Models\User;
use App\Support\Security\UserLifecycle;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UserCanBeBlockedRule implements ValidationRule
{
    public function __construct(
        private readonly ?User $target,
        private readonly ?User $actor = null,
        private readonly bool $overrideSelf = false,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $this->target?->exists) {
            return;
        }

        $lifecycle = app(UserLifecycle::class);

        if ($lifecycle->isLastActiveSuperadmin($this->target)) {
            $fail(tkey('security.validation.user_cannot_be_blocked'));

            return;
        }

        if ($this->actor?->exists && $this->target->is($this->actor) && ! $this->overrideSelf) {
            $fail(tkey('security.validation.current_user_lockout'));
        }
    }
}
