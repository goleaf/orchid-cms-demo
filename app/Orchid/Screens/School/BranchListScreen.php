<?php

declare(strict_types=1);

namespace App\Orchid\Screens\School;

use App\Models\Branch;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class BranchListScreen extends Screen
{
    public function query(): iterable
    {
        return [
            'branches' => Branch::query()
                ->forAdminList()
                ->withCount(['students', 'instructors', 'vehicles', 'groups'])
                ->orderBy('city')
                ->simplePaginate(15),
        ];
    }

    public function name(): ?string
    {
        return 'Branches';
    }

    public function description(): ?string
    {
        return 'Filials, operating locations, and branch-level capacity.';
    }

    public function permission(): iterable
    {
        return ['platform.operations.branches'];
    }

    public function commandBar(): iterable
    {
        return [];
    }

    public function layout(): iterable
    {
        return [
            Layout::table('branches', [
                TD::make('name', 'Branch')
                    ->render(fn (Branch $branch): string => $branch->name),
                TD::make('city', 'City')
                    ->render(fn (Branch $branch): string => $branch->city),
                TD::make('address', 'Address')
                    ->render(fn (Branch $branch): string => $branch->address),
                TD::make('students_count', 'Students')
                    ->render(fn (Branch $branch): string => (string) $branch->students_count)
                    ->alignCenter(),
                TD::make('instructors_count', 'Instructors')
                    ->render(fn (Branch $branch): string => (string) $branch->instructors_count)
                    ->alignCenter(),
                TD::make('vehicles_count', 'Vehicles')
                    ->render(fn (Branch $branch): string => (string) $branch->vehicles_count)
                    ->alignCenter(),
                TD::make('groups_count', 'Groups')
                    ->render(fn (Branch $branch): string => (string) $branch->groups_count)
                    ->alignCenter(),
                TD::make('is_active', 'Active')
                    ->render(fn (Branch $branch): string => $branch->is_active ? 'Yes' : 'No')
                    ->alignCenter(),
            ]),
        ];
    }
}
