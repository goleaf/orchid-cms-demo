<?php

namespace App\Actions\Security;

use App\Actions\Security\Concerns\HashesSecurityContext;
use App\Models\UserSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Throwable;

class CloseUserSessionAction
{
    use HashesSecurityContext;

    public function handle(?string $sessionId = null, ?Request $request = null): int
    {
        if (! Schema::hasTable('user_sessions')) {
            return 0;
        }

        try {
            $request = $this->requestInstance($request);
            $sessionId ??= $request->hasSession() ? $request->session()->getId() : null;

            if (blank($sessionId)) {
                return 0;
            }

            return UserSession::query()
                ->where('session_hash', $this->hashSessionId($sessionId))
                ->whereNull('logged_out_at')
                ->update([
                    'last_seen_at' => now(),
                    'logged_out_at' => now(),
                ]);
        } catch (Throwable) {
            return 0;
        }
    }
}
