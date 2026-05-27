<?php

declare(strict_types=1);

namespace App\Orchid\Screens\School;

use App\Models\MarketingMessageTemplate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class MessageTemplateListScreen extends Screen
{
    public string $channel = '';

    public string $active = '';

    public function query(Request $request): iterable
    {
        $channel = trim((string) $request->query('channel'));
        $active = trim((string) $request->query('active'));

        $this->channel = $channel;
        $this->active = $active;

        return [
            'channel' => $channel,
            'active' => $active,
            'templates' => MarketingMessageTemplate::query()
                ->forTemplateList()
                ->when($channel !== '', fn (Builder $query): Builder => $channel === 'any'
                    ? $query->whereNull('channel')
                    : $query->where('channel', $channel))
                ->when($active !== '', fn (Builder $query): Builder => $query->where('is_active', (bool) $active))
                ->orderBy('sort_order')
                ->orderBy('name')
                ->simplePaginate(20)
                ->withQueryString(),
        ];
    }

    public function name(): ?string
    {
        return tkey('crm.message_templates.title');
    }

    public function description(): ?string
    {
        return tkey('crm.message_templates.description');
    }

    public function permission(): iterable
    {
        return ['platform.marketing.templates'];
    }

    public function commandBar(): iterable
    {
        return [
            Link::make(tkey('common.actions.create'))
                ->icon('bs.plus-circle')
                ->route('platform.marketing.templates.create'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::rows([
                Select::make('channel')
                    ->title(tkey('crm.message_templates.filters.channel'))
                    ->empty(tkey('crm.message_templates.filters.all_channels'), '')
                    ->options([
                        'any' => tkey('crm.communication.channels.any'),
                        ...MarketingMessageTemplate::channelOptions(),
                    ])
                    ->value($this->channel),

                Select::make('active')
                    ->title(tkey('crm.message_templates.filters.status'))
                    ->empty(tkey('crm.message_templates.filters.all_statuses'), '')
                    ->options([
                        '1' => tkey('common.status.active'),
                        '0' => tkey('common.status.inactive'),
                    ])
                    ->value($this->active),

                Button::make(tkey('common.actions.search'))
                    ->icon('bs.search')
                    ->method('filter')
                    ->novalidate(),
            ]),

            Layout::table('templates', [
                TD::make('name', tkey('crm.message_templates.fields.name'))
                    ->render(fn (MarketingMessageTemplate $template): string => (string) Link::make($template->name)
                        ->route('platform.marketing.templates.edit', $template)),
                TD::make('channel', tkey('crm.message_templates.fields.channel'))
                    ->render(fn (MarketingMessageTemplate $template): string => $template->channelLabel()),
                TD::make('subject', tkey('crm.message_templates.fields.subject'))
                    ->render(fn (MarketingMessageTemplate $template): string => $template->subject ?? '-'),
                TD::make('body', tkey('crm.message_templates.fields.body'))
                    ->render(fn (MarketingMessageTemplate $template): string => e(str($template->body)->limit(90)->toString())),
                TD::make('is_active', tkey('crm.message_templates.fields.is_active'))
                    ->render(fn (MarketingMessageTemplate $template): string => $template->is_active ? tkey('common.status.active') : tkey('common.status.inactive'))
                    ->alignCenter(),
                TD::make('sort_order', tkey('crm.message_templates.fields.sort_order'))
                    ->render(fn (MarketingMessageTemplate $template): string => (string) $template->sort_order)
                    ->alignCenter(),
                TD::make('actions', '')
                    ->cantHide()
                    ->alignRight()
                    ->render(fn (MarketingMessageTemplate $template): DropDown => DropDown::make()
                        ->icon('bs.three-dots-vertical')
                        ->list([
                            Link::make(tkey('common.actions.edit'))
                                ->icon('bs.pencil')
                                ->route('platform.marketing.templates.edit', $template),
                            Button::make(tkey('common.actions.delete'))
                                ->icon('bs.trash3')
                                ->method('delete')
                                ->parameters(['messageTemplate' => $template->id])
                                ->confirm(tkey('crm.message_templates.messages.delete_confirm')),
                        ])),
            ]),
        ];
    }

    public function filter(Request $request): RedirectResponse
    {
        return redirect()->route('platform.marketing.templates', array_filter([
            'channel' => $request->input('channel'),
            'active' => $request->input('active'),
        ], fn (mixed $value): bool => filled($value)));
    }

    public function delete(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->hasAccess('platform.marketing.templates'), 403);

        MarketingMessageTemplate::query()->findOrFail($request->integer('messageTemplate'))->delete();

        Toast::info(tkey('crm.message_templates.messages.deleted'));

        return redirect()->route('platform.marketing.templates');
    }
}
