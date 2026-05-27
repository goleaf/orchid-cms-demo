<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class WebsiteDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            WebsitePageSeeder::class,
            WebsiteCourseSeeder::class,
            WebsitePricingSeeder::class,
            WebsiteBranchSeeder::class,
            WebsiteTrainingGroupSeeder::class,
            WebsiteFaqSeeder::class,
            WebsiteTestimonialSeeder::class,
            WebsiteSettingsSeeder::class,
        ]);
    }
}
