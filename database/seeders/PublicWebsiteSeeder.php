<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class PublicWebsiteSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(WebsiteDemoSeeder::class);
    }
}
