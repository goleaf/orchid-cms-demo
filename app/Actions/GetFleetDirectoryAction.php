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
                    'branch:id,name,city',
                    'instructor:id,name',
                ])
                ->orderBy('make')
                ->orderBy('model')
                ->simplePaginate(12)
                ->withQueryString(),
            'seoTitle' => 'Training fleet | DrivePro Academy',
            'seoDescription' => 'Training cars with gearbox, category, branch, instructor, availability, service status, and public characteristics.',
        ];
    }
}
