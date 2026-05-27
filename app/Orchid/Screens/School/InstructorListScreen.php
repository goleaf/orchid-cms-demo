<?php

declare(strict_types=1);

namespace App\Orchid\Screens\School;

use App\Models\Instructor;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class InstructorListScreen extends Screen
{
    public function query(): iterable
    {
        return [
            'instructors' => Instructor::query()
                ->forAdminList()
                ->with('branch:id,name,city')
                ->withCount(['groups', 'lessons', 'vehicles'])
                ->orderBy('name')
                ->simplePaginate(15),
        ];
    }

    public function name(): ?string
    {
        return 'Instructors';
    }

    public function description(): ?string
    {
        return 'Instructor roster, workload, vehicles, and teaching categories.';
    }

    public function permission(): iterable
    {
        return ['platform.operations.instructors'];
    }

    public function commandBar(): iterable
    {
        return [];
    }

    public function layout(): iterable
    {
        return [
            Layout::table('instructors', [
                TD::make('name', 'Instructor')
                    ->render(fn (Instructor $instructor): string => $instructor->name),
                TD::make('branch', 'Branch')
                    ->render(fn (Instructor $instructor): string => $instructor->branch->name),
                TD::make('email', 'Email')
                    ->render(fn (Instructor $instructor): string => $instructor->email),
                TD::make('phone', 'Phone')
                    ->render(fn (Instructor $instructor): string => $instructor->phone ?? '-'),
                TD::make('categories', 'Categories')
                    ->render(fn (Instructor $instructor): string => implode(', ', $instructor->categories ?? [])),
                TD::make('groups_count', 'Groups')
                    ->render(fn (Instructor $instructor): string => (string) $instructor->groups_count)
                    ->alignCenter(),
                TD::make('lessons_count', 'Lessons')
                    ->render(fn (Instructor $instructor): string => (string) $instructor->lessons_count)
                    ->alignCenter(),
                TD::make('vehicles_count', 'Vehicles')
                    ->render(fn (Instructor $instructor): string => (string) $instructor->vehicles_count)
                    ->alignCenter(),
                TD::make('status', 'Status')
                    ->render(fn (Instructor $instructor): string => str($instructor->status->value)->replace('_', ' ')->title()->toString()),
            ]),
        ];
    }
}
