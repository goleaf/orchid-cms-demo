<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ExamSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ExamTypeSeeder::class,
            ExamStatusSeeder::class,
            ExamAttemptStatusSeeder::class,
            ExamResultStatusSeeder::class,
            ExamAdmissionRuleSeeder::class,
            ExamTranslationSeeder::class,
        ]);

        if (app()->environment(['local', 'demo', 'testing'])) {
            $this->call(DemoExamSeeder::class);
        }
    }
}
