<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ExamDictionarySeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ExamTypeSeeder::class,
            ExamStatusSeeder::class,
            ExamAttemptStatusSeeder::class,
            ExamResultStatusSeeder::class,
            ExamAdmissionRuleSeeder::class,
        ]);
    }
}
