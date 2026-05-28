<?php

namespace App\Actions\Security;

use App\Actions\Security\Concerns\HashesSecurityContext;
use App\Models\User;
use App\Models\UserSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Throwable;

class RecordUserSessionAction
{
    use HashesSecurityContext;

    public function handle(User $user, ?string $sessionId = null, ?Request $request = null): ?UserSession
    {
        if (! Schema::hasTable('user_sessions')) {
            return null;
        }

        try {
            $request = $this->requestInstance($request);
            $sessionId ??= $request->hasSession() ? $request->session()->getId() : null;

            if (blank($sessionId)) {
                return null;
            }

            $session = UserSession::query()->updateOrCreate(
                ['session_hash' => $this->hashSessionId($sessionId)],
                [
                    'user_id' => $user->getKey(),
                    'ip_address' => $request->ip(),
                    'user_agent_hash' => $this->userAgentHash($request),
                    'user_agent_preview' => $this->userAgentPreview($request),
                    'logged_in_at' => now(),
                    'last_seen_at' => now(),
                    'logged_out_at' => null,
                ],
            );

            app(RecordSecurityEventAction::class)->handle('session.started', $user, 'info', [], $request);

            return $session;
        } catch (Throwable) {
            return null;
        }
    }
}
