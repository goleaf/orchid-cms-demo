<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            NotificationChannelSeeder::class,
            NotificationTemplateSeeder::class,
            NotificationTemplateVariableSeeder::class,
            ReminderRuleSeeder::class,
            NotificationTranslationSeeder::class,
            DemoNotificationSeeder::class,
        ]);
    }
}
