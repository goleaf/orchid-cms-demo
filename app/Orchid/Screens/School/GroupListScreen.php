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
        return tkey('website.admin.groups.title');
    }

    public function description(): ?string
    {
        return tkey('website.admin.groups.description');
    }

    public function permission(): iterable
    {
        return ['platform.operations.groups', 'website.manage_groups', 'education.groups.view'];
    }

    public function commandBar(): iterable
    {
        return [
            Link::make(tkey('website.admin.groups.create_title'))
                ->icon('bs.plus-circle')
                ->route('platform.website.groups.create'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::table('groups', [
                TD::make('code', tkey('website.groups.columns.code'))
                    ->render(fn (TrainingGroup $group): string => $group->code),
                TD::make('name', tkey('website.groups.columns.group'))
                    ->render(fn (TrainingGroup $group): string => $group->displayName()),
                TD::make('branch', tkey('website.groups.columns.branch'))
                    ->render(fn (TrainingGroup $group): string => $group->branch->displayName()),
                TD::make('program', tkey('website.groups.columns.course'))
                    ->render(fn (TrainingGroup $group): string => $group->trainingProgram->displayTitle()),
                TD::make('instructor', tkey('website.groups.columns.instructor'))
                    ->render(fn (TrainingGroup $group): string => $group->instructor?->name ?? '-'),
                TD::make('starts_on', tkey('website.groups.columns.start'))
                    ->render(fn (TrainingGroup $group): string => $group->starts_on?->toDateString() ?? '-'),
                TD::make('capacity', tkey('website.groups.columns.seats'))
                    ->render(fn (TrainingGroup $group): string => tkey('website.groups.seats_value', [
                        'available' => $group->seatsAvailable(),
                        'capacity' => $group->capacity,
                    ]))
                    ->alignCenter(),
                TD::make('available', tkey('website.admin.groups.columns.visible_on_site'))
                    ->render(fn (TrainingGroup $group): string => (string) $group->seatsAvailable())
                    ->alignCenter(),
                TD::make('status', tkey('crm.leads.fields.status'))
                    ->render(fn (TrainingGroup $group): string => $group->status->label()),
                TD::make('actions', tkey('crm.leads.columns.actions'))
                    ->alignRight()
                    ->render(fn (TrainingGroup $group): string => (string) Link::make(tkey('common.actions.edit'))
                        ->icon('bs.pencil')
                        ->route('platform.website.groups.edit', $group)),
            ]),
        ];
    }
}
