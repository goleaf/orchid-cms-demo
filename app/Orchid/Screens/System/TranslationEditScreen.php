<?php

declare(strict_types=1);

namespace App\Orchid\Screens\System;

use App\Http\Requests\System\TranslationStringRequest;
use App\Models\Language;
use App\Models\TranslationString;
use App\Services\TranslationManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Switcher;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class TranslationEditScreen extends Screen
{
    public $translationString;

    public ?Collection $languages = null;

    public function query(TranslationString $translationString): iterable
    {
        $translationString->load(['values']);
        $languages = Language::active()->ordered()->get();
        $this->languages = $languages;

        return [
            'translationString' => $translationString,
            'translation' => $translationString,
            'values' => $languages
                ->mapWithKeys(fn (Language $language): array => [
                    $language->code => [
                        'value' => $translationString->values->firstWhere('language_code', $language->code)?->value,
                        'is_approved' => $translationString->values->firstWhere('language_code', $language->code)?->is_approved ?? true,
                    ],
                ])
                ->all(),
            'languages' => $languages,
        ];
    }

    public function name(): ?string
    {
        return $this->translationString->exists
            ? tkey('translations.edit_title')
            : tkey('translations.create_title');
    }

    public function description(): ?string
    {
        return tkey('translations.description');
    }

    public function permission(): iterable
    {
        return ['system.translations.update'];
    }

    public function commandBar(): iterable
    {
        return [
            Link::make(tkey('common.actions.back'))
                ->icon('bs.arrow-left')
                ->route('platform.system.translations'),

            Button::make(tkey('common.actions.save'))
                ->icon('bs.check-circle')
                ->method('save'),

            Button::make(tkey('common.actions.delete'))
                ->icon('bs.trash3')
                ->method('delete')
                ->canSee($this->translationString->exists),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::rows([
                Input::make('translation.group')
                    ->title(tkey('translations.fields.group'))
                    ->maxlength(255),

                Input::make('translation.key')
                    ->title(tkey('translations.fields.key'))
                    ->maxlength(255)
                    ->required(),

                TextArea::make('translation.description')
                    ->title(tkey('translations.fields.description'))
                    ->rows(3),

                Switcher::make('translation.is_system')
                    ->title(tkey('translations.fields.is_system'))
                    ->sendTrueOrFalse(),
            ]),

            ...$this->translationValueLayouts(),
        ];
    }

    public function save(TranslationStringRequest $request, TranslationString $translationString, TranslationManager $translations): RedirectResponse
    {
        $translations->saveTranslationString(
            $translationString,
            $request->translationData(),
            $request->valueData(),
        );

        Toast::info(tkey('translations.messages.saved'));

        return redirect()->route('platform.system.translations');
    }

    public function delete(TranslationString $translationString): RedirectResponse
    {
        $translationString->delete();

        Toast::info(tkey('translations.messages.deleted'));

        return redirect()->route('platform.system.translations');
    }

    /**
     * @return array<int, \Orchid\Screen\Layout>
     */
    private function translationValueLayouts(): array
    {
        return ($this->languages ?? collect())
            ->map(fn (Language $language) => Layout::rows([
                TextArea::make('values.'.$language->code.'.value')
                    ->title(tkey('translations.fields.value').' - '.$language->native_name)
                    ->rows(3),

                Switcher::make('values.'.$language->code.'.is_approved')
                    ->title(tkey('translations.fields.is_approved'))
                    ->sendTrueOrFalse(),
            ])->title($language->native_name))
            ->all();
    }
}
