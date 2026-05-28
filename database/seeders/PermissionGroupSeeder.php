<?php

namespace Database\Seeders;

use App\Models\PermissionGroup;
use Database\Seeders\Concerns\SeedsFactoryBackedDictionaries;
use Illuminate\Database\Seeder;

class PermissionGroupSeeder extends Seeder
{
    use SeedsFactoryBackedDictionaries;

    public function run(): void
    {
        $this->seedFactoryBackedDictionary(PermissionGroup::class, 'code', [
            ['code' => 'website', 'state' => 'website'],
            ['code' => 'customer_relationship_management', 'state' => 'crm'],
            ['code' => 'students', 'state' => 'students'],
            ['code' => 'education', 'state' => 'education'],
            ['code' => 'schedule', 'state' => 'schedule'],
            ['code' => 'lessons', 'state' => 'lessons'],
            ['code' => 'driving', 'state' => 'driving'],
            ['code' => 'documents', 'state' => 'documents'],
            ['code' => 'finance', 'state' => 'finance'],
            ['code' => 'exams', 'state' => 'exams'],
            ['code' => 'notifications', 'state' => 'notifications'],
            ['code' => 'analytics', 'state' => 'analytics'],
            ['code' => 'security', 'state' => 'security'],
            ['code' => 'system', 'state' => 'system'],
        ]);
    }
}
