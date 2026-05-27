<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CrmSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CrmStatusSeeder::class,
            CrmSourceSeeder::class,
            CrmLostReasonSeeder::class,
            CrmTagSeeder::class,
            CrmTranslationSeeder::class,
        ]);

        if (app()->environment(['local', 'demo'])) {
            $this->call(CrmDemoLeadSeeder::class);
        }
    }
}
