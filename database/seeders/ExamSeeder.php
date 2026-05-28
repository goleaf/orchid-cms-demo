<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ExamSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ExamDictionarySeeder::class,
            ExamTranslationSeeder::class,
            ExamDemoSeeder::class,
        ]);
    }
}
