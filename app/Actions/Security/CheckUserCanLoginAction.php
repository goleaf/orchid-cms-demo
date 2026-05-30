<?php

namespace App\Actions\Security;

use App\Models\User;
use App\Support\Security\UserLifecycle;

class CheckUserCanLoginAction
{
    /**
     * @return array{allowed: bool, reason: string, status_code: int}
     */
    public function handle(User $user): array
    {
        $status = app(ResolveUserStatusAction::class)->handle($user);

        if (($status['is_archived'] ?? false) === true || $status['code'] === UserLifecycle::STATUS_ARCHIVED) {
            return $this->denied('user_archived', 403);
        }

        if (($status['is_blocked'] ?? false) === true || $status['code'] === UserLifecycle::STATUS_BLOCKED) {
            return $this->denied('user_blocked', 423);
        }

        if ($user->is_active === false || $status['code'] === UserLifecycle::STATUS_INACTIVE) {
            return $this->denied('user_inactive', 403);
        }

        if ((bool) $user->must_change_password) {
            return $this->denied('must_change_password', 428);
        }

        return [
            'allowed' => true,
            'reason' => 'allowed',
            'status_code' => 200,
        ];
    }

    /**
     * @return array{allowed: bool, reason: string, status_code: int}
     */
    private function denied(string $reason, int $statusCode): array
    {
        return [
            'allowed' => false,
            'reason' => $reason,
            'status_code' => $statusCode,
        ];
    }
}
