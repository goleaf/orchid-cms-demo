<?php

declare(strict_types=1);

namespace App\Orchid\Screens\System;

use App\Http\Requests\System\LanguageRequest;
use App\Models\Language;
use Illuminate\Http\RedirectResponse;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Switcher;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class LanguageEditScreen extends Screen
{
    public $language;

    public function query(Language $language): iterable
    {
        return [
            'language' => $language,
        ];
    }

    public function name(): ?string
    {
        return $this->language->exists
            ? tkey('languages.edit_title')
            : tkey('languages.create_title');
    }

    public function description(): ?string
    {
        return tkey('languages.description');
    }

    public function permission(): iterable
    {
        return [
            request()->routeIs('platform.system.languages.create')
                ? 'system.languages.create'
                : 'system.languages.update',
        ];
    }

    public function commandBar(): iterable
    {
        return [
            Link::make(tkey('common.actions.back'))
                ->icon('bs.arrow-left')
                ->route('platform.system.languages'),

            Button::make(tkey('common.actions.save'))
                ->icon('bs.check-circle')
                ->method('save'),

            Button::make(tkey('common.actions.delete'))
                ->icon('bs.trash3')
                ->method('delete')
                ->canSee($this->language->exists && ! $this->language->is_default && (request()->user()?->hasAccess('system.languages.delete') ?? false)),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::rows([
                Input::make('language.code')
                    ->title(tkey('languages.fields.code'))
                    ->maxlength(12)
                    ->required(),

                Input::make('language.name')
                    ->title(tkey('languages.fields.name'))
                    ->maxlength(255)
                    ->required(),

                Input::make('language.native_name')
                    ->title(tkey('languages.fields.native_name'))
                    ->maxlength(255)
                    ->required(),

                Switcher::make('language.is_default')
                    ->title(tkey('languages.fields.is_default'))
                    ->sendTrueOrFalse(),

                Switcher::make('language.is_active')
                    ->title(tkey('languages.fields.is_active'))
                    ->sendTrueOrFalse(),

                Input::make('language.sort_order')
                    ->type('number')
                    ->title(tkey('languages.fields.sort_order'))
                    ->min(0)
                    ->required(),
            ]),
        ];
    }

    public function save(LanguageRequest $request, Language $language): RedirectResponse
    {
        $language->fill($request->languageData());
        $language->save();

        if (! Language::query()->where('is_default', true)->exists()) {
            $language->forceFill(['is_default' => true, 'is_active' => true])->save();
        }

        Toast::info(tkey('languages.messages.saved'));

        return redirect()->route('platform.system.languages');
    }

    public function delete(Language $language): RedirectResponse
    {
        abort_unless(request()->user()?->hasAccess('system.languages.delete'), 403);

        if (! $language->is_default) {
            $language->delete();
        }

        Toast::info(tkey('languages.messages.deleted'));

        return redirect()->route('platform.system.languages');
    }
}
