<?php

namespace Database\Seeders;

use App\Models\Language;
use Illuminate\Database\Seeder;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        collect([
            ['code' => 'ru', 'name' => 'Russian', 'native_name' => 'Русский', 'is_default' => true, 'sort_order' => 10],
            ['code' => 'en', 'name' => 'English', 'native_name' => 'English', 'is_default' => false, 'sort_order' => 20],
            ['code' => 'lt', 'name' => 'Lithuanian', 'native_name' => 'Lietuvių', 'is_default' => false, 'sort_order' => 30],
            ['code' => 'pl', 'name' => 'Polish', 'native_name' => 'Polski', 'is_default' => false, 'sort_order' => 40],
        ])->each(fn (array $language): Language => Language::query()->updateOrCreate(
            ['code' => $language['code']],
            [
                'name' => $language['name'],
                'native_name' => $language['native_name'],
                'is_default' => $language['is_default'],
                'is_active' => true,
                'sort_order' => $language['sort_order'],
            ],
        ));

        Language::flushLanguageCache();
    }
}
