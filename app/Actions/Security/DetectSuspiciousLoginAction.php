<?php

namespace App\Actions\Security;

use App\Models\LoginAttempt;
use App\Models\User;
use App\Models\UserSecuritySession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class DetectSuspiciousLoginAction
{
    /**
     * @return array{suspicious: bool, severity: string, reasons: array<int, string>, recommended_event_type: string}
     */
    public function handle(?User $user = null, ?string $email = null, ?string $ipAddress = null, ?Request $request = null): array
    {
        $reasons = [];
        $severity = 'info';
        $request ??= request();
        $ipAddress ??= $request->ip();

        try {
            if (Schema::hasTable('login_attempts')) {
                $failedByEmail = filled($email)
                    ? LoginAttempt::query()->failed()->byEmail($email)->where('attempted_at', '>=', now()->subMinutes(15))->count()
                    : 0;
                $failedByIp = filled($ipAddress)
                    ? LoginAttempt::query()->failed()->byIpAddress($ipAddress)->where('attempted_at', '>=', now()->subMinutes(15))->count()
                    : 0;

                if ($failedByEmail >= 5) {
                    $reasons[] = 'many_failed_attempts_for_email';
                    $severity = 'high';
                }

                if ($failedByIp >= 10) {
                    $reasons[] = 'many_failed_attempts_from_ip';
                    $severity = 'high';
                }
            }

            if ($user instanceof User && filled($ipAddress) && Schema::hasTable('user_security_sessions')) {
                $knownIp = UserSecuritySession::query()
                    ->byUser($user)
                    ->where('ip_address', $ipAddress)
                    ->exists();
                $hasPrevious = UserSecuritySession::query()->byUser($user)->exists();

                if ($hasPrevious && ! $knownIp) {
                    $reasons[] = 'new_ip_address';
                    $severity = $severity === 'high' ? 'high' : 'warning';
                }
            }
        } catch (Throwable) {
            return [
                'suspicious' => false,
                'severity' => 'info',
                'reasons' => [],
                'recommended_event_type' => 'login_suspicious',
            ];
        }

        return [
            'suspicious' => $reasons !== [],
            'severity' => $severity,
            'reasons' => $reasons,
            'recommended_event_type' => Str::contains(implode(' ', $reasons), 'new_ip') ? 'login_new_ip' : 'login_suspicious',
        ];
    }
}
