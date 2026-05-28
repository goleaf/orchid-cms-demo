<?php

namespace Database\Factories;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<AuditLog>
 */
class AuditLogFactory extends Factory
{
    protected $model = AuditLog::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'user_id' => User::factory(),
            'auditable_type' => User::class,
            'auditable_id' => User::factory(),
            'action' => 'updated',
            'category' => 'security',
            'ip_address' => '127.0.0.1',
            'user_agent_hash' => hash('sha256', 'factory-agent'),
            'old_values' => [],
            'new_values' => [],
            'metadata' => [],
            'occurred_at' => now(),
        ];
    }
}
