<?php

declare(strict_types=1);

namespace App\Orchid\Screens\School;

use App\Actions\CreateOrUpdateTrainingGroupStatusAction;
use App\Actions\DeleteTrainingGroupStatusAction;
use App\Http\Requests\Education\TrainingGroupStatusRequest;
use App\Models\TrainingGroupStatus;
use App\Orchid\Support\TranslatableFields;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Switcher;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class TrainingGroupStatusListScreen extends Screen
{
    public ?TrainingGroupStatus $status = null;

    public function query(Request $request): iterable
    {
        $this->status = $request->filled('status_id')
            ? TrainingGroupStatus::query()->findOrFail($request->integer('status_id'))
            : new TrainingGroupStatus([
                'is_active' => true,
                'sort_order' => 0,
            ]);

        return [
            'statuses' => TrainingGroupStatus::query()
                ->select([
                    'id',
                    'code',
                    'name',
                    'name_translations',
                    'color',
                    'sort_order',
                    'is_system',
                    'is_default',
                    'is_active',
                    'is_public',
                    'accepts_enrollments',
                    'is_in_progress',
                    'is_final',
                ])
                ->ordered()
                ->simplePaginate(20)
                ->withQueryString(),
            'status' => $this->status,
            'status.name_translations' => $this->status->name_translations ?? [],
            'status.description_translations' => $this->status->description_translations ?? [],
        ];
    }

    public function name(): ?string
    {
        return tkey('education.statuses.title');
    }

    public function description(): ?string
    {
        return tkey('education.statuses.description');
    }

    public function permission(): iterable
    {
        return ['education.manage_statuses'];
    }

    public function commandBar(): iterable
    {
        return [
            Link::make(tkey('common.actions.create'))
                ->icon('bs.plus-circle')
                ->route('platform.education.group-statuses'),

            Button::make(tkey('common.actions.save'))
                ->icon('bs.check2-circle')
                ->method('save'),

            Button::make(tkey('common.actions.delete'))
                ->icon('bs.trash3')
                ->method('delete')
                ->parameters(['record' => $this->status?->getKey()])
                ->confirm(tkey('crm.dictionaries.messages.delete_confirm'))
                ->canSee(($this->status?->exists ?? false) && ! (bool) $this->status?->is_system),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::rows([
                Input::make('status.id')->type('hidden'),
                Input::make('status.code')
                    ->title(tkey('crm.dictionaries.fields.code'))
                    ->required(),
                Input::make('status.name')
                    ->title(tkey('crm.dictionaries.fields.name')),
                Input::make('status.color')
                    ->title(tkey('crm.dictionaries.fields.color')),
                Input::make('status.sort_order')
                    ->type('number')
                    ->title(tkey('crm.dictionaries.fields.sort_order')),
                Switcher::make('status.is_default')
                    ->sendTrueOrFalse()
                    ->title(tkey('crm.dictionaries.fields.is_default')),
                Switcher::make('status.is_active')
                    ->sendTrueOrFalse()
                    ->title(tkey('crm.dictionaries.fields.is_active')),
                Switcher::make('status.is_public')
                    ->sendTrueOrFalse()
                    ->title(tkey('education.statuses.fields.is_public')),
                Switcher::make('status.accepts_enrollments')
                    ->sendTrueOrFalse()
                    ->title(tkey('education.statuses.fields.accepts_enrollments')),
                Switcher::make('status.is_in_progress')
                    ->sendTrueOrFalse()
                    ->title(tkey('education.statuses.fields.is_in_progress')),
                Switcher::make('status.is_final')
                    ->sendTrueOrFalse()
                    ->title(tkey('crm.dictionaries.fields.is_final')),
            ])->title($this->status?->exists ? tkey('crm.dictionaries.edit_title') : tkey('crm.dictionaries.create_title')),

            TranslatableFields::input('status.name', 'crm.dictionaries.fields.name_translations', [
                'maxlength' => 255,
                'required' => true,
            ]),

            TranslatableFields::textarea('status.description', 'crm.dictionaries.fields.description_translations', [
                'rows' => 3,
                'maxlength' => 1000,
            ]),

            Layout::table('statuses', [
                TD::make('code', tkey('crm.dictionaries.fields.code'))
                    ->render(fn (TrainingGroupStatus $status): string => (string) Link::make($status->code)
                        ->route('platform.education.group-statuses', ['status_id' => $status->id])),
                TD::make('name', tkey('crm.dictionaries.fields.name'))
                    ->render(fn (TrainingGroupStatus $status): string => $status->displayName()),
                TD::make('is_default', tkey('crm.dictionaries.fields.is_default'))
                    ->render(fn (TrainingGroupStatus $status): string => $status->is_default ? tkey('common.status.yes') : tkey('common.status.no')),
                TD::make('accepts_enrollments', tkey('education.statuses.fields.accepts_enrollments'))
                    ->render(fn (TrainingGroupStatus $status): string => $status->accepts_enrollments ? tkey('common.status.yes') : tkey('common.status.no')),
                TD::make('is_active', tkey('crm.dictionaries.fields.is_active'))
                    ->render(fn (TrainingGroupStatus $status): string => $status->is_active ? tkey('common.status.active') : tkey('common.status.inactive')),
                TD::make('sort_order', tkey('crm.dictionaries.fields.sort_order'))
                    ->render(fn (TrainingGroupStatus $status): string => (string) $status->sort_order),
                TD::make('actions', tkey('crm.leads.columns.actions'))
                    ->alignRight()
                    ->render(fn (TrainingGroupStatus $status): DropDown => DropDown::make()
                        ->icon('bs.three-dots-vertical')
                        ->list([
                            Link::make(tkey('common.actions.edit'))
                                ->icon('bs.pencil')
                                ->route('platform.education.group-statuses', ['status_id' => $status->id]),
                            Button::make(tkey('common.actions.delete'))
                                ->icon('bs.trash3')
                                ->method('delete')
                                ->parameters(['record' => $status->id])
                                ->confirm(tkey('crm.dictionaries.messages.delete_confirm'))
                                ->canSee(! (bool) $status->is_system),
                        ])),
            ]),
        ];
    }

    public function save(TrainingGroupStatusRequest $request, CreateOrUpdateTrainingGroupStatusAction $saveStatus): RedirectResponse
    {
        $saveStatus->handle($request->statusId(), $request->statusData());

        Toast::info(tkey('crm.dictionaries.messages.saved'));

        return redirect()->route('platform.education.group-statuses');
    }

    public function delete(Request $request, DeleteTrainingGroupStatusAction $deleteStatus): RedirectResponse
    {
        abort_unless($request->user()?->hasAccess('education.manage_statuses'), 403);

        $deleteStatus->handle((int) $request->input('record'));

        Toast::info(tkey('crm.dictionaries.messages.deleted'));

        return redirect()->route('platform.education.group-statuses');
    }
}
