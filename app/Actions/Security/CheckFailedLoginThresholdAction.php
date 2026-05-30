<?php

namespace App\Actions\Security;

use App\Models\LoginAttempt;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Throwable;

class CheckFailedLoginThresholdAction
{
    /**
     * @return array{exceeded: bool, severity: string, email_count: int, ip_count: int, threshold: int, minutes: int, should_block: bool}
     */
    public function handle(
        ?string $email = null,
        ?string $ipAddress = null,
        ?User $user = null,
        ?Request $request = null,
        int $threshold = 5,
        int $minutes = 15,
    ): array {
        $result = [
            'exceeded' => false,
            'severity' => 'info',
            'email_count' => 0,
            'ip_count' => 0,
            'threshold' => $threshold,
            'minutes' => $minutes,
            'should_block' => false,
        ];

        if (! Schema::hasTable('login_attempts')) {
            return $result;
        }

        try {
            $result['email_count'] = filled($email)
                ? LoginAttempt::query()->failed()->byEmail($email)->where('attempted_at', '>=', now()->subMinutes($minutes))->count()
                : 0;
            $result['ip_count'] = filled($ipAddress)
                ? LoginAttempt::query()->failed()->byIpAddress($ipAddress)->where('attempted_at', '>=', now()->subMinutes($minutes))->count()
                : 0;
            $result['exceeded'] = $result['email_count'] >= $threshold || $result['ip_count'] >= ($threshold * 2);
            $result['severity'] = $result['exceeded'] ? 'high' : 'info';

            if ($result['exceeded']) {
                app(RecordSecurityEventAction::class)->handle('login_threshold_exceeded', $user, 'high', [
                    'email_count' => $result['email_count'],
                    'ip_count' => $result['ip_count'],
                    'threshold' => $threshold,
                    'minutes' => $minutes,
                ], $request);
            }
        } catch (Throwable) {
            return $result;
        }

        return $result;
    }
}
