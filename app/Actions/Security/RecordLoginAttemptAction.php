<?php

namespace App\Actions\Security;

use App\Actions\Security\Concerns\HashesSecurityContext;
use App\Models\LoginAttempt;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class RecordLoginAttemptAction
{
    use HashesSecurityContext;

    public function handle(
        ?User $user = null,
        ?string $identifier = null,
        bool $successful = false,
        ?string $failureReason = null,
        ?Request $request = null,
        ?string $guard = null,
        ?string $ipAddress = null,
        ?string $userAgent = null,
        array $metadata = [],
    ): ?LoginAttempt {
        if (! Schema::hasTable('login_attempts')) {
            return null;
        }

        try {
            $request = $this->requestInstance($request);
            $email = $this->normalizeEmail($identifier);
            $userAgentValue = $userAgent ?: $request->userAgent();
            $attemptedAt = now();
            $sanitizedMetadata = app(SanitizeLoginMetadataAction::class)->handle($metadata);

            $attempt = LoginAttempt::query()->create([
                'user_id' => $user?->getKey(),
                'email' => $email,
                'guard' => $guard ?: config('auth.defaults.guard', 'web'),
                'identifier_hash' => $this->hashIdentifier($identifier),
                'successful' => $successful,
                'ip_address' => $ipAddress ?: $request->ip(),
                'user_agent' => $userAgentValue,
                'user_agent_hash' => filled($userAgentValue) ? hash('sha256', $userAgentValue) : null,
                'failure_reason' => $failureReason,
                'attempted_at' => $attemptedAt,
                'occurred_at' => $attemptedAt,
                'metadata' => $sanitizedMetadata,
            ]);

            app(RecordAuditLogAction::class)->handle(
                $successful ? 'login' : 'failed_login',
                $user,
                $attempt,
                [],
                [],
                [
                    'successful' => $successful,
                    'failure_reason' => $failureReason,
                    'guard' => $guard ?: config('auth.defaults.guard', 'web'),
                ],
                $request,
            );

            app(RecordSecurityEventAction::class)->handle(
                $successful ? 'login_success' : 'login_failed',
                $user,
                $successful ? 'info' : 'warning',
                [
                    'failure_reason' => $failureReason,
                    'guard' => $guard ?: config('auth.defaults.guard', 'web'),
                ],
                $request,
            );

            return $attempt;
        } catch (Throwable) {
            return null;
        }
    }

    private function normalizeEmail(?string $identifier): ?string
    {
        if (blank($identifier) || ! filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            return null;
        }

        return Str::lower(trim($identifier));
    }
}
