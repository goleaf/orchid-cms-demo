<?php

namespace App\Rules;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Orchid\Platform\Models\Role;

class LastSuperadminRule implements ValidationRule
{
    /**
     * @param  array<int, mixed>|null  $roleIds
     */
    public function __construct(
        private readonly ?User $target,
        private readonly ?array $roleIds = null,
        private readonly bool $active = true,
        private readonly bool $locked = false,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $this->target?->exists || ! $this->target->isSuperadmin()) {
            return;
        }

        $wouldLoseRole = $this->roleIds !== null && ! $this->roleIdsIncludeSuperadmin($this->roleIds);

        if ($this->active && ! $this->locked && ! $wouldLoseRole) {
            return;
        }

        $remaining = User::query()
            ->activeForLogin()
            ->whereKeyNot($this->target->getKey())
            ->whereHas('roles', fn ($query) => $query->where('slug', 'superadmin'))
            ->count();

        if ($remaining === 0) {
            $fail(tkey('security.validation.last_superadmin'));
        }
    }

    /**
     * @param  array<int, mixed>  $roleIds
     */
    private function roleIdsIncludeSuperadmin(array $roleIds): bool
    {
        return Role::query()
            ->whereIn('id', collect($roleIds)->filter()->map(fn (mixed $id): int => (int) $id)->all())
            ->where('slug', 'superadmin')
            ->exists();
    }
}
