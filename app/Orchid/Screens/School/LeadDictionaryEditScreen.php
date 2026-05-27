<?php

declare(strict_types=1);

namespace App\Orchid\Screens\School;

use App\Actions\DeleteLeadDictionaryAction;
use App\Actions\SaveLeadDictionaryAction;
use App\Http\Requests\Marketing\LeadDictionaryDeleteRequest;
use App\Http\Requests\Marketing\LeadDictionaryRequest;
use App\Orchid\Support\TranslatableFields;
use App\Support\Crm\LeadDictionaryRegistry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
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

        $this->item = $item;

        return [
            'dictionary' => $dictionary,
            'item' => $item,
            'name_translations' => $item->getTranslations('name'),
            'description_translations' => $item->getTranslations('description'),
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

                Input::make('item.color')
                    ->title(tkey('crm.dictionaries.fields.color'))
                    ->maxlength(32),

                Switcher::make('item.is_active')
                    ->title(tkey('crm.dictionaries.fields.is_active'))
                    ->sendTrueOrFalse(),

                Switcher::make('item.is_public')
                    ->title(tkey('crm.dictionaries.fields.is_public'))
                    ->sendTrueOrFalse()
                    ->canSee($this->dictionary === 'statuses'),

                Switcher::make('item.is_default')
                    ->title(tkey('crm.dictionaries.fields.is_default'))
                    ->sendTrueOrFalse()
                    ->canSee($this->dictionary === 'statuses'),

                Switcher::make('item.is_final')
                    ->title(tkey('crm.dictionaries.fields.is_final'))
                    ->sendTrueOrFalse()
                    ->canSee($this->dictionary === 'statuses'),

                Switcher::make('item.is_success')
                    ->title(tkey('crm.dictionaries.fields.is_success'))
                    ->sendTrueOrFalse()
                    ->canSee($this->dictionary === 'statuses'),

                Switcher::make('item.is_lost')
                    ->title(tkey('crm.dictionaries.fields.is_lost'))
                    ->sendTrueOrFalse()
                    ->canSee($this->dictionary === 'statuses'),

                Switcher::make('item.is_duplicate')
                    ->title(tkey('crm.dictionaries.fields.is_duplicate'))
                    ->sendTrueOrFalse()
                    ->canSee($this->dictionary === 'statuses'),

                Switcher::make('item.is_spam')
                    ->title(tkey('crm.dictionaries.fields.is_spam'))
                    ->sendTrueOrFalse()
                    ->canSee($this->dictionary === 'statuses'),

                Input::make('item.sort_order')
                    ->type('number')
                    ->title(tkey('crm.dictionaries.fields.sort_order'))
                    ->min(0)
                    ->required(),
            ]),

            TranslatableFields::input('name', 'crm.dictionaries.fields.name_translations', [
                'maxlength' => 255,
            ]),

            TranslatableFields::textarea('description', 'crm.dictionaries.fields.description_translations', [
                'rows' => 3,
                'maxlength' => 1000,
            ]),
        ];
    }

    public function save(
        LeadDictionaryRequest $request,
        SaveLeadDictionaryAction $saveDictionary,
        string $dictionary,
        ?string $record = null
    ): RedirectResponse {
        $saveDictionary->handle($dictionary, $record, $request->dictionaryData());

        Toast::info(tkey('crm.dictionaries.messages.saved'));

        return redirect()->route('platform.crm.dictionaries', $dictionary);
    }

    public function delete(
        LeadDictionaryDeleteRequest $request,
        DeleteLeadDictionaryAction $deleteDictionary,
        string $dictionary,
        string $record
    ): RedirectResponse
    {
        $deleteDictionary->handle($dictionary, $request->recordId());

        Toast::info(tkey('crm.dictionaries.messages.deleted'));

        return redirect()->route('platform.crm.dictionaries', $dictionary);
    }
}
