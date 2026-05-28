<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class AnalyticsSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AnalyticsTranslationSeeder::class,
            AnalyticsDemoSeeder::class,
        ]);
    }
}
