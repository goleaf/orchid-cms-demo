<?php

namespace App\Actions\Security;

use App\Models\User;
use App\Support\Security\UserLifecycle;

class ResolveUserStatusAction
{
    /**
     * @return array<string, mixed>
     */
    public function handle(User $user): array
    {
        $user->loadMissing('status');
        $status = $user->status;

        if ($status === null) {
            return [
                'status' => null,
                'code' => $user->is_active === false ? UserLifecycle::STATUS_INACTIVE : UserLifecycle::STATUS_ACTIVE,
                'label' => $user->is_active === false
                    ? tkey('security.user_statuses.inactive')
                    : tkey('security.user_statuses.active'),
                'is_active' => (bool) $user->is_active,
                'is_blocked' => false,
                'is_archived' => false,
                'is_final' => false,
            ];
        }

        return [
            'status' => $status,
            'code' => $status->code,
            'label' => $status->display_name,
            'is_active' => (bool) $status->is_active,
            'is_blocked' => (bool) $status->is_blocked,
            'is_archived' => (bool) $status->is_archived,
            'is_final' => (bool) $status->is_final,
        ];
    }
}
