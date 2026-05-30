<?php

namespace App\Actions\Security;

use App\Models\User;
use App\Models\UserSecuritySession;
use Illuminate\Http\Request;

class RevokeOtherUserSecuritySessionsAction
{
    public function handle(User $user, ?string $currentSessionId = null, ?User $actor = null, ?Request $request = null): int
    {
        $request ??= request();
        $currentHash = app(BuildSessionIdHashAction::class)->handle(
            $currentSessionId ?: ($request->hasSession() ? $request->session()->getId() : null)
        );

        $query = UserSecuritySession::query()
            ->byUser($user)
            ->active()
            ->where('is_current', false);

        if (filled($currentHash)) {
            $query->where('session_id_hash', '!=', $currentHash);
        }

        $sessions = $query->get();

        foreach ($sessions as $session) {
            app(RevokeUserSecuritySessionAction::class)->handle($session, $actor, $request, false);
        }

        if ($sessions->isNotEmpty()) {
            app(RecordAuditLogAction::class)->handle('other_sessions_revoked', $actor, $user, [], [], [
                'count' => $sessions->count(),
            ], $request);
            app(RecordSecurityEventAction::class)->handle('other_sessions_revoked', $user, 'warning', [
                'count' => $sessions->count(),
            ], $request);
        }

        return $sessions->count();
    }
}
