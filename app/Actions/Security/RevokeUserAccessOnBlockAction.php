<?php

namespace App\Actions\Security;

use App\Models\User;
use App\Models\UserSecuritySession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Throwable;

class RevokeUserAccessOnBlockAction
{
    public function handle(User $user, ?User $actor = null, ?Request $request = null, string $reason = 'blocked'): int
    {
        $revoked = 0;

        try {
            if (Schema::hasColumn('users', 'remember_token')) {
                $user->forceFill(['remember_token' => null])->saveQuietly();
            }

            if (Schema::hasTable('user_security_sessions')) {
                $sessions = UserSecuritySession::query()
                    ->byUser($user)
                    ->active()
                    ->get();

                foreach ($sessions as $session) {
                    $session->forceFill([
                        'revoked_at' => now(),
                        'revoked_by_id' => $actor?->getKey(),
                        'is_current' => false,
                    ])->save();
                    $revoked++;
                }
            }

            if ($revoked > 0) {
                app(RecordAuditLogAction::class)->handle(
                    'user_sessions_revoked',
                    $actor,
                    $user,
                    [],
                    [],
                    ['count' => $revoked, 'reason' => $reason],
                    $request,
                );
            }
        } catch (Throwable) {
            return $revoked;
        }

        return $revoked;
    }
}
