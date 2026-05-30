<?php

namespace App\Actions\Security;

use App\Models\User;
use App\Rules\UserCanBeArchivedRule;
use App\Support\Security\UserLifecycle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ArchiveUserAction
{
    public function handle(User $user, ?User $actor = null, ?Request $request = null, bool $overrideSelf = false): User
    {
        Validator::make(['user' => $user->getKey()], [
            'user' => [new UserCanBeArchivedRule($user, $actor, $overrideSelf)],
        ])->validate();

        $before = app(UserLifecycle::class)->userSnapshot($user);
        $status = app(UserLifecycle::class)->statusByCode(UserLifecycle::STATUS_ARCHIVED);

        if ($status !== null) {
            $user = app(ChangeUserStatusAction::class)->handle($user, $status, $actor, $request);
        } else {
            $user->forceFill(['is_active' => false, 'security_locked_at' => now(), 'security_lock_reason' => 'archived'])->save();
            $user->refresh();
        }

        $revoked = app(RevokeUserAccessOnBlockAction::class)->handle($user, $actor, $request, 'archived');

        app(RecordAuditLogAction::class)->handle(
            'user_archived',
            $actor,
            $user,
            $before,
            app(UserLifecycle::class)->userSnapshot($user),
            ['sessions_revoked' => $revoked],
            $request,
        );

        app(RecordSecurityEventAction::class)->handle(
            'user_archived',
            $user,
            'high',
            ['actor_id' => $actor?->getKey(), 'sessions_revoked' => $revoked],
            $request,
        );

        return $user->refresh()->loadMissing('status');
    }
}
