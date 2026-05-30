<?php

namespace App\Actions\Security;

use App\Models\User;
use App\Rules\UserCanBeBlockedRule;
use App\Support\Security\UserLifecycle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BlockUserAction
{
    public function handle(User $user, ?User $actor = null, ?Request $request = null, bool $overrideSelf = false): User
    {
        Validator::make(['user' => $user->getKey()], [
            'user' => [new UserCanBeBlockedRule($user, $actor, $overrideSelf)],
        ])->validate();

        $status = app(UserLifecycle::class)->statusByCode(UserLifecycle::STATUS_BLOCKED);
        $before = app(UserLifecycle::class)->userSnapshot($user);

        if ($status !== null) {
            $user = app(ChangeUserStatusAction::class)->handle($user, $status, $actor, $request);
        } else {
            $user->forceFill(['is_active' => false, 'security_locked_at' => now(), 'security_lock_reason' => 'blocked'])->save();
            $user->refresh();
        }

        $revoked = app(RevokeUserAccessOnBlockAction::class)->handle($user, $actor, $request, 'blocked');

        app(RecordAuditLogAction::class)->handle(
            'user_blocked',
            $actor,
            $user,
            $before,
            app(UserLifecycle::class)->userSnapshot($user),
            ['sessions_revoked' => $revoked],
            $request,
        );

        app(RecordSecurityEventAction::class)->handle(
            'user_blocked',
            $user,
            'high',
            ['actor_id' => $actor?->getKey(), 'sessions_revoked' => $revoked],
            $request,
        );

        return $user->refresh()->loadMissing('status');
    }
}
