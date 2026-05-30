<?php

namespace App\Rules;

use App\Models\User;
use App\Support\Security\UserLifecycle;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UserCanBeUpdatedRule implements ValidationRule
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        private readonly ?User $target,
        private readonly ?User $actor = null,
        private readonly array $payload = [],
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $this->target?->exists) {
            return;
        }

        $lifecycle = app(UserLifecycle::class);
        $targetStatus = array_key_exists('status_id', $this->payload)
            ? $lifecycle->status($this->payload['status_id'])
            : null;

        $wouldDeactivate = array_key_exists('is_active', $this->payload)
            && ! filter_var($this->payload['is_active'], FILTER_VALIDATE_BOOLEAN);
        $wouldRemoveSuperadmin = array_key_exists('roles', $this->payload)
            && ! $lifecycle->roleIdsIncludeSuperadmin((array) $this->payload['roles']);

        if (
            $lifecycle->isLastActiveSuperadmin($this->target)
            && ($wouldDeactivate || $wouldRemoveSuperadmin || $lifecycle->statusLocksAccount($targetStatus))
        ) {
            $fail(tkey('security.validation.user_cannot_be_updated'));

            return;
        }

        if (
            $this->actor?->exists
            && $this->target->is($this->actor)
            && ($wouldDeactivate || $lifecycle->statusLocksAccount($targetStatus))
            && ! $lifecycle->actorCanOverrideStatus($this->actor)
        ) {
            $fail(tkey('security.validation.current_user_lockout'));
        }
    }
}
