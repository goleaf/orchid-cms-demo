<?php

namespace Database\Seeders;

use App\Models\Language;
use Illuminate\Database\Seeder;

class CrmTranslationSeeder extends Seeder
{
    public function run(): void
    {
        if (! Language::query()->exists()) {
            $this->call(LanguageSeeder::class);
        }

        $this->call(SystemTranslationSeeder::class);
    }
}
