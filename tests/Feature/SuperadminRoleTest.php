<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\Access\SuperadminPermissions;
use Database\Factories\RoleFactory;
use Database\Seeders\SuperadminRoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchid\Platform\Models\Role;
use Tests\TestCase;

class SuperadminRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seed_creates_superadmin_with_all_local_permissions(): void
    {
        $this->seed();

        $role = Role::query()
            ->where('slug', 'superadmin')
            ->firstOrFail();

        $this->assertSame('Superadmin', $role->name);

        foreach (SuperadminPermissions::all() as $permission) {
            $this->assertTrue($role->permissions[$permission] ?? false, $permission);
        }

        $admin = User::query()
            ->where('email', 'admin@example.com')
            ->firstOrFail();

        $this->assertTrue($admin->roles()->where('slug', 'superadmin')->exists());
    }

    public function test_superadmin_seeder_reuses_existing_role_and_adds_missing_permissions(): void
    {
        $role = RoleFactory::new()->create([
            'slug' => 'superadmin',
            'name' => 'Superadmin',
            'permissions' => [
                'custom.local.permission' => true,
                'system.languages.view' => false,
            ],
        ]);

        $this->seed(SuperadminRoleSeeder::class);

        $role->refresh();

        $this->assertTrue($role->permissions['custom.local.permission']);
        $this->assertTrue($role->permissions['system.languages.view']);
        $this->assertTrue($role->permissions['system.translations.import']);
        $this->assertTrue($role->permissions['crm.leads.manage_dictionaries']);
        $this->assertSame(1, Role::query()->where('slug', 'superadmin')->count());
    }
}
