<?php

namespace App\Actions\Security;

use App\Actions\Security\Concerns\HashesSecurityContext;
use App\Models\SecurityEvent;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Throwable;

class RecordSecurityEventAction
{
    use HashesSecurityContext;

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function handle(
        string $eventType,
        ?User $user = null,
        string $severity = 'info',
        array $metadata = [],
        ?Request $request = null,
    ): ?SecurityEvent {
        if (! Schema::hasTable('security_events')) {
            return null;
        }

        try {
            $request = $this->requestInstance($request);

            return SecurityEvent::query()->create([
                'user_id' => $user?->getKey(),
                'event_type' => $eventType,
                'severity' => $severity,
                'ip_address' => $request->ip(),
                'user_agent_hash' => $this->userAgentHash($request),
                'metadata' => app(RedactSensitiveFieldsAction::class)->handle($metadata),
                'occurred_at' => now(),
            ]);
        } catch (Throwable) {
            return null;
        }
    }
}
