<?php

declare(strict_types=1);

namespace App\Orchid\Screens\School;

use App\Models\Vehicle;
use App\Support\LocalizedLabel;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class FleetListScreen extends Screen
{
    public function query(): iterable
    {
        return [
            'vehicles' => Vehicle::query()
                ->forFleetList()
                ->with([
                    'branch:id,name,city',
                    'instructor:id,name',
                ])
                ->orderBy('registration_number')
                ->simplePaginate(15),
        ];
    }

    public function name(): ?string
    {
        return tkey('operations.fleet.title');
    }

    public function description(): ?string
    {
        return tkey('operations.fleet.description');
    }

    public function permission(): iterable
    {
        return ['platform.fleet.vehicles'];
    }

    public function commandBar(): iterable
    {
        return [];
    }

    public function layout(): iterable
    {
        return [
            Layout::table('vehicles', [
                TD::make('registration_number', tkey('operations.columns.registration_number'))
                    ->render(fn (Vehicle $vehicle): string => $vehicle->registration_number),
                TD::make('vehicle', tkey('operations.columns.vehicle'))
                    ->render(fn (Vehicle $vehicle): string => "{$vehicle->make} {$vehicle->model}"),
                TD::make('branch', tkey('operations.columns.branch'))
                    ->render(fn (Vehicle $vehicle): string => $vehicle->branch->name),
                TD::make('instructor', tkey('operations.columns.instructor'))
                    ->render(fn (Vehicle $vehicle): string => $vehicle->instructor?->name ?? '-'),
                TD::make('license_category', tkey('operations.columns.category'))
                    ->render(fn (Vehicle $vehicle): string => $vehicle->license_category)
                    ->alignCenter(),
                TD::make('transmission', tkey('operations.columns.transmission'))
                    ->render(fn (Vehicle $vehicle): string => LocalizedLabel::for('website.transmissions', $vehicle->transmission)),
                TD::make('status', tkey('operations.columns.status'))
                    ->render(fn (Vehicle $vehicle): string => LocalizedLabel::for('operations.statuses.vehicles', $vehicle->status)),
                TD::make('next_service_at', tkey('operations.columns.service'))
                    ->render(fn (Vehicle $vehicle): string => $vehicle->next_service_at?->toDateString() ?? '-'),
                TD::make('next_inspection_at', tkey('operations.columns.inspection'))
                    ->render(fn (Vehicle $vehicle): string => $vehicle->next_inspection_at?->toDateString() ?? '-'),
            ]),
        ];
    }
}
