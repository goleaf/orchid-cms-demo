<?php

namespace App\Actions\Security;

use App\Actions\Security\Concerns\HashesSecurityContext;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Throwable;

class RecordAuditLogAction
{
    use HashesSecurityContext;

    /**
     * @param  array<string, mixed>  $oldValues
     * @param  array<string, mixed>  $newValues
     * @param  array<string, mixed>  $metadata
     */
    public function handle(
        string $action,
        ?User $actor = null,
        ?Model $auditable = null,
        array $oldValues = [],
        array $newValues = [],
        array $metadata = [],
        ?Request $request = null,
        ?string $category = 'security',
    ): ?AuditLog {
        if (! Schema::hasTable('audit_logs')) {
            return null;
        }

        try {
            $redactor = app(RedactSensitiveFieldsAction::class);
            $request = $this->requestInstance($request);

            return AuditLog::query()->create([
                'user_id' => $actor?->getKey(),
                'auditable_type' => $auditable?->getMorphClass(),
                'auditable_id' => $auditable?->getKey(),
                'action' => $action,
                'category' => $category,
                'ip_address' => $request->ip(),
                'user_agent_hash' => $this->userAgentHash($request),
                'old_values' => $redactor->handle($oldValues),
                'new_values' => $redactor->handle($newValues),
                'metadata' => $redactor->handle($metadata),
                'occurred_at' => now(),
            ]);
        } catch (Throwable) {
            return null;
        }
    }
}
