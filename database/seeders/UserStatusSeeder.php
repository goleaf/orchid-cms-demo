<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserStatus;
use Database\Seeders\Concerns\SeedsFactoryBackedDictionaries;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class UserStatusSeeder extends Seeder
{
    use SeedsFactoryBackedDictionaries;

    public function run(): void
    {
        $this->seedFactoryBackedDictionary(UserStatus::class, 'code', [
            ['code' => 'active', 'state' => 'active'],
            ['code' => 'inactive', 'state' => 'inactive'],
            ['code' => 'blocked', 'state' => 'blocked'],
            ['code' => 'archived', 'state' => 'archived'],
        ]);

        UserStatus::query()
            ->where('code', '!=', 'active')
            ->update(['is_default' => false]);

        UserStatus::query()
            ->where('code', 'active')
            ->update(['is_default' => true, 'is_active' => true]);

        $activeId = UserStatus::query()
            ->where('code', 'active')
            ->value('id');

        if ($activeId !== null && Schema::hasColumn('users', 'status_id')) {
            User::query()
                ->whereNull('status_id')
                ->update(['status_id' => $activeId]);
        }
    }
}
