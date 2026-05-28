<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class StudentDictionarySeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            StudentStatusSeeder::class,
            EnrollmentStatusSeeder::class,
        ]);
    }
}
