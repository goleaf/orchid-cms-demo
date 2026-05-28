<?php

namespace Database\Factories;

use App\Models\LoginAttempt;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LoginAttempt>
 */
class LoginAttemptFactory extends Factory
{
    protected $model = LoginAttempt::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'identifier_hash' => hash('sha256', $this->faker->safeEmail()),
            'successful' => true,
            'ip_address' => '127.0.0.1',
            'user_agent_hash' => hash('sha256', 'factory-agent'),
            'failure_reason' => null,
            'occurred_at' => now(),
        ];
    }
}
