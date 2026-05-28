<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\StaffProfile;
use App\Models\User;
use App\Models\UserStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class StaffProfileDemoSeeder extends Seeder
{
    public function run(): void
    {
        $branch = Branch::query()->first() ?? Branch::factory()->create();
        $activeStatusId = UserStatus::query()->where('code', 'active')->value('id');

        $user = User::query()->updateOrCreate(
            ['email' => 'staff.manager@drivepro.test'],
            [
                'name' => 'Staff Demo Manager',
                'password' => Hash::make('password'),
                'status_id' => $activeStatusId,
                'preferred_locale' => 'en',
                'timezone' => config('app.timezone', 'Europe/Vilnius'),
                'is_active' => true,
                'must_change_password' => false,
            ],
        );

        $profile = StaffProfile::withTrashed()
            ->where('user_id', $user->getKey())
            ->first();

        $attributes = StaffProfile::factory()
            ->translated()
            ->visibleOnSite()
            ->make([
                'user_id' => $user->getKey(),
                'branch_id' => $branch->getKey(),
                'staff_number' => 'STAFF-2026-0001',
                'work_email' => 'staff.manager@drivepro.test',
                'preferred_locale' => 'en',
                'timezone' => config('app.timezone', 'Europe/Vilnius'),
            ])
            ->only((new StaffProfile)->getFillable());

        if ($profile === null) {
            StaffProfile::query()->create($attributes);

            return;
        }

        unset($attributes['uuid']);

        if ($profile->trashed()) {
            $profile->restore();
        }

        $profile->fill($attributes)->save();
    }
}
