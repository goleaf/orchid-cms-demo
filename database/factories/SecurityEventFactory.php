<?php

namespace Database\Factories;

use App\Models\SecurityEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<SecurityEvent>
 */
class SecurityEventFactory extends Factory
{
    protected $model = SecurityEvent::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'user_id' => User::factory(),
            'event_type' => 'security.updated',
            'severity' => 'info',
            'ip_address' => '127.0.0.1',
            'user_agent_hash' => hash('sha256', 'factory-agent'),
            'metadata' => [],
            'occurred_at' => now(),
        ];
    }
}
