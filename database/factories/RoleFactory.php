<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Orchid\Platform\Models\Role;

/**
 * @extends Factory<Role>
 */
class RoleFactory extends Factory
{
    protected $model = Role::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->jobTitle(),
            'slug' => $this->faker->unique()->slug(),
            'permissions' => [],
        ];
    }
}
