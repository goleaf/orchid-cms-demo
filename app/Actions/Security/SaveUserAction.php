<?php

namespace App\Actions\Security;

use App\Models\User;
use App\Models\UserStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Orchid\Platform\Models\Role;

class SaveUserAction
{
    public function handle(User $user, Request $request, ?User $actor = null): User
    {
        return DB::transaction(function () use ($user, $request, $actor): User {
            $user->loadMissing('roles');
            $before = $user->exists ? $user->only([
                'name',
                'email',
                'status_id',
                'permissions',
                'preferred_locale',
                'timezone',
                'is_active',
                'security_locked_at',
                'security_lock_reason',
                'last_login_at',
                'last_seen_at',
                'password_changed_at',
                'must_change_password',
                'two_factor_placeholder_enabled',
            ]) : [];

            $payload = $request->input('user', []);
            $attributes = Arr::only($payload, [
                'name',
                'email',
                'status_id',
                'preferred_locale',
                'timezone',
                'is_active',
                'security_locked_at',
                'security_lock_reason',
                'must_change_password',
                'two_factor_placeholder_enabled',
            ]);

            if (! $user->exists && ! array_key_exists('is_active', $attributes)) {
                $attributes['is_active'] = true;
            }

            if (
                ! $user->exists
                && blank($attributes['status_id'] ?? null)
                && Schema::hasTable('user_statuses')
                && Schema::hasColumn('users', 'status_id')
            ) {
                $attributes['status_id'] = UserStatus::query()->default()->value('id')
                    ?: UserStatus::query()->where('code', 'active')->value('id');
            }

            if (array_key_exists('is_active', $attributes)) {
                $attributes['is_active'] = filter_var($attributes['is_active'], FILTER_VALIDATE_BOOLEAN);
            }

            if (array_key_exists('must_change_password', $attributes)) {
                $attributes['must_change_password'] = filter_var($attributes['must_change_password'], FILTER_VALIDATE_BOOLEAN);
            }

            if (($payload['two_factor_placeholder_enabled'] ?? false) === true || ($payload['two_factor_placeholder_enabled'] ?? null) === '1') {
                app(ConfigureTwoFactorPlaceholderAction::class)->handle($user, true, $actor, $request);
            }

            if (filled($payload['password'] ?? null)) {
                $attributes['password'] = Hash::make((string) $payload['password']);
                $attributes['password_changed_at'] = now();
            }

            $this->guardLastSuperadmin($user, $payload);

            $user->fill($attributes);

            if ($request->has('permissions') || ! $user->exists) {
                $user->forceFill(['permissions' => $this->decodePermissions($request->input('permissions', []))]);
            }

            $user->save();

            if (array_key_exists('roles', $payload)) {
                $user->replaceRoles($payload['roles'] ?? []);
            }

            app(AssignUserBranchAccessAction::class)->handle(
                $user,
                array_key_exists('branch_ids', $payload) ? (array) $payload['branch_ids'] : null,
                $actor,
            );

            $user->refresh()->loadMissing(['roles', 'accessibleBranches']);

            app(RecordAuditLogAction::class)->handle(
                $before === [] ? 'user.created' : 'user.updated',
                $actor,
                $user,
                $before,
                $user->only([
                    'name',
                    'email',
                    'status_id',
                    'permissions',
                    'preferred_locale',
                    'timezone',
                    'is_active',
                    'security_locked_at',
                    'security_lock_reason',
                    'last_login_at',
                    'last_seen_at',
                    'password_changed_at',
                    'must_change_password',
                    'two_factor_placeholder_enabled',
                ]),
                [
                    'role_slugs' => $user->roles->pluck('slug')->values()->all(),
                    'branch_ids' => $user->accessibleBranches->pluck('id')->values()->all(),
                ],
                $request,
            );

            app(RecordSecurityEventAction::class)->handle(
                'user.security_profile_saved',
                $user,
                'info',
                ['actor_id' => $actor?->getKey()],
                $request,
            );

            return $user;
        });
    }

    /**
     * @param  array<string, mixed>  $payload
     *
     * @throws ValidationException
     */
    private function guardLastSuperadmin(User $user, array $payload): void
    {
        if (! $user->exists || ! $user->isSuperadmin()) {
            return;
        }

        $wouldDisable = array_key_exists('is_active', $payload)
            && ! filter_var($payload['is_active'], FILTER_VALIDATE_BOOLEAN);
        $wouldLock = filled($payload['security_locked_at'] ?? null);
        $wouldStatusLock = array_key_exists('status_id', $payload)
            && $this->statusLocksAccount($payload['status_id']);
        $wouldLoseRole = array_key_exists('roles', $payload)
            && ! $this->roleIdsIncludeSuperadmin((array) ($payload['roles'] ?? []));

        if (! ($wouldDisable || $wouldLock || $wouldStatusLock || $wouldLoseRole)) {
            return;
        }

        if ($this->activeSuperadminCountExcept($user) === 0) {
            throw ValidationException::withMessages([
                'user.roles' => tkey('security.validation.last_superadmin'),
            ]);
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

    private function activeSuperadminCountExcept(User $user): int
    {
        return User::query()
            ->activeForLogin()
            ->whereKeyNot($user->getKey())
            ->whereHas('roles', fn ($query) => $query->where('slug', 'superadmin'))
            ->count();
    }

    private function statusLocksAccount(mixed $statusId): bool
    {
        if (blank($statusId)) {
            return false;
        }

        if (! Schema::hasTable('user_statuses')) {
            return false;
        }

        $status = UserStatus::query()->find($statusId);

        return (bool) ($status?->is_blocked || $status?->is_archived);
    }

    /**
     * @param  array<string, mixed>|null  $permissions
     * @return array<string, bool>
     */
    private function decodePermissions(?array $permissions): array
    {
        return collect($permissions ?? [])
            ->map(fn (mixed $value, string|int $key): array => [base64_decode((string) $key) => filter_var($value, FILTER_VALIDATE_BOOLEAN)])
            ->collapse()
            ->all();
    }
}
