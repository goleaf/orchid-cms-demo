<?php

namespace App\Orchid\Screens\Website;

use App\Actions\HideTrainingGroupFromSiteAction;
use App\Actions\ShowTrainingGroupOnSiteAction;
use App\Models\TrainingGroup;
use App\Orchid\Screens\Website\Concerns\BuildsWebsiteScreenPayloads;
use Illuminate\Http\RedirectResponse;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class WebsiteGroupListScreen extends Screen
{
    use BuildsWebsiteScreenPayloads;

    public function query(): iterable
    {
        return [
            'groups' => TrainingGroup::query()
                ->operationalList()
                ->with([
                    'branch:id,name,name_translations,city,city_translations',
                    'trainingProgram:id,title,title_translations,name_translations,slug',
                ])
                ->withCount('enrollments')
                ->ordered()
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
        return ['website.manage_groups'];
    }

    public function layout(): iterable
    {
        return [
            Layout::table('groups', [
                TD::make('group_number', tkey('website.groups.columns.code'))
                    ->render(fn (TrainingGroup $group): string => $group->group_number ?? $group->code ?? '-'),
                TD::make('name', tkey('website.groups.fields.name'))
                    ->render(fn (TrainingGroup $group): string => $group->displayName()),
                TD::make('course', tkey('website.groups.fields.course'))
                    ->render(fn (TrainingGroup $group): string => $group->trainingProgram?->displayTitle() ?? '-'),
                TD::make('branch', tkey('website.groups.fields.branch'))
                    ->render(fn (TrainingGroup $group): string => $group->branch?->displayName() ?? '-'),
                TD::make('start_date', tkey('website.groups.fields.start_date'))
                    ->render(fn (TrainingGroup $group): string => $group->starts_on?->toDateString() ?? '-'),
                TD::make('schedule_summary', tkey('website.groups.fields.schedule'))
                    ->render(fn (TrainingGroup $group): string => $group->displayScheduleSummary() ?? $group->meeting_time?->format('H:i') ?? '-'),
                TD::make('capacity', tkey('website.groups.fields.capacity'))
                    ->render(fn (TrainingGroup $group): string => (string) $group->capacity)
                    ->alignCenter(),
                TD::make('available_places', tkey('website.groups.fields.available_places'))
                    ->render(fn (TrainingGroup $group): string => (string) $group->available_places)
                    ->alignCenter(),
                TD::make('status', tkey('website.groups.fields.status'))
                    ->render(fn (TrainingGroup $group): string => $group->status->label()),
                TD::make('visible_on_site', tkey('website.admin.fields.is_visible_on_site'))
                    ->alignCenter()
                    ->render(fn (TrainingGroup $group): string => $this->booleanBadge($group->is_visible_on_site, 'website.admin.status.visible', 'website.admin.status.hidden')),
                TD::make('actions', tkey('crm.leads.columns.actions'))
                    ->alignRight()
                    ->render(fn (TrainingGroup $group): string => $this->groupActions($group)),
            ]),
        ];
    }

    public function showOnSite(ShowTrainingGroupOnSiteAction $show): RedirectResponse
    {
        $group = TrainingGroup::query()->findOrFail(request()->integer('id'));
        $show->handle($group);

        Toast::info(tkey('website.admin.groups.messages.shown'));

        return redirect()->route('platform.website.groups');
    }

    public function hideFromSite(HideTrainingGroupFromSiteAction $hide): RedirectResponse
    {
        $group = TrainingGroup::query()->findOrFail(request()->integer('id'));
        $hide->handle($group);

        Toast::info(tkey('website.admin.groups.messages.hidden'));

        return redirect()->route('platform.website.groups');
    }

    private function groupActions(TrainingGroup $group): string
    {
        return implode(' ', [
            (string) Link::make(tkey('website.admin.groups.actions.open_group'))
                ->icon('bs.box-arrow-in-right')
                ->route('platform.website.groups.edit', $group),
            (string) Link::make(tkey('website.admin.actions.preview'))
                ->icon('bs.box-arrow-up-right')
                ->route('site.apply', [
                    'program' => $group->training_program_id,
                    'branch' => $group->branch_id,
                    'group' => $group->id,
                ])
                ->target('_blank'),
            (string) Button::make(tkey('website.admin.groups.actions.show_on_site'))
                ->icon('bs.eye')
                ->method('showOnSite')
                ->parameters(['id' => $group->id])
                ->canSee(! $group->is_visible_on_site),
            (string) Button::make(tkey('website.admin.groups.actions.hide_from_site'))
                ->icon('bs.eye-slash')
                ->method('hideFromSite')
                ->parameters(['id' => $group->id])
                ->canSee($group->is_visible_on_site),
        ]);
    }
}
