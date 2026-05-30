<?php

namespace App\Rules;

use App\Models\User;
use App\Support\Security\UserLifecycle;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidUserStatusTransitionRule implements ValidationRule
{
    public function __construct(
        private readonly ?User $target,
        private readonly ?User $actor = null,
        private readonly bool $override = false,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $lifecycle = app(UserLifecycle::class);
        $targetStatus = $lifecycle->status($value);

        if ($targetStatus === null || ! $this->target?->exists) {
            return;
        }

        $this->target->loadMissing('status');
        $override = $this->override || $lifecycle->actorCanOverrideStatus($this->actor);

        if (! $lifecycle->canTransition($this->target->status, $targetStatus, $override)) {
            $fail(tkey('security.validation.invalid_user_status_transition'));
        }
    }
}
