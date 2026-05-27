<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Seeder;

class WebsiteBranchSeeder extends Seeder
{
    public function run(): void
    {
        $branches = [
            'vilnius-main' => [
                'code' => 'VILNIUS_MAIN',
                'name' => ['ru' => 'DrivePro Academy Вильнюс', 'en' => 'DrivePro Academy Vilnius', 'lt' => 'DrivePro Academy Vilnius', 'pl' => 'DrivePro Academy Wilno'],
                'city' => ['ru' => 'Вильнюс', 'en' => 'Vilnius', 'lt' => 'Vilnius', 'pl' => 'Wilno'],
                'address' => ['ru' => 'Gedimino pr. 1', 'en' => 'Gedimino Ave. 1', 'lt' => 'Gedimino pr. 1', 'pl' => 'Gedimino pr. 1'],
                'phone' => '+370 600 00000',
                'email' => 'vilnius@drivepro.test',
                'sort_order' => 10,
                'coordinates' => [54.6872, 25.2797],
            ],
            'kaunas-center' => [
                'code' => 'KAUNAS_CENTER',
                'name' => ['ru' => 'DrivePro Academy Каунас', 'en' => 'DrivePro Academy Kaunas', 'lt' => 'DrivePro Academy Kaunas', 'pl' => 'DrivePro Academy Kowno'],
                'city' => ['ru' => 'Каунас', 'en' => 'Kaunas', 'lt' => 'Kaunas', 'pl' => 'Kowno'],
                'address' => ['ru' => 'Laisves al. 10', 'en' => 'Laisves Ave. 10', 'lt' => 'Laisves al. 10', 'pl' => 'Laisves al. 10'],
                'phone' => '+370 600 00010',
                'email' => 'kaunas@drivepro.test',
                'sort_order' => 20,
                'coordinates' => [54.8985, 23.9036],
            ],
        ];

        foreach ($branches as $slug => $branch) {
            $payload = Branch::factory()
                ->active()
                ->visibleOnSite()
                ->translated()
                ->withContacts($branch['phone'], $branch['email'])
                ->withCoordinates($branch['coordinates'][0], $branch['coordinates'][1])
                ->make([
                    'code' => $branch['code'],
                    'slug' => $slug,
                    'name' => $branch['name']['en'],
                    'name_translations' => $branch['name'],
                    'city' => $branch['city']['en'],
                    'city_translations' => $branch['city'],
                    'address' => $branch['address']['en'],
                    'address_translations' => $branch['address'],
                    'sort_order' => $branch['sort_order'],
                ])
                ->only((new Branch)->getFillable());

            Branch::query()->updateOrCreate(['slug' => $slug], $payload);
        }
    }
}
