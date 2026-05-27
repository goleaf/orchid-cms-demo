<?php

namespace App\Actions;

use App\Models\Vehicle;

class GetFleetDirectoryAction
{
    /**
     * @return array<string, mixed>
     */
    public function handle(): array
    {
        return [
            'vehicles' => Vehicle::query()
                ->forFleetList()
                ->with([
                    'branch:id,name,name_translations,city,city_translations',
                    'instructor:id,name',
                ])
                ->orderBy('make')
                ->orderBy('model')
                ->simplePaginate(12)
                ->withQueryString(),
            'seoTitle' => tkey('website.vehicles.seo.title'),
            'seoDescription' => tkey('website.vehicles.seo.description'),
        ];
    }
}
