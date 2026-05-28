<?php

namespace Database\Seeders;

use App\Actions\Security\SyncPermissionRegistryAction;
use Illuminate\Database\Seeder;

class PermissionRegistrySeeder extends Seeder
{
    public function run(): void
    {
        $this->call(PermissionGroupSeeder::class);

        app(SyncPermissionRegistryAction::class)->handle();
    }
}
