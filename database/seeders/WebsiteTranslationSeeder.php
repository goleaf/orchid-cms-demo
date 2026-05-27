<?php

namespace Database\Seeders;

class WebsiteTranslationSeeder extends SystemTranslationSeeder
{
    public function run(): void
    {
        $this->seedEntries($this->websiteEntries());
    }
}
