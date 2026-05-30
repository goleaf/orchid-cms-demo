<?php

namespace App\Actions\Security;

use App\Models\User;
use App\Support\Security\UserLifecycle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Orchid\Platform\Models\Role;

class SyncUserRolesAction
{
    /**
     * @param  array<int, mixed>  $roles
     */
    public function handle(User $user, array $roles, ?User $actor = null, ?Request $request = null): User
    {
        return DB::transaction(function () use ($user, $roles, $actor, $request): User {
            $user->loadMissing('roles');
            $before = $user->roles->pluck('slug')->values()->all();
            $roleIds = $this->roleIds($roles);

            if (
                app(UserLifecycle::class)->isLastActiveSuperadmin($user)
                && ! app(UserLifecycle::class)->roleIdsIncludeSuperadmin($roleIds)
            ) {
                throw ValidationException::withMessages([
                    'roles' => tkey('security.validation.last_superadmin_user_protected'),
                ]);
            }

            $user->roles()->sync($roleIds);
            $user->refresh()->load('roles');

            app(RecordAuditLogAction::class)->handle(
                'user_roles_synced',
                $actor,
                $user,
                ['role_slugs' => $before],
                ['role_slugs' => $user->roles->pluck('slug')->values()->all()],
                [],
                $request,
            );

            return $user;
        });
    }

    /**
     * @param  array<int, mixed>  $roles
     * @return array<int, int>
     */
    private function roleIds(array $roles): array
    {
        $numericIds = collect($roles)
            ->filter(fn (mixed $role): bool => is_numeric($role))
            ->map(fn (mixed $role): int => (int) $role)
            ->values();

        $slugs = collect($roles)
            ->filter(fn (mixed $role): bool => is_string($role) && ! is_numeric($role))
            ->map(fn (string $role): string => trim($role))
            ->filter()
            ->values();

        $slugIds = $slugs->isEmpty()
            ? collect()
            : Role::query()->whereIn('slug', $slugs->all())->pluck('id');

        return $numericIds
            ->merge($slugIds)
            ->map(fn (mixed $id): int => (int) $id)
            ->unique()
            ->values()
            ->all();
    }
}
