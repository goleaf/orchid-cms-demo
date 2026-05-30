<?php

namespace App\Rules;

use App\Models\User;
use App\Support\Security\UserLifecycle;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UserCanBeArchivedRule implements ValidationRule
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

        if (app(UserLifecycle::class)->isLastActiveSuperadmin($this->target)) {
            $fail(tkey('security.validation.user_cannot_be_archived'));

            return;
        }

        if ($this->actor?->exists && $this->target->is($this->actor) && ! $this->overrideSelf) {
            $fail(tkey('security.validation.current_user_lockout'));
        }
    }
}
