<?php

declare(strict_types=1);

namespace App\Orchid\Screens\School;

use App\Models\CommunicationTemplate;
use App\Models\NotificationChannel;
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

class CommunicationTemplateListScreen extends Screen
{
    public string $channel = '';

    public string $type = '';

    public string $active = '';

    public function query(Request $request): iterable
    {
        $this->channel = trim((string) $request->query('channel'));
        $this->type = trim((string) $request->query('type'));
        $this->active = trim((string) $request->query('active'));

        return [
            'channel' => $this->channel,
            'type' => $this->type,
            'active' => $this->active,
            'templates' => CommunicationTemplate::query()
                ->forList()
                ->with('notificationChannel:id,code,name_translations')
                ->when($this->channel !== '', fn (Builder $query): Builder => $this->channel === 'any'
                    ? $query->whereNull('notification_channel_id')
                    : $query->where('notification_channel_id', $this->channel))
                ->when($this->type !== '', fn (Builder $query): Builder => $query->where('type', $this->type))
                ->when($this->active !== '', fn (Builder $query): Builder => $query->where('is_active', (bool) $this->active))
                ->ordered()
                ->simplePaginate(20)
                ->withQueryString(),
        ];
    }

    public function name(): ?string
    {
        return tkey('communication.templates.title');
    }

    public function description(): ?string
    {
        return tkey('communication.templates.description');
    }

    public function permission(): iterable
    {
        return ['communications.templates.view'];
    }

    public function commandBar(): iterable
    {
        return [
            Link::make(tkey('common.actions.create'))
                ->icon('bs.plus-circle')
                ->route('platform.communications.templates.create')
                ->canSee(request()->user()?->hasAccess('communications.templates.manage') ?? false),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::rows([
                Select::make('channel')
                    ->title(tkey('communication.templates.fields.channel'))
                    ->empty(tkey('communication.common.filters.all_channels'), '')
                    ->options([
                        'any' => tkey('communication.channels.any'),
                        ...NotificationChannel::options(activeOnly: false),
                    ])
                    ->value($this->channel),

                Select::make('type')
                    ->title(tkey('communication.templates.fields.type'))
                    ->empty(tkey('communication.common.filters.all_statuses'), '')
                    ->options(CommunicationTemplate::typeOptions())
                    ->value($this->type),

                Select::make('active')
                    ->title(tkey('crm.message_templates.filters.status'))
                    ->empty(tkey('communication.common.filters.all_statuses'), '')
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
                TD::make('name', tkey('communication.channels.fields.name'))
                    ->render(fn (CommunicationTemplate $template): string => (string) Link::make($template->displayName())
                        ->route('platform.communications.templates.edit', $template)),
                TD::make('type', tkey('communication.templates.fields.type'))
                    ->render(fn (CommunicationTemplate $template): string => $template->typeLabel()),
                TD::make('channel', tkey('communication.templates.fields.channel'))
                    ->render(fn (CommunicationTemplate $template): string => $template->channelLabel()),
                TD::make('subject', tkey('communication.templates.fields.subject'))
                    ->render(fn (CommunicationTemplate $template): string => $template->subject() ?? '-'),
                TD::make('is_active', tkey('communication.common.fields.is_active'))
                    ->render(fn (CommunicationTemplate $template): string => $template->is_active ? tkey('common.status.active') : tkey('common.status.inactive')),
                TD::make('actions', tkey('crm.leads.columns.actions'))
                    ->alignRight()
                    ->render(fn (CommunicationTemplate $template): DropDown => DropDown::make()
                        ->icon('bs.three-dots-vertical')
                        ->list([
                            Link::make(tkey('common.actions.edit'))
                                ->icon('bs.pencil')
                                ->route('platform.communications.templates.edit', $template),
                        ])),
            ]),
        ];
    }

    public function filter(Request $request): RedirectResponse
    {
        return redirect()->route('platform.communications.templates', array_filter([
            'channel' => $request->input('channel'),
            'type' => $request->input('type'),
            'active' => $request->input('active'),
        ], fn (mixed $value): bool => filled($value)));
    }
}
