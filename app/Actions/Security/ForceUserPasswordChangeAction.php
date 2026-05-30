<?php

namespace App\Actions\Security;

use App\Models\User;
use App\Rules\UserCanForcePasswordChangeRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class ForceUserPasswordChangeAction
{
    public function handle(User $user, ?User $actor = null, ?Request $request = null, bool $revokeSessions = false): User
    {
        Validator::make(['user' => $user->getKey()], [
            'user' => [new UserCanForcePasswordChangeRule($user)],
        ])->validate();

        $before = $user->only(['must_change_password']);

        if (Schema::hasColumn('users', 'must_change_password')) {
            $user->forceFill(['must_change_password' => true])->save();
        }

        $revoked = $revokeSessions
            ? app(RevokeUserAccessOnBlockAction::class)->handle($user, $actor, $request, 'force_password_change')
            : 0;

        app(RecordAuditLogAction::class)->handle(
            'user_force_password_change',
            $actor,
            $user,
            $before,
            $user->only(['must_change_password']),
            ['sessions_revoked' => $revoked],
            $request,
        );

        app(RecordSecurityEventAction::class)->handle(
            'user_force_password_change',
            $user,
            'high',
            ['actor_id' => $actor?->getKey(), 'sessions_revoked' => $revoked],
            $request,
        );

        return $user->refresh();
    }
}
