<?php

declare(strict_types=1);

namespace App\Orchid\Screens\School;

use App\Actions\CreateOrUpdateNotificationChannelAction;
use App\Http\Requests\Communication\NotificationChannelRequest;
use App\Models\NotificationChannel;
use App\Orchid\Support\TranslatableFields;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Switcher;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class NotificationChannelListScreen extends Screen
{
    public ?NotificationChannel $channel = null;

    public function query(Request $request): iterable
    {
        $this->channel = $request->filled('channel_id')
            ? NotificationChannel::query()->findOrFail($request->integer('channel_id'))
            : new NotificationChannel([
                'driver' => 'placeholder',
                'is_active' => true,
                'supports_templates' => true,
                'supports_scheduling' => true,
                'sort_order' => 0,
            ]);

        return [
            'channels' => NotificationChannel::query()
                ->forList()
                ->ordered()
                ->simplePaginate(20)
                ->withQueryString(),
            'channel' => $this->channel,
            'channel.name_translations' => $this->channel->name_translations ?? [],
            'channel.description_translations' => $this->channel->description_translations ?? [],
        ];
    }

    public function name(): ?string
    {
        return tkey('communication.channels.title');
    }

    public function description(): ?string
    {
        return tkey('communication.channels.description');
    }

    public function permission(): iterable
    {
        return ['communications.channels.view'];
    }

    public function commandBar(): iterable
    {
        return [
            Link::make(tkey('common.actions.create'))
                ->icon('bs.plus-circle')
                ->route('platform.communications.channels')
                ->canSee(request()->user()?->hasAccess('communications.channels.manage') ?? false),

            Button::make(tkey('common.actions.save'))
                ->icon('bs.check2-circle')
                ->method('save')
                ->canSee(request()->user()?->hasAccess('communications.channels.manage') ?? false),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::rows([
                Input::make('channel.id')->type('hidden'),

                Input::make('channel.code')
                    ->title(tkey('communication.channels.fields.code'))
                    ->maxlength(120)
                    ->required(),

                Input::make('channel.driver')
                    ->title(tkey('communication.channels.fields.driver'))
                    ->maxlength(120)
                    ->required(),

                Input::make('channel.provider')
                    ->title(tkey('communication.channels.fields.provider'))
                    ->maxlength(120),

                Input::make('channel.sort_order')
                    ->type('number')
                    ->title(tkey('communication.common.fields.sort_order'))
                    ->min(0),

                Switcher::make('channel.is_active')
                    ->sendTrueOrFalse()
                    ->title(tkey('communication.common.fields.is_active')),

                Switcher::make('channel.supports_internal')
                    ->sendTrueOrFalse()
                    ->title(tkey('communication.channels.fields.supports_internal')),

                Switcher::make('channel.supports_external')
                    ->sendTrueOrFalse()
                    ->title(tkey('communication.channels.fields.supports_external')),

                Switcher::make('channel.supports_templates')
                    ->sendTrueOrFalse()
                    ->title(tkey('communication.channels.fields.supports_templates')),

                Switcher::make('channel.supports_scheduling')
                    ->sendTrueOrFalse()
                    ->title(tkey('communication.channels.fields.supports_scheduling')),

                Switcher::make('channel.supports_delivery_status')
                    ->sendTrueOrFalse()
                    ->title(tkey('communication.channels.fields.supports_delivery_status')),
            ])->title($this->channel?->exists ? tkey('crm.dictionaries.edit_title') : tkey('crm.dictionaries.create_title')),

            TranslatableFields::input('channel.name', 'communication.channels.fields.name', [
                'maxlength' => 255,
                'required' => true,
            ]),

            TranslatableFields::textarea('channel.description', 'communication.channels.fields.description', [
                'rows' => 3,
                'maxlength' => 1000,
            ]),

            Layout::table('channels', [
                TD::make('code', tkey('communication.channels.fields.code'))
                    ->render(fn (NotificationChannel $channel): string => (string) Link::make($channel->code)
                        ->route('platform.communications.channels', ['channel_id' => $channel->id])),
                TD::make('name', tkey('communication.channels.fields.name'))
                    ->render(fn (NotificationChannel $channel): string => $channel->displayName()),
                TD::make('driver', tkey('communication.channels.fields.driver'))
                    ->render(fn (NotificationChannel $channel): string => $channel->driver),
                TD::make('provider', tkey('communication.channels.fields.provider'))
                    ->render(fn (NotificationChannel $channel): string => $channel->provider ?: '-'),
                TD::make('is_active', tkey('communication.common.fields.is_active'))
                    ->render(fn (NotificationChannel $channel): string => $channel->is_active ? tkey('common.status.active') : tkey('common.status.inactive')),
                TD::make('sort_order', tkey('communication.common.fields.sort_order'))
                    ->render(fn (NotificationChannel $channel): string => (string) $channel->sort_order),
                TD::make('actions', tkey('crm.leads.columns.actions'))
                    ->alignRight()
                    ->render(fn (NotificationChannel $channel): DropDown => DropDown::make()
                        ->icon('bs.three-dots-vertical')
                        ->list([
                            Link::make(tkey('common.actions.edit'))
                                ->icon('bs.pencil')
                                ->route('platform.communications.channels', ['channel_id' => $channel->id]),
                        ])),
            ]),
        ];
    }

    public function save(
        NotificationChannelRequest $request,
        CreateOrUpdateNotificationChannelAction $saveChannel,
    ): RedirectResponse {
        $saveChannel->handle($request->channelId(), $request->channelData());

        Toast::info(tkey('communication.common.messages.saved'));

        return redirect()->route('platform.communications.channels');
    }
}
