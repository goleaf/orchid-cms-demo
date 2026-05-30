<?php

namespace App\Actions\Security;

use App\Models\User;
use App\Models\UserSecuritySession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class RecordUserLoginSessionAction
{
    public function handle(
        User $user,
        ?string $sessionId = null,
        ?Request $request = null,
        ?string $guard = null,
        array $metadata = [],
    ): ?UserSecuritySession {
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

            UserSecuritySession::query()
                ->where('user_id', $user->getKey())
                ->where('guard', $guard ?: config('auth.defaults.guard', 'web'))
                ->where('session_id_hash', '!=', $hash)
                ->update(['is_current' => false]);

            $session = UserSecuritySession::query()->updateOrCreate(
                ['session_id_hash' => $hash],
                [
                    'user_id' => $user->getKey(),
                    'guard' => $guard ?: config('auth.defaults.guard', 'web'),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'device_name' => $this->deviceName($request->userAgent()),
                    'browser_name' => $this->browserName($request->userAgent()),
                    'platform_name' => $this->platformName($request->userAgent()),
                    'logged_in_at' => now(),
                    'last_activity_at' => now(),
                    'logged_out_at' => null,
                    'revoked_at' => null,
                    'revoked_by_id' => null,
                    'is_current' => true,
                    'metadata' => app(SanitizeLoginMetadataAction::class)->handle($metadata),
                ],
            );

            app(RecordAuditLogAction::class)->handle('session_created', $user, $session, [], [], [], $request);

            return $session;
        } catch (Throwable) {
            return null;
        }
    }

    private function deviceName(?string $userAgent): ?string
    {
        if (blank($userAgent)) {
            return null;
        }

        return Str::contains(Str::lower($userAgent), ['mobile', 'iphone', 'android']) ? 'Mobile' : 'Desktop';
    }

    private function browserName(?string $userAgent): ?string
    {
        $agent = Str::lower((string) $userAgent);

        return match (true) {
            str_contains($agent, 'firefox') => 'Firefox',
            str_contains($agent, 'edg') => 'Edge',
            str_contains($agent, 'chrome') => 'Chrome',
            str_contains($agent, 'safari') => 'Safari',
            default => null,
        };
    }

    private function platformName(?string $userAgent): ?string
    {
        $agent = Str::lower((string) $userAgent);

        return match (true) {
            str_contains($agent, 'windows') => 'Windows',
            str_contains($agent, 'mac') => 'macOS',
            str_contains($agent, 'linux') => 'Linux',
            str_contains($agent, 'android') => 'Android',
            str_contains($agent, 'iphone') || str_contains($agent, 'ipad') => 'iOS',
            default => null,
        };
    }
}
