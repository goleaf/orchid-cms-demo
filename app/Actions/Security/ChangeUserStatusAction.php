<?php

namespace App\Actions\Security;

use App\Models\User;
use App\Models\UserStatus;
use App\Rules\ActiveUserStatusRule;
use App\Rules\CurrentUserLockoutRule;
use App\Rules\LastSuperadminUserProtectedRule;
use App\Rules\ValidUserStatusTransitionRule;
use App\Support\Security\UserLifecycle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ChangeUserStatusAction
{
    public function handle(User $user, UserStatus|int|string $status, ?User $actor = null, ?Request $request = null): User
    {
        $lifecycle = app(UserLifecycle::class);
        $targetStatus = $lifecycle->status($status);

        if ($targetStatus === null) {
            throw ValidationException::withMessages([
                'status_id' => tkey('security.validation.user_status_invalid'),
            ]);
        }

        $user->loadMissing('status', 'roles');
        $override = $lifecycle->actorCanOverrideStatus($actor);

        Validator::make(
            ['status_id' => $targetStatus->getKey()],
            ['status_id' => [
                'required',
                'integer',
                new ActiveUserStatusRule,
                new ValidUserStatusTransitionRule($user, $actor, $override),
                new LastSuperadminUserProtectedRule($user, targetStatus: $targetStatus),
                new CurrentUserLockoutRule($user, $actor, targetStatus: $targetStatus, allowOverride: $override),
            ]],
        )->validate();

        return DB::transaction(function () use ($user, $targetStatus, $actor, $request, $lifecycle): User {
            $before = $user->only(['status_id']);
            $user->forceFill(['status_id' => $targetStatus->getKey()])->save();

            $sessionsRevoked = 0;
            if ($targetStatus->is_blocked || $targetStatus->is_archived) {
                $sessionsRevoked = app(RevokeUserAccessOnBlockAction::class)->handle(
                    $user,
                    $actor,
                    $request,
                    $targetStatus->code,
                );
            }

            app(RecordAuditLogAction::class)->handle(
                'user_status_changed',
                $actor,
                $user,
                $before,
                ['status_id' => $targetStatus->getKey(), 'status_code' => $targetStatus->code],
                ['sessions_revoked' => $sessionsRevoked],
                $request,
            );

            app(RecordSecurityEventAction::class)->handle(
                'user_status_changed',
                $user,
                $lifecycle->statusLocksAccount($targetStatus) ? 'high' : 'info',
                [
                    'actor_id' => $actor?->getKey(),
                    'status_code' => $targetStatus->code,
                    'sessions_revoked' => $sessionsRevoked,
                ],
                $request,
            );

            return $user->refresh()->load('status');
        });
    }
}
