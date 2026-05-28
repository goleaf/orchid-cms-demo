<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserSession;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<UserSession>
 */
class UserSessionFactory extends Factory
{
    protected $model = UserSession::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'session_hash' => hash('sha256', (string) Str::uuid()),
            'ip_address' => '127.0.0.1',
            'user_agent_hash' => hash('sha256', 'factory-agent'),
            'user_agent_preview' => 'factory-agent',
            'logged_in_at' => now(),
            'last_seen_at' => now(),
            'logged_out_at' => null,
        ];
    }
}
