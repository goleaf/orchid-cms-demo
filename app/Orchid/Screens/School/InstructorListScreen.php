<?php

declare(strict_types=1);

namespace App\Orchid\Screens\School;

use App\Models\Instructor;
use App\Support\LocalizedLabel;
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
        return tkey('operations.instructors.title');
    }

    public function description(): ?string
    {
        return tkey('operations.instructors.description');
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
                TD::make('name', tkey('operations.columns.instructor'))
                    ->render(fn (Instructor $instructor): string => $instructor->name),
                TD::make('branch', tkey('operations.columns.branch'))
                    ->render(fn (Instructor $instructor): string => $instructor->branch->name),
                TD::make('email', tkey('operations.columns.email'))
                    ->render(fn (Instructor $instructor): string => $instructor->email),
                TD::make('phone', tkey('operations.columns.phone'))
                    ->render(fn (Instructor $instructor): string => $instructor->phone ?? '-'),
                TD::make('categories', tkey('operations.columns.categories'))
                    ->render(fn (Instructor $instructor): string => implode(', ', $instructor->categories ?? [])),
                TD::make('groups_count', tkey('operations.columns.groups'))
                    ->render(fn (Instructor $instructor): string => (string) $instructor->groups_count)
                    ->alignCenter(),
                TD::make('lessons_count', tkey('operations.columns.lessons'))
                    ->render(fn (Instructor $instructor): string => (string) $instructor->lessons_count)
                    ->alignCenter(),
                TD::make('vehicles_count', tkey('operations.columns.vehicles'))
                    ->render(fn (Instructor $instructor): string => (string) $instructor->vehicles_count)
                    ->alignCenter(),
                TD::make('status', tkey('operations.columns.status'))
                    ->render(fn (Instructor $instructor): string => LocalizedLabel::for('operations.statuses.instructors', $instructor->status)),
            ]),
        ];
    }
}
