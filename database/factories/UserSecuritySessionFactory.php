<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserSecuritySession;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<UserSecuritySession>
 */
class UserSecuritySessionFactory extends Factory
{
    protected $model = UserSecuritySession::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'user_id' => User::factory(),
            'session_id_hash' => hash('sha256', (string) Str::uuid()),
            'guard' => 'web',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'factory-agent',
            'device_name' => 'Desktop',
            'browser_name' => 'Browser',
            'platform_name' => 'Platform',
            'country' => null,
            'city' => null,
            'logged_in_at' => now(),
            'last_activity_at' => now(),
            'logged_out_at' => null,
            'revoked_at' => null,
            'revoked_by_id' => null,
            'is_current' => false,
            'metadata' => ['source' => 'factory'],
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes): array => [
            'logged_out_at' => null,
            'revoked_at' => null,
        ]);
    }

    public function revoked(?User $revokedBy = null): static
    {
        return $this->state(fn (array $attributes): array => [
            'revoked_at' => now(),
            'revoked_by_id' => $revokedBy?->getKey() ?? User::factory(),
            'is_current' => false,
        ]);
    }

    public function loggedOut(): static
    {
        return $this->state(fn (array $attributes): array => [
            'logged_out_at' => now(),
            'is_current' => false,
        ]);
    }

    public function current(): static
    {
        return $this->active()->state(fn (array $attributes): array => [
            'is_current' => true,
        ]);
    }

    public function recent(): static
    {
        return $this->state(fn (array $attributes): array => [
            'logged_in_at' => now()->subMinutes(10),
            'last_activity_at' => now(),
        ]);
    }

    public function old(): static
    {
        return $this->state(fn (array $attributes): array => [
            'logged_in_at' => now()->subDays(120),
            'last_activity_at' => now()->subDays(120),
            'logged_out_at' => now()->subDays(90),
        ]);
    }

    public function withUser(?User $user = null): static
    {
        return $this->state(fn (array $attributes): array => [
            'user_id' => $user?->getKey() ?? User::factory(),
        ]);
    }

    public function withIpAddress(?string $ipAddress = null): static
    {
        return $this->state(fn (array $attributes): array => [
            'ip_address' => $ipAddress ?? '127.0.0.1',
        ]);
    }

    public function withDevice(): static
    {
        return $this->state(fn (array $attributes): array => [
            'device_name' => 'Workstation',
            'browser_name' => 'Firefox',
            'platform_name' => 'macOS',
        ]);
    }

    public function withLocation(): static
    {
        return $this->state(fn (array $attributes): array => [
            'country' => 'LT',
            'city' => 'Vilnius',
        ]);
    }
}
