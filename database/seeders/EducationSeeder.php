<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class EducationSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            EducationTranslationSeeder::class,
            TrainingGroupStatusSeeder::class,
            LearningProgramSeeder::class,
            LearningProgramModuleSeeder::class,
        ]);
    }
}
