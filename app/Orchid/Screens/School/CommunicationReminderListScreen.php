<?php

declare(strict_types=1);

namespace App\Orchid\Screens\School;

use App\Actions\CompleteCommunicationReminderAction;
use App\Actions\CreateOrUpdateCommunicationReminderAction;
use App\Http\Requests\Communication\CommunicationReminderRequest;
use App\Models\CommunicationReminder;
use App\Models\CommunicationTemplate;
use App\Models\Lead;
use App\Models\NotificationChannel;
use App\Models\Student;
use App\Models\User;
use App\Orchid\Support\TranslatableFields;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class CommunicationReminderListScreen extends Screen
{
    public ?CommunicationReminder $reminder = null;

    public string $status = '';

    public function query(Request $request): iterable
    {
        $this->status = trim((string) $request->query('status'));
        $this->reminder = $request->filled('reminder_id')
            ? CommunicationReminder::query()->findOrFail($request->integer('reminder_id'))
            : new CommunicationReminder([
                'status' => CommunicationReminder::STATUS_SCHEDULED,
                'priority' => CommunicationReminder::PRIORITY_NORMAL,
                'due_at' => now()->addDay(),
            ]);

        return [
            'status' => $this->status,
            'reminder' => $this->reminder,
            'reminder.title_translations' => $this->reminder->title_translations ?? [],
            'reminder.body_translations' => $this->reminder->body_translations ?? [],
            'reminders' => CommunicationReminder::query()
                ->forList()
                ->with([
                    'assignedTo:id,name,email',
                    'notificationChannel:id,code,name_translations',
                    'communicationTemplate:id,name_translations',
                    'student:id,first_name,last_name,full_name,email,phone',
                    'lead:id,first_name,last_name,full_name,email,phone,lead_number',
                ])
                ->when($this->status !== '', fn (Builder $query): Builder => $query->where('status', $this->status))
                ->orderBy('due_at')
                ->simplePaginate(20)
                ->withQueryString(),
        ];
    }

    public function name(): ?string
    {
        return tkey('communication.reminders.title');
    }

    public function description(): ?string
    {
        return tkey('communication.reminders.description');
    }

    public function permission(): iterable
    {
        return ['communications.reminders.view'];
    }

    public function commandBar(): iterable
    {
        return [
            Link::make(tkey('common.actions.create'))
                ->icon('bs.plus-circle')
                ->route('platform.communications.reminders')
                ->canSee(request()->user()?->hasAccess('communications.reminders.manage') ?? false),

            Button::make(tkey('common.actions.save'))
                ->icon('bs.check2-circle')
                ->method('save')
                ->canSee(request()->user()?->hasAccess('communications.reminders.manage') ?? false),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::rows([
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

            Layout::rows([
                Input::make('reminder.id')->type('hidden'),

                Select::make('reminder.status')
                    ->title(tkey('communication.reminders.fields.status'))
                    ->options($this->statusOptions())
                    ->required(),

                Select::make('reminder.priority')
                    ->title(tkey('communication.reminders.fields.priority'))
                    ->options($this->priorityOptions())
                    ->required(),

                Select::make('reminder.notification_channel_id')
                    ->title(tkey('communication.templates.fields.channel'))
                    ->empty(tkey('communication.channels.any'), '')
                    ->options(NotificationChannel::options()),

                Select::make('reminder.communication_template_id')
                    ->title(tkey('communication.reminders.fields.template'))
                    ->empty('-', '')
                    ->options($this->templateOptions()),

                Select::make('reminder.assigned_to_user_id')
                    ->title(tkey('communication.reminders.fields.assignee'))
                    ->empty('-', '')
                    ->options($this->userOptions()),

                Select::make('reminder.marketing_lead_id')
                    ->title(tkey('communication.reminders.fields.lead'))
                    ->empty('-', '')
                    ->options($this->leadOptions()),

                Select::make('reminder.student_profile_id')
                    ->title(tkey('communication.reminders.fields.student'))
                    ->empty('-', '')
                    ->options($this->studentOptions()),

                Input::make('reminder.due_at')
                    ->type('datetime-local')
                    ->title(tkey('communication.reminders.fields.due_at'))
                    ->required(),

                TextArea::make('reminder.note')
                    ->title(tkey('communication.reminders.fields.note'))
                    ->rows(3)
                    ->maxlength(2000),
            ])->title($this->reminder?->exists ? tkey('crm.dictionaries.edit_title') : tkey('crm.dictionaries.create_title')),

            TranslatableFields::input('reminder.title', 'communication.reminders.fallback_title', [
                'maxlength' => 255,
            ]),

            TranslatableFields::textarea('reminder.body', 'communication.templates.fields.body', [
                'rows' => 4,
                'maxlength' => 2000,
            ]),

            Layout::table('reminders', [
                TD::make('title', tkey('communication.reminders.fallback_title'))
                    ->render(fn (CommunicationReminder $reminder): string => (string) Link::make($reminder->displayTitle())
                        ->route('platform.communications.reminders', ['reminder_id' => $reminder->id])),
                TD::make('status', tkey('communication.reminders.fields.status'))
                    ->render(fn (CommunicationReminder $reminder): string => $reminder->statusLabel()),
                TD::make('priority', tkey('communication.reminders.fields.priority'))
                    ->render(fn (CommunicationReminder $reminder): string => $reminder->priorityLabel()),
                TD::make('assignee', tkey('communication.reminders.fields.assignee'))
                    ->render(fn (CommunicationReminder $reminder): string => $reminder->assignedTo?->name ?? '-'),
                TD::make('student', tkey('communication.reminders.fields.student'))
                    ->render(fn (CommunicationReminder $reminder): string => $reminder->student?->display_name ?? '-'),
                TD::make('lead', tkey('communication.reminders.fields.lead'))
                    ->render(fn (CommunicationReminder $reminder): string => $reminder->lead?->fullName() ?? '-'),
                TD::make('due_at', tkey('communication.reminders.fields.due_at'))
                    ->render(fn (CommunicationReminder $reminder): string => $reminder->due_at?->format('Y-m-d H:i') ?? '-'),
                TD::make('actions', tkey('crm.leads.columns.actions'))
                    ->alignRight()
                    ->render(fn (CommunicationReminder $reminder): DropDown => DropDown::make()
                        ->icon('bs.three-dots-vertical')
                        ->list([
                            Link::make(tkey('common.actions.edit'))
                                ->icon('bs.pencil')
                                ->route('platform.communications.reminders', ['reminder_id' => $reminder->id]),
                            Button::make(tkey('communication.reminders.statuses.completed'))
                                ->icon('bs.check2-circle')
                                ->method('complete')
                                ->parameters(['reminder' => $reminder->id])
                                ->canSee($reminder->status === CommunicationReminder::STATUS_SCHEDULED),
                        ])),
            ]),
        ];
    }

    public function filter(Request $request): RedirectResponse
    {
        return redirect()->route('platform.communications.reminders', array_filter([
            'status' => $request->input('status'),
        ], fn (mixed $value): bool => filled($value)));
    }

    public function save(
        CommunicationReminderRequest $request,
        CreateOrUpdateCommunicationReminderAction $saveReminder,
    ): RedirectResponse {
        $saveReminder->handle($request->reminderId(), $request->reminderData());

        Toast::info(tkey('communication.common.messages.saved'));

        return redirect()->route('platform.communications.reminders');
    }

    public function complete(Request $request, CompleteCommunicationReminderAction $completeReminder): RedirectResponse
    {
        abort_unless($request->user()?->hasAccess('communications.reminders.manage'), 403);

        $reminder = CommunicationReminder::query()->findOrFail((int) $request->input('reminder'));
        $completeReminder->handle($reminder, $request->user());

        Toast::info(tkey('communication.common.messages.saved'));

        return redirect()->route('platform.communications.reminders');
    }

    /**
     * @return array<string, string>
     */
    private function statusOptions(): array
    {
        return collect(CommunicationReminder::statusValues())
            ->mapWithKeys(fn (string $status): array => [$status => tkey('communication.reminders.statuses.'.$status)])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    private function priorityOptions(): array
    {
        return collect(CommunicationReminder::priorityValues())
            ->mapWithKeys(fn (string $priority): array => [$priority => tkey('communication.reminders.priorities.'.$priority)])
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private function templateOptions(): array
    {
        return CommunicationTemplate::query()
            ->active()
            ->ordered()
            ->limit(100)
            ->get(['id', 'name_translations'])
            ->mapWithKeys(fn (CommunicationTemplate $template): array => [$template->id => $template->displayName()])
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private function userOptions(): array
    {
        return User::query()
            ->orderBy('name')
            ->limit(100)
            ->pluck('name', 'id')
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private function leadOptions(): array
    {
        return Lead::query()
            ->forLeadList()
            ->latest()
            ->limit(100)
            ->get()
            ->mapWithKeys(fn (Lead $lead): array => [$lead->id => $lead->fullName()])
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private function studentOptions(): array
    {
        return Student::query()
            ->forCrmList()
            ->latest()
            ->limit(100)
            ->get()
            ->mapWithKeys(fn (Student $student): array => [$student->id => $student->display_name])
            ->all();
    }
}
