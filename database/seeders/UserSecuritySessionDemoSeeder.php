<?php

namespace Database\Seeders;

use App\Actions\Security\BuildSessionIdHashAction;
use App\Models\User;
use App\Models\UserSecuritySession;
use Illuminate\Database\Seeder;

class UserSecuritySessionDemoSeeder extends Seeder
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

        $hash = app(BuildSessionIdHashAction::class)->handle('demo-session-admin');

        UserSecuritySession::query()->updateOrCreate(
            ['session_id_hash' => $hash],
            [
                'uuid' => '33333333-3333-4333-8333-333333333333',
                'user_id' => $user->id,
                'guard' => 'web',
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Demo Browser',
                'device_name' => 'Desktop',
                'browser_name' => 'Browser',
                'platform_name' => 'Platform',
                'country' => 'LT',
                'city' => 'Vilnius',
                'logged_in_at' => now()->subHour(),
                'last_activity_at' => now()->subMinutes(5),
                'logged_out_at' => null,
                'revoked_at' => null,
                'revoked_by_id' => null,
                'is_current' => false,
                'metadata' => ['source' => 'demo'],
            ],
        );
    }
}
