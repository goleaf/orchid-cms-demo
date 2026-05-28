<?php

namespace App\Actions\Security;

use App\Actions\Security\Concerns\HashesSecurityContext;
use App\Models\LoginAttempt;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Throwable;

class RecordLoginAttemptAction
{
    use HashesSecurityContext;

    public function handle(
        ?User $user,
        ?string $identifier,
        bool $successful,
        ?string $failureReason = null,
        ?Request $request = null,
    ): ?LoginAttempt {
        if (! Schema::hasTable('login_attempts')) {
            return null;
        }

        try {
            $request = $this->requestInstance($request);
            $attempt = LoginAttempt::query()->create([
                'user_id' => $user?->getKey(),
                'identifier_hash' => $this->hashIdentifier($identifier),
                'successful' => $successful,
                'ip_address' => $request->ip(),
                'user_agent_hash' => $this->userAgentHash($request),
                'failure_reason' => $failureReason,
                'occurred_at' => now(),
            ]);

            app(RecordSecurityEventAction::class)->handle(
                $successful ? 'login.succeeded' : 'login.failed',
                $user,
                $successful ? 'info' : 'warning',
                ['failure_reason' => $failureReason],
                $request,
            );

            return $attempt;
        } catch (Throwable) {
            return null;
        }
    }
}
