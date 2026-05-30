<?php

namespace App\Actions\Security;

use App\Models\User;
use App\Models\UserSecuritySession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Throwable;

class TouchUserSecuritySessionAction
{
    public function handle(User $user, ?string $sessionId = null, ?Request $request = null, int $throttleMinutes = 5): ?UserSecuritySession
    {
        if (! Schema::hasTable('user_security_sessions')) {
            return null;
        }

        try {
            $request ??= request();
            $sessionId ??= $request->hasSession() ? $request->session()->getId() : null;
            $hash = app(BuildSessionIdHashAction::class)->handle($sessionId);

            if (blank($hash)) {
                return null;
            }

            $session = UserSecuritySession::query()
                ->where('session_id_hash', $hash)
                ->active()
                ->first();

            if (! $session instanceof UserSecuritySession) {
                return null;
            }

            if ($session->last_activity_at !== null && $session->last_activity_at->gt(now()->subMinutes($throttleMinutes))) {
                return $session;
            }

            $session->forceFill([
                'last_activity_at' => now(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ])->save();

            if (Schema::hasColumn('users', 'last_seen_at')) {
                $user->forceFill(['last_seen_at' => now()])->saveQuietly();
            }

            return $session;
        } catch (Throwable) {
            return null;
        }
    }
}
