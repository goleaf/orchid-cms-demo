<?php

declare(strict_types=1);

namespace App\Orchid\Screens\School;

use App\Models\TrainingGroup;
use Orchid\Screen\Actions\Link;
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
                    'branch:id,name,name_translations,city,city_translations',
                    'trainingProgram:id,title,title_translations,license_category',
                    'instructor:id,name',
                ])
                ->withCount('enrollments')
                ->orderBy('starts_on')
                ->simplePaginate(15),
        ];
    }

    public function name(): ?string
    {
        return tkey('education.groups.title');
    }

    public function description(): ?string
    {
        return tkey('education.groups.description');
    }

    public function permission(): iterable
    {
        return ['platform.operations.groups', 'website.manage_groups', 'education.groups.view'];
    }

    public function commandBar(): iterable
    {
        return [
            Link::make(tkey('education.groups.actions.create'))
                ->icon('bs.plus-circle')
                ->route('platform.education.groups.create'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::table('groups', [
                TD::make('code', tkey('education.groups.fields.code'))
                    ->render(fn (TrainingGroup $group): string => $group->code),
                TD::make('name', tkey('education.groups.fields.name'))
                    ->render(fn (TrainingGroup $group): string => $group->displayName()),
                TD::make('branch', tkey('education.groups.fields.branch'))
                    ->render(fn (TrainingGroup $group): string => $group->branch->displayName()),
                TD::make('program', tkey('education.groups.fields.course'))
                    ->render(fn (TrainingGroup $group): string => $group->trainingProgram->displayTitle()),
                TD::make('instructor', tkey('education.groups.fields.teacher'))
                    ->render(fn (TrainingGroup $group): string => $group->instructor?->name ?? '-'),
                TD::make('starts_on', tkey('education.groups.fields.start_date'))
                    ->render(fn (TrainingGroup $group): string => $group->starts_on?->toDateString() ?? '-'),
                TD::make('capacity', tkey('education.groups.fields.available_places'))
                    ->render(fn (TrainingGroup $group): string => tkey('website.groups.seats_value', [
                        'available' => $group->seatsAvailable(),
                        'capacity' => $group->capacity,
                    ]))
                    ->alignCenter(),
                TD::make('available', tkey('education.groups.fields.is_visible_on_site'))
                    ->render(fn (TrainingGroup $group): string => (string) $group->seatsAvailable())
                    ->alignCenter(),
                TD::make('status', tkey('education.groups.fields.status'))
                    ->render(fn (TrainingGroup $group): string => $group->status->label()),
                TD::make('actions', tkey('education.groups.actions.open'))
                    ->alignRight()
                    ->render(fn (TrainingGroup $group): string => (string) Link::make(tkey('education.groups.actions.edit'))
                        ->icon('bs.pencil')
                        ->route('platform.education.groups.edit', $group)),
            ]),
        ];
    }
}
