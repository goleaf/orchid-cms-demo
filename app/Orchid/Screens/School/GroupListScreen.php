<?php

declare(strict_types=1);

namespace App\Orchid\Screens\School;

use App\Models\TrainingGroup;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class GroupListScreen extends Screen
{
    public function query(): iterable
    {
        return [
            'groups' => TrainingGroup::query()
                ->operationalList()
                ->with([
                    'branch:id,name,city',
                    'trainingProgram:id,title,license_category',
                    'instructor:id,name',
                ])
                ->withCount('enrollments')
                ->orderBy('starts_on')
                ->simplePaginate(15),
        ];
    }

    public function name(): ?string
    {
        return 'Training groups';
    }

    public function description(): ?string
    {
        return 'Cohorts, seats, meeting rhythm, instructors, and program intake.';
    }

    public function permission(): iterable
    {
        return ['platform.operations.groups'];
    }

    public function commandBar(): iterable
    {
        return [];
    }

    public function layout(): iterable
    {
        return [
            Layout::table('groups', [
                TD::make('code', 'Code')
                    ->render(fn (TrainingGroup $group): string => $group->code),
                TD::make('name', 'Group')
                    ->render(fn (TrainingGroup $group): string => $group->name),
                TD::make('branch', 'Branch')
                    ->render(fn (TrainingGroup $group): string => $group->branch->name),
                TD::make('program', 'Program')
                    ->render(fn (TrainingGroup $group): string => $group->trainingProgram->title),
                TD::make('instructor', 'Instructor')
                    ->render(fn (TrainingGroup $group): string => $group->instructor?->name ?? '-'),
                TD::make('starts_on', 'Starts')
                    ->render(fn (TrainingGroup $group): string => $group->starts_on?->toDateString() ?? '-'),
                TD::make('capacity', 'Seats')
                    ->render(fn (TrainingGroup $group): string => $group->enrollments_count.'/'.$group->capacity)
                    ->alignCenter(),
                TD::make('available', 'Open')
                    ->render(fn (TrainingGroup $group): string => (string) $group->seatsAvailable())
                    ->alignCenter(),
                TD::make('status', 'Status')
                    ->render(fn (TrainingGroup $group): string => str($group->status->value)->replace('_', ' ')->title()->toString()),
            ]),
        ];
    }
}
