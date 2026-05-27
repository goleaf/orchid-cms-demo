<?php

declare(strict_types=1);

namespace App\Orchid\Screens\School;

use App\Actions\SaveTrainingGroupAction;
use App\Enums\GroupStatus;
use App\Http\Requests\TrainingGroupRequest;
use App\Models\Branch;
use App\Models\Instructor;
use App\Models\TrainingGroup;
use App\Models\TrainingProgram;
use App\Orchid\Support\TranslatableFields;
use Illuminate\Http\RedirectResponse;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class GroupEditScreen extends Screen
{
    public ?TrainingGroup $group = null;

    /**
     * @var array<int, string>
     */
    private array $branches = [];

    /**
     * @var array<int, string>
     */
    private array $programs = [];

    /**
     * @var array<int, string>
     */
    private array $instructors = [];

    public function query(?TrainingGroup $group = null): iterable
    {
        $groupModel = $group?->exists
            ? $group
            : new TrainingGroup([
                'status' => GroupStatus::Open,
                'capacity' => 12,
                'places_taken' => 0,
                'is_visible_on_site' => true,
            ]);
        $this->group = $groupModel;

        $this->branches = Branch::query()
            ->forAdminList()
            ->orderBy('sort_order')
            ->orderBy('city')
            ->get()
            ->mapWithKeys(fn (Branch $branch): array => [$branch->id => $branch->displayName()])
            ->all();
        $this->programs = TrainingProgram::query()
            ->forAcademyList()
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get()
            ->mapWithKeys(fn (TrainingProgram $program): array => [$program->id => $program->displayTitle()])
            ->all();
        $this->instructors = Instructor::query()
            ->forAdminList()
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();

        return [
            'group' => $groupModel,
            'group.status' => $groupModel->status instanceof GroupStatus
                ? $groupModel->status->value
                : ($groupModel->status ?? GroupStatus::Open->value),
            'group.meeting_days' => implode(', ', $groupModel->meeting_days ?? []),
            'group.meeting_time' => $groupModel->meeting_time?->format('H:i'),
            'name_translations' => $groupModel->getTranslations('name') ?: ['ru' => $groupModel->name],
        ];
    }

    public function name(): ?string
    {
        return $this->group?->exists
            ? tkey('website.admin.groups.edit_title')
            : tkey('website.admin.groups.create_title');
    }

    public function description(): ?string
    {
        return tkey('website.admin.groups.description');
    }

    public function permission(): iterable
    {
        return ['platform.operations.groups', 'website.manage_groups'];
    }

    public function commandBar(): iterable
    {
        return [
            Link::make(tkey('common.actions.back'))
                ->icon('bs.arrow-left')
                ->route('platform.website.groups'),

            Button::make(tkey('common.actions.save'))
                ->icon('bs.check-lg')
                ->method('save'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::rows([
                Input::make('group.id')->type('hidden'),
                Input::make('group.code')
                    ->title(tkey('website.groups.columns.code'))
                    ->required(),
                Select::make('group.training_program_id')
                    ->title(tkey('crm.leads.fields.course'))
                    ->options($this->programs)
                    ->required(),
                Select::make('group.branch_id')
                    ->title(tkey('crm.leads.fields.branch'))
                    ->options($this->branches)
                    ->required(),
                Select::make('group.instructor_id')
                    ->title(tkey('crm.leads.fields.instructor'))
                    ->options($this->instructors)
                    ->empty(tkey('crm.leads.empty.no_instructor')),
                Select::make('group.status')
                    ->title(tkey('crm.leads.fields.status'))
                    ->options(GroupStatus::options())
                    ->required(),
                Input::make('group.capacity')
                    ->type('number')
                    ->title(tkey('website.admin.groups.fields.capacity'))
                    ->required(),
                Input::make('group.places_taken')
                    ->type('number')
                    ->title(tkey('website.admin.groups.fields.places_taken')),
                Input::make('group.starts_on')
                    ->type('date')
                    ->title(tkey('website.groups.columns.start')),
                Input::make('group.ends_on')
                    ->type('date')
                    ->title(tkey('website.admin.groups.fields.ends_on')),
                Input::make('group.meeting_days')
                    ->title(tkey('website.groups.columns.days')),
                Input::make('group.meeting_time')
                    ->type('time')
                    ->title(tkey('website.groups.columns.time')),
                Input::make('group.classroom')
                    ->title(tkey('website.admin.groups.fields.classroom')),
                Select::make('group.is_visible_on_site')
                    ->title(tkey('website.admin.groups.fields.is_visible_on_site'))
                    ->options([
                        1 => tkey('common.status.yes'),
                        0 => tkey('common.status.no'),
                    ]),
            ])->title(tkey('website.admin.sections.system')),

            TranslatableFields::input('name', 'website.admin.groups.fields.name', [
                'title_key' => 'website.admin.sections.content',
                'maxlength' => 255,
                'required' => true,
            ]),
        ];
    }

    public function save(TrainingGroupRequest $request, SaveTrainingGroupAction $save): RedirectResponse
    {
        $group = filled($request->input('group.id'))
            ? TrainingGroup::query()->findOrFail($request->integer('group.id'))
            : new TrainingGroup;

        $save->handle($group, $request->groupData());

        Toast::info(tkey('website.admin.groups.messages.saved'));

        return redirect()->route('platform.website.groups');
    }
}
