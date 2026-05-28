<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\User;
use App\Models\UserBranchAccess;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserBranchAccess>
 */
class UserBranchAccessFactory extends Factory
{
    protected $model = UserBranchAccess::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'branch_id' => Branch::factory(),
            'access_level' => 'staff',
            'created_by_id' => null,
            'updated_by_id' => null,
        ];
    }
}
