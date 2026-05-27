<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class WebsiteSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            'default_phone' => ['group' => 'contacts', 'state' => 'groupContacts', 'value' => '+370 600 00000', 'is_public' => true],
            'default_email' => ['group' => 'contacts', 'state' => 'groupContacts', 'value' => 'info@drivepro.test', 'is_public' => true],
            'default_currency' => ['group' => 'website', 'state' => 'groupWebsite', 'value' => 'EUR', 'is_public' => true],
            'social_links' => ['group' => 'website', 'state' => 'groupWebsite', 'value' => ['facebook' => null, 'instagram' => null], 'is_public' => true],
            'hero_image' => ['group' => 'seo', 'state' => 'groupSeo', 'value' => 'images/driving-school-hero.png', 'is_public' => true],
            'default_branch_id' => ['group' => 'contacts', 'state' => 'groupContacts', 'value' => null, 'is_public' => false],
            'analytics_enabled' => ['group' => 'analytics', 'state' => 'groupAnalytics', 'value' => false, 'is_public' => false],
            'cookie_notice_enabled' => ['group' => 'analytics', 'state' => 'groupAnalytics', 'value' => true, 'is_public' => true],
        ];

        foreach ($settings as $key => $setting) {
            $factory = SiteSetting::factory()->{$setting['state']}();
            $factory = $setting['is_public'] ? $factory->public() : $factory->private();

            $payload = $factory
                ->make([
                    'key' => $key,
                    'group' => $setting['group'],
                    'value' => $setting['value'],
                    'description' => 'Default public website setting.',
                    'is_public' => $setting['is_public'],
                ])
                ->only((new SiteSetting)->getFillable());

            SiteSetting::query()->updateOrCreate(['key' => $key], $payload);
        }
    }
}
