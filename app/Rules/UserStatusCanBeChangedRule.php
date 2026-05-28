<?php

namespace App\Rules;

use App\Models\User;
use App\Models\UserStatus;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UserStatusCanBeChangedRule implements ValidationRule
{
    public function __construct(
        private readonly ?User $target = null,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (blank($value) || ! $this->target?->exists || ! $this->target->isSuperadmin()) {
            return;
        }

        $status = $this->status($value);

        if ($status === null || ! ($status->is_blocked || $status->is_archived)) {
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

    private function status(mixed $value): ?UserStatus
    {
        if ($value instanceof UserStatus) {
            return $value;
        }

        return UserStatus::query()
            ->when(is_numeric($value), fn ($query) => $query->whereKey((int) $value))
            ->when(! is_numeric($value), fn ($query) => $query->where('code', (string) $value))
            ->first();
    }
}
