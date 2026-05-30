<?php

namespace App\Actions\Security;

use App\Models\User;
use App\Models\UserSecuritySession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class RevokeUserSecuritySessionAction
{
    public function handle(
        UserSecuritySession $session,
        ?User $actor = null,
        ?Request $request = null,
        bool $logoutIfCurrent = true,
    ): UserSecuritySession {
        if (! $session->can_be_revoked) {
            throw ValidationException::withMessages([
                'session' => tkey('security.validation.session_cannot_be_revoked'),
            ]);
        }

        $request ??= request();

        $session->forceFill([
            'revoked_at' => now(),
            'revoked_by_id' => $actor?->getKey(),
            'is_current' => false,
        ])->save();

        app(RecordAuditLogAction::class)->handle('session_revoked', $actor, $session, [], [], [], $request);
        app(RecordSecurityEventAction::class)->handle('session_revoked', $session->user, 'warning', [
            'revoked_by_id' => $actor?->getKey(),
        ], $request);

        if ($logoutIfCurrent && $this->isCurrentRequestSession($session, $request)) {
            Auth::guard($session->guard ?: config('auth.defaults.guard', 'web'))->logout();

            if ($request->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }
        }

        return $session->refresh();
    }

    private function isCurrentRequestSession(UserSecuritySession $session, Request $request): bool
    {
        $hash = app(BuildSessionIdHashAction::class)->handle($request->hasSession() ? $request->session()->getId() : null);

        return filled($hash) && hash_equals($session->session_id_hash, $hash);
    }
}
