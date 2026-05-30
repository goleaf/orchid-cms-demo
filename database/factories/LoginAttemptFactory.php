<?php

namespace Database\Factories;

use App\Models\LoginAttempt;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

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
        $email = $this->faker->safeEmail();

        return [
            'uuid' => (string) Str::uuid(),
            'user_id' => User::factory(),
            'email' => $email,
            'guard' => 'web',
            'identifier_hash' => hash('sha256', Str::lower($email)),
            'successful' => true,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'factory-agent',
            'user_agent_hash' => hash('sha256', 'factory-agent'),
            'failure_reason' => null,
            'attempted_at' => now(),
            'occurred_at' => now(),
            'metadata' => ['source' => 'factory'],
        ];
    }

    public function successful(): static
    {
        return $this->state(fn (array $attributes): array => [
            'successful' => true,
            'failure_reason' => null,
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes): array => [
            'successful' => false,
            'failure_reason' => LoginAttempt::FAILURE_UNKNOWN,
        ]);
    }

    public function invalidCredentials(): static
    {
        return $this->failed()->state(fn (array $attributes): array => [
            'failure_reason' => LoginAttempt::FAILURE_INVALID_CREDENTIALS,
        ]);
    }

    public function userBlocked(): static
    {
        return $this->failed()->state(fn (array $attributes): array => [
            'failure_reason' => LoginAttempt::FAILURE_USER_BLOCKED,
        ]);
    }

    public function userInactive(): static
    {
        return $this->failed()->state(fn (array $attributes): array => [
            'failure_reason' => LoginAttempt::FAILURE_USER_INACTIVE,
        ]);
    }

    public function tooManyAttempts(): static
    {
        return $this->failed()->state(fn (array $attributes): array => [
            'failure_reason' => LoginAttempt::FAILURE_TOO_MANY_ATTEMPTS,
        ]);
    }

    public function passwordExpired(): static
    {
        return $this->failed()->state(fn (array $attributes): array => [
            'failure_reason' => LoginAttempt::FAILURE_PASSWORD_EXPIRED,
        ]);
    }

    public function mustChangePassword(): static
    {
        return $this->failed()->state(fn (array $attributes): array => [
            'failure_reason' => LoginAttempt::FAILURE_MUST_CHANGE_PASSWORD,
        ]);
    }

    public function withUser(?User $user = null): static
    {
        return $this->state(fn (array $attributes): array => [
            'user_id' => $user?->getKey() ?? User::factory(),
        ]);
    }

    public function withEmail(?string $email = null): static
    {
        $email ??= $this->faker->safeEmail();

        return $this->state(fn (array $attributes): array => [
            'email' => Str::lower($email),
            'identifier_hash' => hash('sha256', Str::lower($email)),
        ]);
    }

    public function withIpAddress(?string $ipAddress = null): static
    {
        return $this->state(fn (array $attributes): array => [
            'ip_address' => $ipAddress ?? '127.0.0.1',
        ]);
    }

    public function recent(): static
    {
        return $this->state(fn (array $attributes): array => [
            'attempted_at' => now(),
            'occurred_at' => now(),
        ]);
    }

    public function old(): static
    {
        return $this->state(fn (array $attributes): array => [
            'attempted_at' => now()->subDays(120),
            'occurred_at' => now()->subDays(120),
        ]);
    }
}
