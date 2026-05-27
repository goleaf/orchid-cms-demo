<?php

namespace Database\Seeders;

use App\Models\LeadSource;
use Database\Seeders\Concerns\SeedsFactoryBackedDictionaries;
use Illuminate\Database\Seeder;

class CrmSourceSeeder extends Seeder
{
    use SeedsFactoryBackedDictionaries;

    public function run(): void
    {
        $this->seedFactoryBackedDictionary(LeadSource::class, 'code', [
            ['code' => 'website', 'state' => 'website'],
            ['code' => 'callback', 'state' => 'callback'],
            ['code' => 'contact_form', 'state' => 'contactForm'],
            ['code' => 'phone', 'state' => 'phone'],
            ['code' => 'office', 'state' => 'office'],
            ['code' => 'google_ads', 'state' => 'googleAds'],
            ['code' => 'facebook', 'state' => 'facebook'],
            ['code' => 'instagram', 'state' => 'instagram'],
            ['code' => 'tiktok', 'state' => 'tiktok'],
            ['code' => 'telegram', 'state' => 'telegram'],
            ['code' => 'whatsapp', 'state' => 'whatsapp'],
            ['code' => 'referral', 'state' => 'referral'],
            ['code' => 'partner', 'state' => 'partner'],
            ['code' => 'other', 'state' => 'other'],
        ]);
    }
}
