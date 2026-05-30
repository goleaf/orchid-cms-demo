<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\User;
use App\Models\UserStatus;
use App\Support\Access\SuperadminPermissions;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Orchid\Platform\Models\Role;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'status_id' => null,
            'preferred_locale' => null,
            'timezone' => config('app.timezone', 'Europe/Vilnius'),
            'is_active' => true,
            'security_locked_at' => null,
            'security_lock_reason' => null,
            'last_login_at' => null,
            'last_seen_at' => null,
            'password_changed_at' => null,
            'must_change_password' => false,
            'two_factor_placeholder_enabled' => false,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function active(): static
    {
        return $this->state(fn (): array => [
            'status_id' => $this->statusId('active'),
            'is_active' => true,
            'security_locked_at' => null,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => [
            'status_id' => $this->statusId('inactive'),
            'is_active' => false,
        ]);
    }

    public function blocked(): static
    {
        return $this->state(fn (): array => [
            'status_id' => $this->statusId('blocked'),
            'is_active' => true,
        ]);
    }

    public function archived(): static
    {
        return $this->state(fn (): array => [
            'status_id' => $this->statusId('archived'),
            'is_active' => false,
        ]);
    }

    public function mustChangePassword(): static
    {
        return $this->state(fn (): array => ['must_change_password' => true]);
    }

    public function withPreferredLocale(string $locale = 'en'): static
    {
        return $this->state(fn (): array => ['preferred_locale' => $locale]);
    }

    public function withTimezone(string $timezone = 'Europe/Vilnius'): static
    {
        return $this->state(fn (): array => ['timezone' => $timezone]);
    }

    public function withStaffProfile(): static
    {
        return $this->afterCreating(function (User $user): void {
            if (! $user->staffProfile()->exists()) {
                StaffProfileFactory::new()->create(['user_id' => $user->getKey()]);
            }
        });
    }

    /**
     * @param  array<int, string>|string  $roles
     */
    public function withRoles(array|string $roles = ['staff']): static
    {
        $roles = (array) $roles;

        return $this->afterCreating(function (User $user) use ($roles): void {
            $ids = collect($roles)
                ->map(fn (string $slug): int => $this->role($slug)->getKey())
                ->all();

            $user->roles()->syncWithoutDetaching($ids);
        });
    }

    public function withBranchAccess(): static
    {
        return $this->afterCreating(function (User $user): void {
            $branch = Branch::query()->first() ?? Branch::factory()->create();

            $user->accessibleBranches()->syncWithoutDetaching([
                $branch->getKey() => ['access_level' => 'staff'],
            ]);
        });
    }

    public function superadmin(): static
    {
        return $this->active()->withRoles('superadmin');
    }

    public function director(): static
    {
        return $this->withRoles('director');
    }

    public function manager(): static
    {
        return $this->withRoles('manager');
    }

    public function administrator(): static
    {
        return $this->withRoles('administrator');
    }

    public function teacher(): static
    {
        return $this->withRoles('teacher');
    }

    public function instructor(): static
    {
        return $this->withRoles('instructor');
    }

    public function finance(): static
    {
        return $this->withRoles('finance');
    }

    public function marketing(): static
    {
        return $this->withRoles('marketing');
    }

    private function statusId(string $code): ?int
    {
        $status = UserStatus::query()->where('code', $code)->first();

        if ($status !== null) {
            return $status->getKey();
        }

        if (! method_exists(UserStatus::factory(), $code)) {
            return null;
        }

        return UserStatus::factory()->{$code}()->create()->getKey();
    }

    private function role(string $slug): Role
    {
        $permissions = $slug === 'superadmin' ? SuperadminPermissions::enabled() : [];

        return Role::query()->firstOrCreate(
            ['slug' => $slug],
            [
                'name' => str($slug)->replace(['_', '-'], ' ')->title()->toString(),
                'permissions' => $permissions,
            ],
        );
    }
}
