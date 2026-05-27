<?php

declare(strict_types=1);

namespace App\Orchid\Screens\System;

use App\Models\Language;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class LanguageListScreen extends Screen
{
    public function query(): iterable
    {
        return [
            'languages' => Language::query()
                ->select([
                    'id',
                    'code',
                    'name',
                    'native_name',
                    'is_default',
                    'is_active',
                    'sort_order',
                    'created_at',
                    'updated_at',
                ])
                ->ordered()
                ->simplePaginate(20),
        ];
    }

    public function name(): ?string
    {
        return tkey('languages.title');
    }

    public function description(): ?string
    {
        return tkey('languages.description');
    }

    public function permission(): iterable
    {
        return ['system.languages.view'];
    }

    public function commandBar(): iterable
    {
        return [
            Link::make(tkey('common.actions.create'))
                ->icon('bs.plus-circle')
                ->route('platform.system.languages.create')
                ->canSee(request()->user()?->hasAccess('system.languages.create') ?? false),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::table('languages', [
                TD::make('code', tkey('languages.fields.code'))
                    ->render(fn (Language $language): string => $language->code),
                TD::make('name', tkey('languages.fields.name'))
                    ->render(fn (Language $language): string => $language->name),
                TD::make('native_name', tkey('languages.fields.native_name'))
                    ->render(fn (Language $language): string => $language->native_name),
                TD::make('is_default', tkey('languages.fields.is_default'))
                    ->render(fn (Language $language): string => $language->is_default ? tkey('common.status.yes') : tkey('common.status.no'))
                    ->alignCenter(),
                TD::make('is_active', tkey('languages.fields.is_active'))
                    ->render(fn (Language $language): string => $language->is_active ? tkey('common.status.active') : tkey('common.status.inactive'))
                    ->alignCenter(),
                TD::make('sort_order', tkey('languages.fields.sort_order'))
                    ->render(fn (Language $language): string => (string) $language->sort_order)
                    ->alignCenter(),
                TD::make('actions', '')
                    ->cantHide()
                    ->alignRight()
                    ->render(fn (Language $language): DropDown => DropDown::make()
                        ->icon('bs.three-dots-vertical')
                        ->list([
                            Link::make(tkey('common.actions.edit'))
                                ->icon('bs.pencil')
                                ->route('platform.system.languages.edit', $language)
                                ->canSee(request()->user()?->hasAccess('system.languages.update') ?? false),
                            Button::make(tkey('common.actions.set_default'))
                                ->icon('bs.check-circle')
                                ->method('setDefault')
                                ->parameters(['language' => $language->id])
                                ->canSee(! $language->is_default && (request()->user()?->hasAccess('system.languages.update') ?? false)),
                            Button::make($language->is_active ? tkey('common.actions.deactivate') : tkey('common.actions.activate'))
                                ->icon($language->is_active ? 'bs.pause-circle' : 'bs.play-circle')
                                ->method('toggleActive')
                                ->parameters(['language' => $language->id])
                                ->canSee(! $language->is_default && (request()->user()?->hasAccess('system.languages.update') ?? false)),
                            Button::make(tkey('common.actions.delete'))
                                ->icon('bs.trash3')
                                ->method('delete')
                                ->parameters(['language' => $language->id])
                                ->confirm(tkey('languages.messages.deleted'))
                                ->canSee(! $language->is_default && (request()->user()?->hasAccess('system.languages.delete') ?? false)),
                        ])),
            ]),
        ];
    }

    public function setDefault(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->hasAccess('system.languages.update'), 403);

        $language = Language::query()->findOrFail($request->integer('language'));
        $language->forceFill(['is_default' => true, 'is_active' => true])->save();

        Toast::info(tkey('languages.messages.default_set'));

        return redirect()->route('platform.system.languages');
    }

    public function toggleActive(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->hasAccess('system.languages.update'), 403);

        $language = Language::query()->findOrFail($request->integer('language'));

        if (! $language->is_default) {
            $language->forceFill(['is_active' => ! $language->is_active])->save();
        }

        Toast::info(tkey('languages.messages.saved'));

        return redirect()->route('platform.system.languages');
    }

    public function delete(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->hasAccess('system.languages.delete'), 403);

        $language = Language::query()->findOrFail($request->integer('language'));

        if (! $language->is_default) {
            $language->delete();
        }

        Toast::info(tkey('languages.messages.deleted'));

        return redirect()->route('platform.system.languages');
    }
}
