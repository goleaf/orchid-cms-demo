<?php

declare(strict_types=1);

namespace App\Orchid\Screens\School;

use App\Models\Vehicle;
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
        return 'Fleet';
    }

    public function description(): ?string
    {
        return 'Vehicles, instructors, service dates, inspections, and status.';
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
                TD::make('registration_number', 'Reg. no.')
                    ->render(fn (Vehicle $vehicle): string => $vehicle->registration_number),
                TD::make('vehicle', 'Vehicle')
                    ->render(fn (Vehicle $vehicle): string => "{$vehicle->make} {$vehicle->model}"),
                TD::make('branch', 'Branch')
                    ->render(fn (Vehicle $vehicle): string => $vehicle->branch->name),
                TD::make('instructor', 'Instructor')
                    ->render(fn (Vehicle $vehicle): string => $vehicle->instructor?->name ?? '-'),
                TD::make('license_category', 'Category')
                    ->render(fn (Vehicle $vehicle): string => $vehicle->license_category)
                    ->alignCenter(),
                TD::make('transmission', 'Transmission')
                    ->render(fn (Vehicle $vehicle): string => str($vehicle->transmission)->title()->toString()),
                TD::make('status', 'Status')
                    ->render(fn (Vehicle $vehicle): string => str($vehicle->status->value)->replace('_', ' ')->title()->toString()),
                TD::make('next_service_at', 'Service')
                    ->render(fn (Vehicle $vehicle): string => $vehicle->next_service_at?->toDateString() ?? '-'),
                TD::make('next_inspection_at', 'Inspection')
                    ->render(fn (Vehicle $vehicle): string => $vehicle->next_inspection_at?->toDateString() ?? '-'),
            ]),
        ];
    }
}
