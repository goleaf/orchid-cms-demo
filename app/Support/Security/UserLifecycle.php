<?php

namespace App\Support\Security;

use App\Models\User;
use App\Models\UserStatus;
use Illuminate\Support\Facades\Schema;
use Orchid\Platform\Models\Role;

class UserLifecycle
{
    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    public const STATUS_BLOCKED = 'blocked';

    public const STATUS_ARCHIVED = 'archived';

    /**
     * @return array<string, array<int, string>>
     */
    public function allowedTransitions(): array
    {
        return [
            self::STATUS_ACTIVE => [self::STATUS_INACTIVE, self::STATUS_BLOCKED, self::STATUS_ARCHIVED],
            self::STATUS_INACTIVE => [self::STATUS_ACTIVE, self::STATUS_BLOCKED, self::STATUS_ARCHIVED],
            self::STATUS_BLOCKED => [self::STATUS_ACTIVE, self::STATUS_ARCHIVED],
            self::STATUS_ARCHIVED => [],
        ];
    }

    public function status(string|int|null|UserStatus $status): ?UserStatus
    {
        if ($status instanceof UserStatus) {
            return $status;
        }

        if (blank($status) || ! Schema::hasTable('user_statuses')) {
            return null;
        }

        return UserStatus::query()
            ->when(is_numeric($status), fn ($query) => $query->whereKey((int) $status))
            ->when(! is_numeric($status), fn ($query) => $query->where('code', (string) $status))
            ->first();
    }

    public function defaultStatus(): ?UserStatus
    {
        if (! Schema::hasTable('user_statuses')) {
            return null;
        }

        return UserStatus::query()->default()->first()
            ?: UserStatus::query()->where('code', self::STATUS_ACTIVE)->first();
    }

    public function statusByCode(string $code): ?UserStatus
    {
        if (! Schema::hasTable('user_statuses')) {
            return null;
        }

        return UserStatus::query()->where('code', $code)->first();
    }

    public function statusLocksAccount(?UserStatus $status): bool
    {
        return (bool) ($status?->is_blocked || $status?->is_archived);
    }

    public function isInactiveStatus(?UserStatus $status): bool
    {
        return $status?->code === self::STATUS_INACTIVE;
    }

    public function canTransition(?UserStatus $from, UserStatus $to, bool $override = false): bool
    {
        if ($override) {
            return true;
        }

        if ($from === null) {
            return $to->code !== self::STATUS_ARCHIVED;
        }

        if ($from->getKey() === $to->getKey()) {
            return true;
        }

        return in_array($to->code, $this->allowedTransitions()[$from->code] ?? [], true);
    }

    public function roleIdsIncludeSuperadmin(array $roleIds): bool
    {
        $ids = collect($roleIds)
            ->filter(fn (mixed $id): bool => is_numeric($id))
            ->map(fn (mixed $id): int => (int) $id)
            ->values()
            ->all();

        if ($ids === []) {
            return false;
        }

        return Role::query()
            ->whereIn('id', $ids)
            ->where('slug', 'superadmin')
            ->exists();
    }

    public function activeSuperadminCountExcept(User $user): int
    {
        return User::query()
            ->activeForLogin()
            ->whereKeyNot($user->getKey())
            ->whereHas('roles', fn ($query) => $query->where('slug', 'superadmin'))
            ->count();
    }

    public function isLastActiveSuperadmin(User $user): bool
    {
        return $user->exists
            && $user->isSuperadmin()
            && $this->activeSuperadminCountExcept($user) === 0;
    }

    public function actorCanOverrideStatus(?User $actor): bool
    {
        return (bool) ($actor?->hasAccess('security.users.override_status_transition') || $actor?->isSuperadmin());
    }

    public function actorCanManageUsers(?User $actor, string $permission): bool
    {
        return (bool) ($actor?->hasAnyAccess([$permission, 'platform.systems.users']) || $actor?->isSuperadmin());
    }

    /**
     * @return array<string, mixed>
     */
    public function userSnapshot(User $user): array
    {
        return $user->only([
            'name',
            'email',
            'status_id',
            'preferred_locale',
            'timezone',
            'is_active',
            'security_locked_at',
            'security_lock_reason',
            'last_login_at',
            'last_seen_at',
            'password_changed_at',
            'must_change_password',
        ]);
    }
}
