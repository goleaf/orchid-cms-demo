<?php

declare(strict_types=1);

namespace App\Orchid\Screens\School;

use App\Actions\CreateOrUpdateEnrollmentStatusAction;
use App\Http\Requests\Students\EnrollmentStatusRequest;
use App\Models\EnrollmentStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Switcher;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class EnrollmentStatusListScreen extends Screen
{
    public ?EnrollmentStatus $status = null;

    public function query(Request $request): iterable
    {
        $this->status = $request->filled('status_id')
            ? EnrollmentStatus::query()->findOrFail($request->integer('status_id'))
            : new EnrollmentStatus([
                'is_active' => true,
                'sort_order' => 0,
            ]);

        return [
            'statuses' => EnrollmentStatus::query()
                ->orderBy('sort_order')
                ->orderBy('code')
                ->simplePaginate(20)
                ->withQueryString(),
            'status' => $this->status,
            'status.name_translations' => $this->status->name_translations ?? [],
            'status.description_translations' => $this->status->description_translations ?? [],
        ];
    }

    public function name(): ?string
    {
        return tkey('menu.students.enrollment_statuses');
    }

    public function description(): ?string
    {
        return tkey('students.dictionaries.enrollment_statuses.description');
    }

    public function permission(): iterable
    {
        return ['students.manage_statuses'];
    }

    public function commandBar(): iterable
    {
        return [
            Link::make(tkey('students.actions.create'))
                ->icon('bs.plus-circle')
                ->route('platform.students.enrollment-statuses'),

            Button::make(tkey('crm.dictionaries.actions.save'))
                ->icon('bs.check2-circle')
                ->method('save'),
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
                Input::make('status.name_translations.ru')
                    ->title(tkey('crm.dictionaries.fields.name_translations'))
                    ->required(),
                Input::make('status.name_translations.en')
                    ->title(tkey('crm.dictionaries.fields.name_translations').' EN'),
                Input::make('status.name_translations.lt')
                    ->title(tkey('crm.dictionaries.fields.name_translations').' LT'),
                Input::make('status.name_translations.pl')
                    ->title(tkey('crm.dictionaries.fields.name_translations').' PL'),
                Input::make('status.description_translations.ru')
                    ->title(tkey('crm.dictionaries.fields.description_translations')),
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
                Switcher::make('status.is_success')
                    ->sendTrueOrFalse()
                    ->title(tkey('crm.dictionaries.fields.is_success')),
                Switcher::make('status.is_cancelled')
                    ->sendTrueOrFalse()
                    ->title(tkey('crm.dictionaries.fields.is_cancelled')),
                Switcher::make('status.is_waiting_documents')
                    ->sendTrueOrFalse()
                    ->title(tkey('crm.dictionaries.fields.is_waiting_documents')),
                Switcher::make('status.is_waiting_payment')
                    ->sendTrueOrFalse()
                    ->title(tkey('crm.dictionaries.fields.is_waiting_payment')),
                Switcher::make('status.is_in_progress')
                    ->sendTrueOrFalse()
                    ->title(tkey('crm.dictionaries.fields.is_in_progress')),
            ])->title($this->status?->exists ? tkey('crm.dictionaries.edit_title') : tkey('crm.dictionaries.create_title')),

            Layout::table('statuses', [
                TD::make('code', tkey('crm.dictionaries.fields.code'))
                    ->render(fn (EnrollmentStatus $status): string => (string) Link::make($status->code)
                        ->route('platform.students.enrollment-statuses', ['status_id' => $status->id])),
                TD::make('name', tkey('crm.dictionaries.fields.name'))
                    ->render(fn (EnrollmentStatus $status): string => $status->displayName()),
                TD::make('color', tkey('crm.dictionaries.fields.color'))
                    ->render(fn (EnrollmentStatus $status): string => $status->color ?: '-'),
                TD::make('is_default', tkey('crm.dictionaries.fields.is_default'))
                    ->render(fn (EnrollmentStatus $status): string => $status->is_default ? tkey('common.status.yes') : tkey('common.status.no')),
                TD::make('is_active', tkey('crm.dictionaries.fields.is_active'))
                    ->render(fn (EnrollmentStatus $status): string => $status->is_active ? tkey('common.status.active') : tkey('common.status.inactive')),
                TD::make('sort_order', tkey('crm.dictionaries.fields.sort_order'))
                    ->render(fn (EnrollmentStatus $status): string => (string) $status->sort_order),
            ]),
        ];
    }

    public function save(EnrollmentStatusRequest $request, CreateOrUpdateEnrollmentStatusAction $saveStatus): RedirectResponse
    {
        $saveStatus->handle($request->statusId(), $request->statusData());

        Toast::info(tkey('crm.dictionaries.messages.saved'));

        return redirect()->route('platform.students.enrollment-statuses');
    }
}
