<?php

namespace App\Rules;

use App\Models\User;
use App\Models\UserStatus;
use App\Support\Security\UserLifecycle;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class LastSuperadminUserProtectedRule implements ValidationRule
{
    /**
     * @param  array<int, mixed>|null  $roleIds
     */
    public function __construct(
        private readonly ?User $target,
        private readonly ?array $roleIds = null,
        private readonly ?UserStatus $targetStatus = null,
        private readonly ?bool $active = null,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $lifecycle = app(UserLifecycle::class);

        if (! $this->target?->exists || ! $lifecycle->isLastActiveSuperadmin($this->target)) {
            return;
        }

        $wouldLoseRole = $this->roleIds !== null && ! $lifecycle->roleIdsIncludeSuperadmin($this->roleIds);
        $wouldLockStatus = $lifecycle->statusLocksAccount($this->targetStatus);
        $wouldDeactivate = $this->active === false;

        if ($wouldLoseRole || $wouldLockStatus || $wouldDeactivate) {
            $fail(tkey('security.validation.last_superadmin_user_protected'));
        }
    }
}
