<?php

namespace App\Actions\Security;

use App\Models\User;
use App\Rules\UserCanBeUnblockedRule;
use App\Support\Security\UserLifecycle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UnblockUserAction
{
    public function handle(User $user, ?User $actor = null, ?Request $request = null): User
    {
        Validator::make(['user' => $user->getKey()], [
            'user' => [new UserCanBeUnblockedRule($user)],
        ])->validate();

        $before = app(UserLifecycle::class)->userSnapshot($user);
        $status = app(UserLifecycle::class)->statusByCode(UserLifecycle::STATUS_ACTIVE);

        if ($status !== null) {
            $user = app(ChangeUserStatusAction::class)->handle($user, $status, $actor, $request);
        } else {
            $user->forceFill([
                'is_active' => true,
                'security_locked_at' => null,
                'security_lock_reason' => null,
            ])->save();
            $user->refresh();
        }

        app(RecordAuditLogAction::class)->handle(
            'user_unblocked',
            $actor,
            $user,
            $before,
            app(UserLifecycle::class)->userSnapshot($user),
            [],
            $request,
        );

        app(RecordSecurityEventAction::class)->handle(
            'user_unblocked',
            $user,
            'warning',
            ['actor_id' => $actor?->getKey()],
            $request,
        );

        return $user->refresh()->loadMissing('status');
    }
}
