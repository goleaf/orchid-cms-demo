<?php

namespace App\Actions\Security;

use App\Models\User;
use App\Models\UserSecuritySession;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RevokeAllUserSecuritySessionsAction
{
    public function handle(
        User $target,
        ?User $actor = null,
        ?Request $request = null,
        bool $includeCurrent = false,
    ): int {
        if (! $actor?->hasAccess('security.sessions.revoke_all') && ! $actor?->isSuperadmin()) {
            throw ValidationException::withMessages([
                'user' => tkey('security.validation.user_cannot_revoke_all_sessions'),
            ]);
        }

        $request ??= request();
        $currentHash = app(BuildSessionIdHashAction::class)->handle($request->hasSession() ? $request->session()->getId() : null);

        if ($actor?->is($target) && $target->isSuperadmin() && ($includeCurrent || blank($currentHash))) {
            throw ValidationException::withMessages([
                'user' => tkey('security.validation.user_cannot_revoke_all_sessions'),
            ]);
        }

        $query = UserSecuritySession::query()->byUser($target)->active();

        if (! $includeCurrent) {
            $query->where('is_current', false);

            if (filled($currentHash)) {
                $query->where('session_id_hash', '!=', $currentHash);
            }
        }

        $sessions = $query->get();

        foreach ($sessions as $session) {
            app(RevokeUserSecuritySessionAction::class)->handle($session, $actor, $request, false);
        }

        if ($sessions->isNotEmpty()) {
            app(RecordAuditLogAction::class)->handle('sessions_revoked', $actor, $target, [], [], [
                'count' => $sessions->count(),
                'include_current' => $includeCurrent,
            ], $request);
            app(RecordSecurityEventAction::class)->handle('sessions_revoked', $target, 'high', [
                'count' => $sessions->count(),
                'include_current' => $includeCurrent,
            ], $request);
        }

        return $sessions->count();
    }
}
