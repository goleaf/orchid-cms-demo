<?php

namespace App\Actions\Security;

use App\Models\User;
use App\Models\UserSecuritySession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Throwable;

class RecordUserLogoutSessionAction
{
    public function handle(?User $user = null, ?string $sessionId = null, ?Request $request = null): int
    {
        if (! Schema::hasTable('user_security_sessions')) {
            return 0;
        }

        try {
            $request ??= request();
            $sessionId ??= $request->hasSession() ? $request->session()->getId() : null;
            $hash = app(BuildSessionIdHashAction::class)->handle($sessionId);

            if (blank($hash)) {
                return 0;
            }

            $sessions = UserSecuritySession::query()
                ->where('session_id_hash', $hash)
                ->active()
                ->get();

            foreach ($sessions as $session) {
                $session->forceFill([
                    'last_activity_at' => now(),
                    'logged_out_at' => now(),
                    'is_current' => false,
                ])->save();

                app(RecordAuditLogAction::class)->handle('logout', $user ?: $session->user, $session, [], [], [], $request);
                app(RecordSecurityEventAction::class)->handle('logout', $user ?: $session->user, 'info', [], $request);
            }

            return $sessions->count();
        } catch (Throwable) {
            return 0;
        }
    }
}
