<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Seeder;

class WebsiteBranchSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->countries() as $country) {
            foreach ($country['branches'] as $slug => $branch) {
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
                        'country' => $country['name']['en'],
                        'country_translations' => $country['name'],
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

    /**
     * @return array<int, array{
     *     name: array{ru: string, en: string, lt: string, pl: string},
     *     branches: array<string, array{
     *         code: string,
     *         name: array{ru: string, en: string, lt: string, pl: string},
     *         city: array{ru: string, en: string, lt: string, pl: string},
     *         address: array{ru: string, en: string, lt: string, pl: string},
     *         phone: string,
     *         email: string,
     *         sort_order: int,
     *         coordinates: array{float, float}
     *     }>
     * }>
     */
    private function countries(): array
    {
        return [
            [
                'name' => ['ru' => 'Литва', 'en' => 'Lithuania', 'lt' => 'Lietuva', 'pl' => 'Litwa'],
                'branches' => [
                    'vilnius-main' => [
                        'code' => 'LT_VILNIUS_MAIN',
                        'name' => ['ru' => 'DrivePro Academy Вильнюс', 'en' => 'DrivePro Academy Vilnius', 'lt' => 'DrivePro Academy Vilniaus filialas', 'pl' => 'DrivePro Academy Wilno'],
                        'city' => ['ru' => 'Вильнюс', 'en' => 'Vilnius', 'lt' => 'Vilniaus miestas', 'pl' => 'Wilno'],
                        'address' => ['ru' => 'Gedimino pr. 1', 'en' => 'Gedimino Ave. 1', 'lt' => 'Gedimino pr. 1', 'pl' => 'Gedimino pr. 1'],
                        'phone' => '+370 600 00000',
                        'email' => 'vilnius@drivepro.test',
                        'sort_order' => 10,
                        'coordinates' => [54.6872, 25.2797],
                    ],
                    'kaunas-center' => [
                        'code' => 'LT_KAUNAS_CENTER',
                        'name' => ['ru' => 'DrivePro Academy Каунас', 'en' => 'DrivePro Academy Kaunas', 'lt' => 'DrivePro Academy Kauno filialas', 'pl' => 'DrivePro Academy Kowno'],
                        'city' => ['ru' => 'Каунас', 'en' => 'Kaunas', 'lt' => 'Kauno miestas', 'pl' => 'Kowno'],
                        'address' => ['ru' => 'Laisves al. 10', 'en' => 'Laisves Ave. 10', 'lt' => 'Laisves al. 10', 'pl' => 'Laisves al. 10'],
                        'phone' => '+370 600 00010',
                        'email' => 'kaunas@drivepro.test',
                        'sort_order' => 20,
                        'coordinates' => [54.8985, 23.9036],
                    ],
                    'klaipeda-harbor' => [
                        'code' => 'LT_KLAIPEDA_HARBOR',
                        'name' => ['ru' => 'DrivePro Academy Клайпеда', 'en' => 'DrivePro Academy Klaipeda', 'lt' => 'DrivePro Academy Klaipedos filialas', 'pl' => 'DrivePro Academy Klajpeda'],
                        'city' => ['ru' => 'Клайпеда', 'en' => 'Klaipeda', 'lt' => 'Klaipeda', 'pl' => 'Klajpeda'],
                        'address' => ['ru' => 'Taikos pr. 24', 'en' => 'Taikos Ave. 24', 'lt' => 'Taikos pr. 24', 'pl' => 'Taikos pr. 24'],
                        'phone' => '+370 600 00020',
                        'email' => 'klaipeda@drivepro.test',
                        'sort_order' => 30,
                        'coordinates' => [55.7033, 21.1443],
                    ],
                ],
            ],
            [
                'name' => ['ru' => 'Латвия', 'en' => 'Latvia', 'lt' => 'Latvija', 'pl' => 'Lotwa'],
                'branches' => [
                    'riga-center' => [
                        'code' => 'LV_RIGA_CENTER',
                        'name' => ['ru' => 'DrivePro Academy Рига', 'en' => 'DrivePro Academy Riga', 'lt' => 'DrivePro Academy Rygos filialas', 'pl' => 'DrivePro Academy Ryga'],
                        'city' => ['ru' => 'Рига', 'en' => 'Riga', 'lt' => 'Ryga', 'pl' => 'Ryga'],
                        'address' => ['ru' => 'Brivibas iela 40', 'en' => 'Brivibas Street 40', 'lt' => 'Brivibas iela 40', 'pl' => 'Brivibas iela 40'],
                        'phone' => '+371 200 00010',
                        'email' => 'riga@drivepro.test',
                        'sort_order' => 110,
                        'coordinates' => [56.9496, 24.1052],
                    ],
                    'daugavpils-east' => [
                        'code' => 'LV_DAUGAVPILS_EAST',
                        'name' => ['ru' => 'DrivePro Academy Даугавпилс', 'en' => 'DrivePro Academy Daugavpils', 'lt' => 'DrivePro Academy Daugpilio filialas', 'pl' => 'DrivePro Academy Dyneburg'],
                        'city' => ['ru' => 'Даугавпилс', 'en' => 'Daugavpils', 'lt' => 'Daugpilis', 'pl' => 'Dyneburg'],
                        'address' => ['ru' => 'Rigas iela 12', 'en' => 'Rigas Street 12', 'lt' => 'Rigas iela 12', 'pl' => 'Rigas iela 12'],
                        'phone' => '+371 200 00020',
                        'email' => 'daugavpils@drivepro.test',
                        'sort_order' => 120,
                        'coordinates' => [55.8747, 26.5362],
                    ],
                    'liepaja-coast' => [
                        'code' => 'LV_LIEPAJA_COAST',
                        'name' => ['ru' => 'DrivePro Academy Лиепая', 'en' => 'DrivePro Academy Liepaja', 'lt' => 'DrivePro Academy Liepojos filialas', 'pl' => 'DrivePro Academy Lipawa'],
                        'city' => ['ru' => 'Лиепая', 'en' => 'Liepaja', 'lt' => 'Liepoja', 'pl' => 'Lipawa'],
                        'address' => ['ru' => 'Kungu iela 8', 'en' => 'Kungu Street 8', 'lt' => 'Kungu iela 8', 'pl' => 'Kungu iela 8'],
                        'phone' => '+371 200 00030',
                        'email' => 'liepaja@drivepro.test',
                        'sort_order' => 130,
                        'coordinates' => [56.5047, 21.0108],
                    ],
                ],
            ],
            [
                'name' => ['ru' => 'Эстония', 'en' => 'Estonia', 'lt' => 'Estija', 'pl' => 'Estonia'],
                'branches' => [
                    'tallinn-north' => [
                        'code' => 'EE_TALLINN_NORTH',
                        'name' => ['ru' => 'DrivePro Academy Таллин', 'en' => 'DrivePro Academy Tallinn', 'lt' => 'DrivePro Academy Talino filialas', 'pl' => 'DrivePro Academy Tallinn'],
                        'city' => ['ru' => 'Таллин', 'en' => 'Tallinn', 'lt' => 'Talinas', 'pl' => 'Tallinn'],
                        'address' => ['ru' => 'Narva mnt 18', 'en' => 'Narva Road 18', 'lt' => 'Narva mnt 18', 'pl' => 'Narva mnt 18'],
                        'phone' => '+372 500 00010',
                        'email' => 'tallinn@drivepro.test',
                        'sort_order' => 210,
                        'coordinates' => [59.4370, 24.7536],
                    ],
                    'tartu-campus' => [
                        'code' => 'EE_TARTU_CAMPUS',
                        'name' => ['ru' => 'DrivePro Academy Тарту', 'en' => 'DrivePro Academy Tartu', 'lt' => 'DrivePro Academy Tartu filialas', 'pl' => 'DrivePro Academy Tartu'],
                        'city' => ['ru' => 'Тарту', 'en' => 'Tartu', 'lt' => 'Tartu', 'pl' => 'Tartu'],
                        'address' => ['ru' => 'Riia 15', 'en' => 'Riia 15', 'lt' => 'Riia 15', 'pl' => 'Riia 15'],
                        'phone' => '+372 500 00020',
                        'email' => 'tartu@drivepro.test',
                        'sort_order' => 220,
                        'coordinates' => [58.3776, 26.7290],
                    ],
                    'parnu-resort' => [
                        'code' => 'EE_PARNU_RESORT',
                        'name' => ['ru' => 'DrivePro Academy Пярну', 'en' => 'DrivePro Academy Parnu', 'lt' => 'DrivePro Academy Pernu filialas', 'pl' => 'DrivePro Academy Parnawa'],
                        'city' => ['ru' => 'Пярну', 'en' => 'Parnu', 'lt' => 'Pernu', 'pl' => 'Parnawa'],
                        'address' => ['ru' => 'Akadeemia 6', 'en' => 'Akadeemia 6', 'lt' => 'Akadeemia 6', 'pl' => 'Akadeemia 6'],
                        'phone' => '+372 500 00030',
                        'email' => 'parnu@drivepro.test',
                        'sort_order' => 230,
                        'coordinates' => [58.3859, 24.4971],
                    ],
                ],
            ],
            [
                'name' => ['ru' => 'Польша', 'en' => 'Poland', 'lt' => 'Lenkija', 'pl' => 'Polska'],
                'branches' => [
                    'warsaw-central' => [
                        'code' => 'PL_WARSAW_CENTRAL',
                        'name' => ['ru' => 'DrivePro Academy Варшава', 'en' => 'DrivePro Academy Warsaw', 'lt' => 'DrivePro Academy Varsuvos filialas', 'pl' => 'DrivePro Academy Warszawa'],
                        'city' => ['ru' => 'Варшава', 'en' => 'Warsaw', 'lt' => 'Varsuva', 'pl' => 'Warszawa'],
                        'address' => ['ru' => 'Marszalkowska 25', 'en' => 'Marszalkowska 25', 'lt' => 'Marszalkowska 25', 'pl' => 'Marszalkowska 25'],
                        'phone' => '+48 500 000 010',
                        'email' => 'warsaw@drivepro.test',
                        'sort_order' => 310,
                        'coordinates' => [52.2297, 21.0122],
                    ],
                    'krakow-old-town' => [
                        'code' => 'PL_KRAKOW_OLD_TOWN',
                        'name' => ['ru' => 'DrivePro Academy Краков', 'en' => 'DrivePro Academy Krakow', 'lt' => 'DrivePro Academy Krokuvos filialas', 'pl' => 'DrivePro Academy Krakow'],
                        'city' => ['ru' => 'Краков', 'en' => 'Krakow', 'lt' => 'Krokuva', 'pl' => 'Krakow'],
                        'address' => ['ru' => 'Dluga 14', 'en' => 'Dluga 14', 'lt' => 'Dluga 14', 'pl' => 'Dluga 14'],
                        'phone' => '+48 500 000 020',
                        'email' => 'krakow@drivepro.test',
                        'sort_order' => 320,
                        'coordinates' => [50.0647, 19.9450],
                    ],
                    'gdansk-port' => [
                        'code' => 'PL_GDANSK_PORT',
                        'name' => ['ru' => 'DrivePro Academy Гданьск', 'en' => 'DrivePro Academy Gdansk', 'lt' => 'DrivePro Academy Gdansko filialas', 'pl' => 'DrivePro Academy Gdansk'],
                        'city' => ['ru' => 'Гданьск', 'en' => 'Gdansk', 'lt' => 'Gdanskas', 'pl' => 'Gdansk'],
                        'address' => ['ru' => 'Dlugie Ogrody 6', 'en' => 'Dlugie Ogrody 6', 'lt' => 'Dlugie Ogrody 6', 'pl' => 'Dlugie Ogrody 6'],
                        'phone' => '+48 500 000 030',
                        'email' => 'gdansk@drivepro.test',
                        'sort_order' => 330,
                        'coordinates' => [54.3520, 18.6466],
                    ],
                ],
            ],
            [
                'name' => ['ru' => 'Германия', 'en' => 'Germany', 'lt' => 'Vokietija', 'pl' => 'Niemcy'],
                'branches' => [
                    'berlin-mitte' => [
                        'code' => 'DE_BERLIN_MITTE',
                        'name' => ['ru' => 'DrivePro Academy Берлин', 'en' => 'DrivePro Academy Berlin', 'lt' => 'DrivePro Academy Berlyno filialas', 'pl' => 'DrivePro Academy Berlin'],
                        'city' => ['ru' => 'Берлин', 'en' => 'Berlin', 'lt' => 'Berlynas', 'pl' => 'Berlin'],
                        'address' => ['ru' => 'Friedrichstrasse 90', 'en' => 'Friedrichstrasse 90', 'lt' => 'Friedrichstrasse 90', 'pl' => 'Friedrichstrasse 90'],
                        'phone' => '+49 30 00000010',
                        'email' => 'berlin@drivepro.test',
                        'sort_order' => 410,
                        'coordinates' => [52.5200, 13.4050],
                    ],
                    'hamburg-hafen' => [
                        'code' => 'DE_HAMBURG_HAFEN',
                        'name' => ['ru' => 'DrivePro Academy Гамбург', 'en' => 'DrivePro Academy Hamburg', 'lt' => 'DrivePro Academy Hamburgo filialas', 'pl' => 'DrivePro Academy Hamburg'],
                        'city' => ['ru' => 'Гамбург', 'en' => 'Hamburg', 'lt' => 'Hamburgas', 'pl' => 'Hamburg'],
                        'address' => ['ru' => 'Hafenstrasse 20', 'en' => 'Hafenstrasse 20', 'lt' => 'Hafenstrasse 20', 'pl' => 'Hafenstrasse 20'],
                        'phone' => '+49 40 00000020',
                        'email' => 'hamburg@drivepro.test',
                        'sort_order' => 420,
                        'coordinates' => [53.5511, 9.9937],
                    ],
                    'munich-south' => [
                        'code' => 'DE_MUNICH_SOUTH',
                        'name' => ['ru' => 'DrivePro Academy Мюнхен', 'en' => 'DrivePro Academy Munich', 'lt' => 'DrivePro Academy Miuncheno filialas', 'pl' => 'DrivePro Academy Monachium'],
                        'city' => ['ru' => 'Мюнхен', 'en' => 'Munich', 'lt' => 'Miunchenas', 'pl' => 'Monachium'],
                        'address' => ['ru' => 'Sendlinger Strasse 9', 'en' => 'Sendlinger Strasse 9', 'lt' => 'Sendlinger Strasse 9', 'pl' => 'Sendlinger Strasse 9'],
                        'phone' => '+49 89 00000030',
                        'email' => 'munich@drivepro.test',
                        'sort_order' => 430,
                        'coordinates' => [48.1351, 11.5820],
                    ],
                ],
            ],
        ];
    }
}
