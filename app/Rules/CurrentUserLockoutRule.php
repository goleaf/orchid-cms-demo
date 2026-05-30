<?php

namespace App\Rules;

use App\Models\User;
use App\Models\UserStatus;
use App\Support\Security\UserLifecycle;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CurrentUserLockoutRule implements ValidationRule
{
    public function __construct(
        private readonly ?User $target,
        private readonly ?User $actor,
        private readonly ?UserStatus $targetStatus = null,
        private readonly ?bool $active = null,
        private readonly bool $allowOverride = false,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->allowOverride || ! $this->target?->exists || ! $this->actor?->exists || ! $this->target->is($this->actor)) {
            return;
        }

        if ($this->active === false || app(UserLifecycle::class)->statusLocksAccount($this->targetStatus)) {
            $fail(tkey('security.validation.current_user_lockout'));
        }
    }
}
