<?php

namespace App\Actions\Security;

use App\Models\LoginAttempt;
use App\Models\User;
use Illuminate\Http\Request;
use Throwable;

class RecordFailedLoginAction
{
    public function handle(
        ?User $user = null,
        ?string $email = null,
        ?string $failureReason = null,
        ?Request $request = null,
        ?string $guard = null,
        array $metadata = [],
    ): array {
        $request ??= request();
        $failureReason = $failureReason ?: $this->resolveFailureReason($user);

        try {
            $attempt = app(RecordLoginAttemptAction::class)->handle(
                $user,
                $email ?: $user?->email,
                false,
                $failureReason,
                $request,
                $guard,
                metadata: $metadata,
            );

            $threshold = app(CheckFailedLoginThresholdAction::class)->handle($email ?: $user?->email, $request->ip(), $user, $request);
            $suspicious = app(DetectSuspiciousLoginAction::class)->handle($user, $email ?: $user?->email, $request->ip(), $request);

            if ($suspicious['suspicious'] ?? false) {
                app(RecordSecurityEventAction::class)->handle(
                    (string) ($suspicious['recommended_event_type'] ?? 'login_suspicious'),
                    $user,
                    (string) ($suspicious['severity'] ?? 'high'),
                    ['reasons' => $suspicious['reasons'] ?? []],
                    $request,
                );
            }

            return ['attempt' => $attempt, 'threshold' => $threshold, 'suspicious' => $suspicious];
        } catch (Throwable) {
            return ['attempt' => null, 'threshold' => null, 'suspicious' => null];
        }
    }

    private function resolveFailureReason(?User $user): string
    {
        if (! $user instanceof User) {
            return LoginAttempt::FAILURE_INVALID_CREDENTIALS;
        }

        if ($user->isArchived()) {
            return LoginAttempt::FAILURE_USER_ARCHIVED;
        }

        if ($user->isBlocked() || $user->security_locked_at !== null) {
            return LoginAttempt::FAILURE_USER_BLOCKED;
        }

        if ($user->is_active === false) {
            return LoginAttempt::FAILURE_USER_INACTIVE;
        }

        if ($user->must_change_password) {
            return LoginAttempt::FAILURE_MUST_CHANGE_PASSWORD;
        }

        return LoginAttempt::FAILURE_INVALID_CREDENTIALS;
    }
}
