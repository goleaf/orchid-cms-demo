<?php

declare(strict_types=1);

namespace App\Orchid\Screens\School;

use App\Models\NotificationChannel;
use App\Models\NotificationDeliveryLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class NotificationDeliveryLogListScreen extends Screen
{
    public string $channel = '';

    public string $status = '';

    public function query(Request $request): iterable
    {
        $this->channel = trim((string) $request->query('channel'));
        $this->status = trim((string) $request->query('status'));

        return [
            'channel' => $this->channel,
            'status' => $this->status,
            'logs' => NotificationDeliveryLog::query()
                ->forList()
                ->with([
                    'notificationChannel:id,code,name_translations',
                    'communicationTemplate:id,name_translations',
                    'user:id,name,email',
                    'student:id,first_name,last_name,full_name,email,phone',
                    'lead:id,first_name,last_name,full_name,email,phone,lead_number',
                ])
                ->when($this->channel !== '', fn (Builder $query): Builder => $query->where('notification_channel_id', $this->channel))
                ->when($this->status !== '', fn (Builder $query): Builder => $query->where('status', $this->status))
                ->latest()
                ->simplePaginate(30)
                ->withQueryString(),
        ];
    }

    public function name(): ?string
    {
        return tkey('communication.delivery_logs.title');
    }

    public function description(): ?string
    {
        return tkey('communication.delivery_logs.description');
    }

    public function permission(): iterable
    {
        return ['communications.delivery_logs.view'];
    }

    public function commandBar(): iterable
    {
        return [];
    }

    public function layout(): iterable
    {
        return [
            Layout::rows([
                Select::make('channel')
                    ->title(tkey('communication.templates.fields.channel'))
                    ->empty(tkey('communication.common.filters.all_channels'), '')
                    ->options(NotificationChannel::options(activeOnly: false))
                    ->value($this->channel),

                Select::make('status')
                    ->title(tkey('communication.reminders.fields.status'))
                    ->empty(tkey('communication.common.filters.all_statuses'), '')
                    ->options($this->statusOptions())
                    ->value($this->status),

                Button::make(tkey('common.actions.search'))
                    ->icon('bs.search')
                    ->method('filter')
                    ->novalidate(),
            ]),

            Layout::table('logs', [
                TD::make('created_at', tkey('communication.common.fields.created_at'))
                    ->render(fn (NotificationDeliveryLog $log): string => $log->created_at?->format('Y-m-d H:i') ?? '-'),
                TD::make('channel', tkey('communication.templates.fields.channel'))
                    ->render(fn (NotificationDeliveryLog $log): string => $log->notificationChannel?->displayName() ?? '-'),
                TD::make('status', tkey('communication.reminders.fields.status'))
                    ->render(fn (NotificationDeliveryLog $log): string => $log->statusLabel()),
                TD::make('recipient', tkey('communication.delivery_logs.fields.recipient'))
                    ->render(fn (NotificationDeliveryLog $log): string => $log->recipient_name ?: ($log->user?->name ?: ($log->student?->display_name ?: ($log->lead?->fullName() ?: '-')))),
                TD::make('subject', tkey('communication.templates.fields.subject'))
                    ->render(fn (NotificationDeliveryLog $log): string => $log->subject ?? '-'),
                TD::make('provider', tkey('communication.delivery_logs.fields.provider'))
                    ->render(fn (NotificationDeliveryLog $log): string => $log->provider ?: '-'),
                TD::make('sent_at', tkey('communication.delivery_logs.fields.sent_at'))
                    ->render(fn (NotificationDeliveryLog $log): string => $log->sent_at?->format('Y-m-d H:i') ?? '-'),
            ]),
        ];
    }

    public function filter(Request $request): RedirectResponse
    {
        return redirect()->route('platform.communications.delivery-logs', array_filter([
            'channel' => $request->input('channel'),
            'status' => $request->input('status'),
        ], fn (mixed $value): bool => filled($value)));
    }

    /**
     * @return array<string, string>
     */
    private function statusOptions(): array
    {
        return collect(NotificationDeliveryLog::statusValues())
            ->mapWithKeys(fn (string $status): array => [$status => tkey('communication.delivery_logs.statuses.'.$status)])
            ->all();
    }
}
