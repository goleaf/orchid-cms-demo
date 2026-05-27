<?php

declare(strict_types=1);

namespace App\Orchid\Screens\School;

use App\Support\Crm\LeadDictionaryRegistry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class LeadDictionaryListScreen extends Screen
{
    /**
     * @var array<string, mixed>
     */
    private array $definition = [];

    private string $dictionary = '';

    public function query(string $dictionary): iterable
    {
        $this->dictionary = $dictionary;
        $this->definition = LeadDictionaryRegistry::definition($dictionary);

        /** @var class-string<Model> $modelClass */
        $modelClass = $this->definition['model'];

        return [
            'items' => $modelClass::query()
                ->ordered()
                ->simplePaginate(20)
                ->withQueryString(),
        ];
    }

    public function name(): ?string
    {
        return tkey((string) $this->definition['title_key']);
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
            Link::make(tkey('common.actions.create'))
                ->icon('bs.plus-circle')
                ->route('platform.crm.dictionaries.create', $this->dictionary),
        ];
    }

    public function layout(): iterable
    {
        $keyColumn = (string) $this->definition['key_column'];

        return [
            Layout::table('items', [
                TD::make($keyColumn, tkey('crm.dictionaries.fields.key'))
                    ->render(fn (Model $item): string => (string) Link::make((string) $item->getAttribute($keyColumn))
                        ->route('platform.crm.dictionaries.edit', [$this->dictionary, $item->getKey()])),
                TD::make('name', tkey('crm.dictionaries.fields.name'))
                    ->render(fn (Model $item): string => $item->displayName()),
                TD::make('color', tkey('crm.dictionaries.fields.color'))
                    ->render(fn (Model $item): string => (string) ($item->getAttribute('color') ?: '-')),
                TD::make('is_active', tkey('crm.dictionaries.fields.is_active'))
                    ->render(fn (Model $item): string => $item->getAttribute('is_active') ? tkey('common.status.active') : tkey('common.status.inactive'))
                    ->alignCenter(),
                TD::make('is_final', tkey('crm.dictionaries.fields.is_final'))
                    ->render(fn (Model $item): string => $this->dictionary === 'statuses'
                        ? ($item->getAttribute('is_final') ? tkey('common.status.yes') : tkey('common.status.no'))
                        : '-')
                    ->alignCenter(),
                TD::make('sort_order', tkey('crm.dictionaries.fields.sort_order'))
                    ->render(fn (Model $item): string => (string) $item->getAttribute('sort_order'))
                    ->alignCenter(),
                TD::make('actions', tkey('crm.leads.columns.actions'))
                    ->cantHide()
                    ->alignRight()
                    ->render(fn (Model $item): DropDown => DropDown::make()
                        ->icon('bs.three-dots-vertical')
                        ->list([
                            Link::make(tkey('common.actions.edit'))
                                ->icon('bs.pencil')
                                ->route('platform.crm.dictionaries.edit', [$this->dictionary, $item->getKey()]),
                            Button::make(tkey('common.actions.delete'))
                                ->icon('bs.trash3')
                                ->method('delete')
                                ->parameters([
                                    'dictionary' => $this->dictionary,
                                    'record' => $item->getKey(),
                                ])
                                ->confirm(tkey('crm.dictionaries.messages.delete_confirm'))
                                ->canSee(! (bool) $item->getAttribute('is_system')),
                        ])),
            ]),
        ];
    }

    public function delete(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->hasAccess('crm.leads.manage_dictionaries'), 403);

        $dictionary = (string) $request->input('dictionary', $this->dictionary);
        $definition = LeadDictionaryRegistry::definition($dictionary);

        /** @var class-string<Model> $modelClass */
        $modelClass = $definition['model'];
        $item = $modelClass::query()->findOrFail($request->integer('record'));

        abort_if((bool) $item->getAttribute('is_system'), 403);

        $item->delete();

        Toast::info(tkey('crm.dictionaries.messages.deleted'));

        return redirect()->route('platform.crm.dictionaries', $dictionary);
    }
}
