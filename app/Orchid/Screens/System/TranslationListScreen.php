<?php

declare(strict_types=1);

namespace App\Orchid\Screens\System;

use App\Http\Requests\System\TranslationImportRequest;
use App\Models\Language;
use App\Models\TranslationString;
use App\Models\TranslationValue;
use App\Services\TranslationManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Label;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TranslationListScreen extends Screen
{
    public int $missingCount = 0;

    public ?Collection $languages = null;

    public array $groups = [];

    public string $group = '';

    public string $search = '';

    public function query(Request $request): iterable
    {
        $languages = Language::active()->ordered()->get();
        $languageCodes = $languages->pluck('code')->all();
        $search = trim((string) $request->query('search'));
        $group = trim((string) $request->query('group'));
        $groups = TranslationString::query()
            ->select(['group'])
            ->whereNotNull('group')
            ->orderBy('group')
            ->pluck('group', 'group')
            ->all();

        $this->languages = $languages;
        $this->missingCount = $this->missingCount($languageCodes);
        $this->groups = $groups;
        $this->group = $group;
        $this->search = $search;

        return [
            'languages' => $languages,
            'missing_count' => $this->missingCount,
            'group' => $group,
            'search' => $search,
            'groups' => $groups,
            'translationStrings' => TranslationString::query()
                ->forManagerList()
                ->when($group !== '', fn (Builder $query): Builder => $query->where('group', $group))
                ->when($search !== '', fn (Builder $query): Builder => $query->where(function (Builder $query) use ($search): void {
                    $query
                        ->where('key', 'like', '%'.$search.'%')
                        ->orWhereHas('values', fn (Builder $query): Builder => $query->where('value', 'like', '%'.$search.'%'));
                }))
                ->orderBy('group')
                ->orderBy('key')
                ->simplePaginate(20)
                ->withQueryString(),
        ];
    }

    public function name(): ?string
    {
        return tkey('translations.title');
    }

    public function description(): ?string
    {
        return tkey('translations.description');
    }

    public function permission(): iterable
    {
        return ['system.translations.view'];
    }

    public function commandBar(): iterable
    {
        return [
            Link::make(tkey('common.actions.create'))
                ->icon('bs.plus-circle')
                ->route('platform.system.translations.create')
                ->canSee(request()->user()?->hasAccess('system.translations.update') ?? false),

            Button::make(tkey('common.actions.export_csv'))
                ->icon('bs.filetype-csv')
                ->method('exportCsv')
                ->download()
                ->canSee(request()->user()?->hasAccess('system.translations.export') ?? false),

            Button::make(tkey('common.actions.export_json'))
                ->icon('bs.filetype-json')
                ->method('exportJson')
                ->download()
                ->canSee(request()->user()?->hasAccess('system.translations.export') ?? false),

            Button::make(tkey('common.actions.bulk_create_missing'))
                ->icon('bs.plus-square-dotted')
                ->method('createMissingValues')
                ->canSee(request()->user()?->hasAccess('system.translations.update') ?? false),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::rows([
                Label::make('missing_count')
                    ->title(tkey('translations.fields.missing_count'))
                    ->value((string) $this->missingCount),

                Select::make('group')
                    ->title(tkey('translations.filters.group'))
                    ->empty('', '')
                    ->options($this->groups)
                    ->value($this->group),

                Input::make('search')
                    ->title(tkey('translations.filters.search'))
                    ->value($this->search),

                Button::make(tkey('common.actions.search'))
                    ->icon('bs.search')
                    ->method('filter')
                    ->novalidate(),
            ]),

            Layout::rows([
                Input::make('import_file')
                    ->type('file')
                    ->title(tkey('translations.fields.import_file')),

                Button::make(tkey('common.actions.import'))
                    ->icon('bs.upload')
                    ->method('import')
                    ->formenctype('multipart/form-data')
                    ->canSee(request()->user()?->hasAccess('system.translations.import') ?? false),
            ]),

            Layout::table('translationStrings', [
                TD::make('key', tkey('translations.fields.key'))
                    ->render(fn (TranslationString $translationString): string => $translationString->key),
                TD::make('group', tkey('translations.fields.group'))
                    ->render(fn (TranslationString $translationString): string => (string) $translationString->group),
                TD::make('missing_values_count', tkey('translations.fields.missing_count'))
                    ->render(fn (TranslationString $translationString): string => (string) $this->rowMissingCount($translationString, $this->languages ?? collect()))
                    ->alignCenter(),
                ...$this->languageColumns($this->languages ?? collect()),
                TD::make('actions', '')
                    ->cantHide()
                    ->alignRight()
                    ->render(fn (TranslationString $translationString): DropDown => DropDown::make()
                        ->icon('bs.three-dots-vertical')
                        ->list([
                            Link::make(tkey('common.actions.edit'))
                                ->icon('bs.pencil')
                                ->route('platform.system.translations.edit', $translationString)
                                ->canSee(request()->user()?->hasAccess('system.translations.update') ?? false),
                            Button::make(tkey('common.actions.delete'))
                                ->icon('bs.trash3')
                                ->method('delete')
                                ->parameters(['translationString' => $translationString->id])
                                ->canSee(request()->user()?->hasAccess('system.translations.update') ?? false),
                        ])),
            ]),
        ];
    }

    public function filter(Request $request): RedirectResponse
    {
        return redirect()->route('platform.system.translations', array_filter([
            'group' => $request->input('group'),
            'search' => $request->input('search'),
        ], fn (mixed $value): bool => filled($value)));
    }

    public function createMissingValues(TranslationManager $translations): RedirectResponse
    {
        abort_unless(request()->user()?->hasAccess('system.translations.update'), 403);

        $translations->createMissingValues();

        Toast::info(tkey('translations.messages.missing_created'));

        return redirect()->route('platform.system.translations');
    }

    public function import(TranslationImportRequest $request, TranslationManager $translations): RedirectResponse
    {
        $translations->importUploadedFile($request->file('import_file'));

        Toast::info(tkey('translations.messages.imported'));

        return redirect()->route('platform.system.translations');
    }

    public function exportCsv(TranslationManager $translations): StreamedResponse
    {
        abort_unless(request()->user()?->hasAccess('system.translations.export'), 403);

        return response()->streamDownload(
            fn (): int => print ($translations->exportCsv()),
            'translations.csv',
            ['Content-Type' => 'text/csv; charset=UTF-8'],
        );
    }

    public function exportJson(TranslationManager $translations): StreamedResponse
    {
        abort_unless(request()->user()?->hasAccess('system.translations.export'), 403);

        return response()->streamDownload(
            fn (): int => print (json_encode($translations->exportArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)),
            'translations.json',
            ['Content-Type' => 'application/json; charset=UTF-8'],
        );
    }

    public function delete(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->hasAccess('system.translations.update'), 403);

        TranslationString::query()->findOrFail($request->integer('translationString'))->delete();

        Toast::info(tkey('translations.messages.deleted'));

        return redirect()->route('platform.system.translations');
    }

    /**
     * @return array<int, TD>
     */
    private function languageColumns(Collection $languages): array
    {
        return $languages
            ->map(fn (Language $language): TD => TD::make('value_'.$language->code, $language->native_name)
                ->render(fn (TranslationString $translationString): string => $this->renderValue($translationString, $language->code)))
            ->all();
    }

    private function renderValue(TranslationString $translationString, string $languageCode): string
    {
        $value = $translationString->values->firstWhere('language_code', $languageCode)?->value;

        return filled($value)
            ? e(Str::limit((string) $value, 60))
            : '<span class="text-danger">'.e(tkey('translations.fields.missing_count')).'</span>';
    }

    private function rowMissingCount(TranslationString $translationString, Collection $languages): int
    {
        return $languages
            ->filter(fn (Language $language): bool => blank($translationString->values->firstWhere('language_code', $language->code)?->value))
            ->count();
    }

    /**
     * @param  array<int, string>  $languageCodes
     */
    private function missingCount(array $languageCodes): int
    {
        $totalSlots = TranslationString::query()->count() * count($languageCodes);
        $filledSlots = TranslationValue::query()
            ->whereIn('language_code', $languageCodes)
            ->whereNotNull('value')
            ->where('value', '<>', '')
            ->count();

        return max(0, $totalSlots - $filledSlots);
    }
}
