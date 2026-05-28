<?php

declare(strict_types=1);

namespace App\Orchid\Screens\School;

use App\Actions\CreateOrUpdateStudentStatusAction;
use App\Actions\DeleteStudentStatusAction;
use App\Http\Requests\Students\DeleteStudentStatusRequest;
use App\Http\Requests\Students\StudentStatusRequest;
use App\Models\StudentStatus;
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

class StudentStatusListScreen extends Screen
{
    public ?StudentStatus $status = null;

    public function query(Request $request): iterable
    {
        $this->status = $request->filled('status_id')
            ? StudentStatus::query()->findOrFail($request->integer('status_id'))
            : new StudentStatus([
                'is_active' => true,
                'sort_order' => 0,
            ]);

        return [
            'statuses' => StudentStatus::query()
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
                    'is_final',
                    'is_blocked',
                    'is_archived',
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
        return tkey('menu.students.statuses');
    }

    public function description(): ?string
    {
        return tkey('students.dictionaries.statuses.description');
    }

    public function permission(): iterable
    {
        return ['students.manage_statuses'];
    }

    public function commandBar(): iterable
    {
        return [
            Link::make(tkey('common.actions.create'))
                ->icon('bs.plus-circle')
                ->route('platform.students.statuses'),

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
                Switcher::make('status.is_final')
                    ->sendTrueOrFalse()
                    ->title(tkey('crm.dictionaries.fields.is_final')),
                Switcher::make('status.is_blocked')
                    ->sendTrueOrFalse()
                    ->title(tkey('crm.dictionaries.fields.is_blocked')),
                Switcher::make('status.is_archived')
                    ->sendTrueOrFalse()
                    ->title(tkey('crm.dictionaries.fields.is_archived')),
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
                    ->render(fn (StudentStatus $status): string => (string) Link::make($status->code)
                        ->route('platform.students.statuses', ['status_id' => $status->id])),
                TD::make('name', tkey('crm.dictionaries.fields.name'))
                    ->render(fn (StudentStatus $status): string => $status->displayName()),
                TD::make('color', tkey('crm.dictionaries.fields.color'))
                    ->render(fn (StudentStatus $status): string => $status->color ?: '-'),
                TD::make('is_default', tkey('crm.dictionaries.fields.is_default'))
                    ->render(fn (StudentStatus $status): string => $status->is_default ? tkey('common.status.yes') : tkey('common.status.no')),
                TD::make('is_active', tkey('crm.dictionaries.fields.is_active'))
                    ->render(fn (StudentStatus $status): string => $status->is_active ? tkey('common.status.active') : tkey('common.status.inactive')),
                TD::make('sort_order', tkey('crm.dictionaries.fields.sort_order'))
                    ->render(fn (StudentStatus $status): string => (string) $status->sort_order),
                TD::make('actions', tkey('crm.leads.columns.actions'))
                    ->cantHide()
                    ->alignRight()
                    ->render(fn (StudentStatus $status): DropDown => DropDown::make()
                        ->icon('bs.three-dots-vertical')
                        ->list([
                            Link::make(tkey('common.actions.edit'))
                                ->icon('bs.pencil')
                                ->route('platform.students.statuses', ['status_id' => $status->id]),
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

    public function save(StudentStatusRequest $request, CreateOrUpdateStudentStatusAction $saveStatus): RedirectResponse
    {
        $saveStatus->handle($request->statusId(), $request->statusData());

        Toast::info(tkey('crm.dictionaries.messages.saved'));

        return redirect()->route('platform.students.statuses');
    }

    public function delete(DeleteStudentStatusRequest $request, DeleteStudentStatusAction $deleteStatus): RedirectResponse
    {
        $deleteStatus->handle($request->recordId());

        Toast::info(tkey('crm.dictionaries.messages.deleted'));

        return redirect()->route('platform.students.statuses');
    }
}
