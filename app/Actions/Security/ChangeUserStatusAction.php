<?php

namespace App\Actions\Security;

use App\Models\User;
use App\Models\UserStatus;
use App\Rules\ActiveUserStatusRule;
use App\Rules\UserStatusCanBeChangedRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ChangeUserStatusAction
{
    public function handle(User $user, UserStatus|int|string $status, ?User $actor = null, ?Request $request = null): User
    {
        $targetStatus = $this->status($status);

        if ($targetStatus === null) {
            throw ValidationException::withMessages([
                'status_id' => tkey('security.validation.user_status_invalid'),
            ]);
        }

        Validator::make(
            ['status_id' => $targetStatus->getKey()],
            ['status_id' => ['required', 'integer', new ActiveUserStatusRule, new UserStatusCanBeChangedRule($user)]],
        )->validate();

        return DB::transaction(function () use ($user, $targetStatus, $actor, $request): User {
            $before = $user->only(['status_id']);
            $user->forceFill(['status_id' => $targetStatus->getKey()])->save();

            app(RecordAuditLogAction::class)->handle(
                'user.status_changed',
                $actor,
                $user,
                $before,
                ['status_id' => $targetStatus->getKey(), 'status_code' => $targetStatus->code],
                [],
                $request,
            );

            app(RecordSecurityEventAction::class)->handle(
                'user.status_changed',
                $user,
                $targetStatus->is_blocked || $targetStatus->is_archived ? 'warning' : 'info',
                ['actor_id' => $actor?->getKey(), 'status_code' => $targetStatus->code],
                $request,
            );

            return $user->refresh()->load('status');
        });
    }

    private function status(UserStatus|int|string $status): ?UserStatus
    {
        if ($status instanceof UserStatus) {
            return $status;
        }

        return UserStatus::query()
            ->when(is_numeric($status), fn ($query) => $query->whereKey((int) $status))
            ->when(! is_numeric($status), fn ($query) => $query->where('code', (string) $status))
            ->first();
    }
}
