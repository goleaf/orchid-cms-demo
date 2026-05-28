<?php

namespace Database\Seeders;

use App\Models\StudentStatus;
use Database\Seeders\Concerns\SeedsFactoryBackedDictionaries;
use Illuminate\Database\Seeder;

class StudentStatusSeeder extends Seeder
{
    use SeedsFactoryBackedDictionaries;

    public function run(): void
    {
        $this->seedFactoryBackedDictionary(StudentStatus::class, 'code', [
            ['code' => 'active', 'state' => 'active'],
            ['code' => 'inactive', 'state' => 'inactive'],
            ['code' => 'blocked', 'state' => 'blocked'],
            ['code' => 'archived', 'state' => 'archived'],
            ['code' => 'lead', 'state' => 'lead'],
            ['code' => 'enrolled', 'state' => 'enrolled'],
            ['code' => 'graduated', 'state' => 'graduated'],
        ]);

        StudentStatus::query()
            ->where('code', '!=', 'active')
            ->update(['is_default' => false]);
    }
}
