<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\Access\SuperadminPermissions;
use Illuminate\Database\Seeder;
use Orchid\Platform\Models\Role;

class SuperadminRoleSeeder extends Seeder
{
    public function run(): void
    {
        $existingRole = Role::query()
            ->where('slug', 'superadmin')
            ->first();

        $permissions = [
            ...($existingRole?->permissions ?? []),
            ...SuperadminPermissions::enabled(),
        ];

        $superadminRole = Role::query()->updateOrCreate(
            ['slug' => 'superadmin'],
            [
                'name' => 'Superadmin',
                'permissions' => $permissions,
            ],
        );

        $seededAdmin = User::query()
            ->where('email', 'admin@example.com')
            ->first();

        $seededAdmin?->roles()->syncWithoutDetaching([$superadminRole->id]);
    }
}
