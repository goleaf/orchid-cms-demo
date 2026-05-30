<?php

namespace App\Actions\Security;

use App\Models\LoginAttempt;
use App\Models\User;
use App\Models\UserSecuritySession;
use Illuminate\Support\Facades\Schema;

class BuildUserSecuritySummaryAction
{
    /**
     * @return array<string, mixed>
     */
    public function handle(User $user): array
    {
        $user->loadMissing(['roles', 'accessibleBranches', 'status']);

        $rolePermissions = $user->roles
            ->flatMap(fn ($role) => array_keys(array_filter((array) $role->permissions)))
            ->values();
        $directPermissions = collect((array) $user->permissions)
            ->filter()
            ->keys()
            ->values();

        return [
            'user_id' => $user->getKey(),
            'display_name' => app(ResolveUserDisplayNameAction::class)->handle($user),
            'status' => app(ResolveUserStatusAction::class)->handle($user),
            'roles' => $user->roles->pluck('slug')->values()->all(),
            'permissions' => $rolePermissions->merge($directPermissions)->unique()->sort()->values()->all(),
            'branch_access' => $user->accessibleBranches
                ->map(fn ($branch): array => [
                    'id' => $branch->getKey(),
                    'name' => $branch->name,
                    'access_level' => $branch->pivot?->access_level,
                ])
                ->values()
                ->all(),
            'sessions' => $this->sessionSummary($user),
            'latest_login_attempt' => $this->latestLoginAttempt($user),
            'login_check' => app(CheckUserCanLoginAction::class)->handle($user),
        ];
    }

    /**
     * @return array<string, int>
     */
    private function sessionSummary(User $user): array
    {
        if (! Schema::hasTable('user_security_sessions')) {
            return ['active' => 0, 'revoked' => 0, 'logged_out' => 0];
        }

        return [
            'active' => UserSecuritySession::query()->byUser($user)->active()->count(),
            'revoked' => UserSecuritySession::query()->byUser($user)->revoked()->count(),
            'logged_out' => UserSecuritySession::query()->byUser($user)->loggedOut()->count(),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function latestLoginAttempt(User $user): ?array
    {
        if (! Schema::hasTable('login_attempts')) {
            return null;
        }

        $attempt = LoginAttempt::query()->byUser($user)->latestFirst()->first();

        if ($attempt === null) {
            return null;
        }

        return [
            'id' => $attempt->getKey(),
            'successful' => (bool) $attempt->successful,
            'failure_reason' => $attempt->failure_reason,
            'attempted_at' => $attempt->attempted_at,
        ];
    }
}
