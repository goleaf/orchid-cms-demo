<?php

namespace Database\Seeders;

use App\Models\LoginAttempt;
use App\Models\User;
use Illuminate\Database\Seeder;

class LoginAttemptDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            return;
        }

        $user = User::query()->where('email', 'admin@example.com')->first() ?? User::query()->first();

        if (! $user instanceof User) {
            return;
        }

        $attempts = [
            [
                'uuid' => '11111111-1111-4111-8111-111111111111',
                'successful' => true,
                'failure_reason' => null,
                'attempted_at' => now()->subMinutes(20),
            ],
            [
                'uuid' => '22222222-2222-4222-8222-222222222222',
                'successful' => false,
                'failure_reason' => LoginAttempt::FAILURE_INVALID_CREDENTIALS,
                'attempted_at' => now()->subMinutes(10),
            ],
        ];

        foreach ($attempts as $attempt) {
            LoginAttempt::query()->updateOrCreate(
                ['uuid' => $attempt['uuid']],
                [
                    'user_id' => $attempt['successful'] ? $user->id : null,
                    'email' => $user->email,
                    'guard' => 'web',
                    'identifier_hash' => hash('sha256', $user->email),
                    'successful' => $attempt['successful'],
                    'ip_address' => '127.0.0.1',
                    'user_agent' => 'Demo Browser',
                    'user_agent_hash' => hash('sha256', 'Demo Browser'),
                    'failure_reason' => $attempt['failure_reason'],
                    'attempted_at' => $attempt['attempted_at'],
                    'occurred_at' => $attempt['attempted_at'],
                    'metadata' => ['source' => 'demo'],
                ],
            );
        }
    }
}
