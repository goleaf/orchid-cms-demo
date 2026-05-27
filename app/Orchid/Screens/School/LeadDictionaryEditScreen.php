<?php

declare(strict_types=1);

namespace App\Orchid\Screens\School;

use App\Models\Language;
use App\Support\Crm\LeadDictionaryRegistry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Switcher;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class LeadDictionaryEditScreen extends Screen
{
    /**
     * @var array<string, mixed>
     */
    private array $definition = [];

    private string $dictionary = '';

    private ?Model $item = null;

    private ?Collection $languages = null;

    public function query(string $dictionary, ?string $record = null): iterable
    {
        $this->dictionary = $dictionary;
        $this->definition = LeadDictionaryRegistry::definition($dictionary);

        /** @var class-string<Model> $modelClass */
        $modelClass = $this->definition['model'];

        $item = filled($record)
            ? $modelClass::query()->findOrFail($record)
            : new $modelClass;

        if (! $item->exists) {
            $item->forceFill([
                'is_active' => true,
                'sort_order' => 0,
            ]);
        }

        $languages = Language::active()->ordered()->get();
        $this->item = $item;
        $this->languages = $languages;

        return [
            'dictionary' => $dictionary,
            'item' => $item,
            'translations' => $languages
                ->mapWithKeys(fn (Language $language): array => [
                    $language->code => $item->getTranslation('name', $language->code, $language->code),
                ])
                ->all(),
        ];
    }

    public function name(): ?string
    {
        return $this->item?->exists
            ? tkey('crm.dictionaries.edit_title')
            : tkey('crm.dictionaries.create_title');
    }

    public function description(): ?string
    {
        return tkey((string) $this->definition['description_key']);
    }

    public function permission(): iterable
    {
        return ['crm.leads.manage_dictionaries'];
    }

    public function commandBar(): iterable
    {
        return [
            Link::make(tkey('common.actions.back'))
                ->icon('bs.arrow-left')
                ->route('platform.crm.dictionaries', $this->dictionary),

            Button::make(tkey('common.actions.save'))
                ->icon('bs.check-circle')
                ->method('save'),

            Button::make(tkey('common.actions.delete'))
                ->icon('bs.trash3')
                ->method('delete')
                ->canSee(($this->item?->exists ?? false) && ! (bool) $this->item?->getAttribute('is_system')),
        ];
    }

    public function layout(): iterable
    {
        $keyColumn = (string) $this->definition['key_column'];

        return [
            Layout::rows([
                Input::make('item.'.$keyColumn)
                    ->title($keyColumn === 'slug' ? tkey('crm.dictionaries.fields.slug') : tkey('crm.dictionaries.fields.code'))
                    ->maxlength(120)
                    ->required(),

                Input::make('item.name')
                    ->title(tkey('crm.dictionaries.fields.name'))
                    ->maxlength(255),

                Switcher::make('item.is_active')
                    ->title(tkey('crm.dictionaries.fields.is_active'))
                    ->sendTrueOrFalse(),

                Input::make('item.sort_order')
                    ->type('number')
                    ->title(tkey('crm.dictionaries.fields.sort_order'))
                    ->min(0)
                    ->required(),
            ]),

            ...$this->translationLayouts(),
        ];
    }

    public function save(Request $request, string $dictionary, ?string $record = null): RedirectResponse
    {
        abort_unless($request->user()?->hasAccess('crm.leads.manage_dictionaries'), 403);

        $definition = LeadDictionaryRegistry::definition($dictionary);

        /** @var class-string<Model> $modelClass */
        $modelClass = $definition['model'];
        $item = filled($record)
            ? $modelClass::query()->findOrFail($record)
            : new $modelClass;

        $keyColumn = (string) $definition['key_column'];
        $table = $item->getTable();

        $data = $request->validate([
            'item.'.$keyColumn => [
                'required',
                'string',
                'max:120',
                Rule::unique($table, $keyColumn)->ignore($item->getKey()),
            ],
            'item.name' => ['nullable', 'string', 'max:255'],
            'item.is_active' => ['nullable', 'boolean'],
            'item.sort_order' => ['required', 'integer', 'min:0'],
            'translations' => ['array'],
            'translations.*' => ['nullable', 'string', 'max:255'],
        ]);

        $translations = collect($data['translations'] ?? [])
            ->map(fn (?string $value): ?string => filled($value) ? $value : null)
            ->all();

        $item->fill([
            $keyColumn => $data['item'][$keyColumn],
            'name' => $data['item']['name'] ?? null,
            'name_translations' => $translations,
            'is_active' => (bool) ($data['item']['is_active'] ?? false),
            'sort_order' => (int) $data['item']['sort_order'],
        ]);
        $item->save();

        Toast::info(tkey('crm.dictionaries.messages.saved'));

        return redirect()->route('platform.crm.dictionaries', $dictionary);
    }

    public function delete(Request $request, string $dictionary, string $record): RedirectResponse
    {
        abort_unless($request->user()?->hasAccess('crm.leads.manage_dictionaries'), 403);

        $definition = LeadDictionaryRegistry::definition($dictionary);

        /** @var class-string<Model> $modelClass */
        $modelClass = $definition['model'];
        $item = $modelClass::query()->findOrFail($record);

        abort_if((bool) $item->getAttribute('is_system'), 403);

        $item->delete();

        Toast::info(tkey('crm.dictionaries.messages.deleted'));

        return redirect()->route('platform.crm.dictionaries', $dictionary);
    }

    /**
     * @return array<int, \Orchid\Screen\Layout>
     */
    private function translationLayouts(): array
    {
        return ($this->languages ?? collect())
            ->map(fn (Language $language) => Layout::rows([
                Input::make('translations.'.$language->code)
                    ->title(tkey('crm.dictionaries.fields.name_translation', ['language' => $language->native_name]))
                    ->maxlength(255),
            ])->title($language->native_name))
            ->all();
    }
}
