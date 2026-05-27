<?php

declare(strict_types=1);

namespace App\Orchid\Screens\School;

use App\Actions\AddLeadCommentAction;
use App\Actions\AddLeadCommunicationAction;
use App\Actions\MoveLeadToStatusAction;
use App\Actions\UpdateMarketingLeadCrmAction;
use App\Enums\LeadStatus;
use App\Models\Branch;
use App\Models\Instructor;
use App\Models\LeadLostReason;
use App\Models\LeadSource;
use App\Models\MarketingLead;
use App\Models\MarketingLeadComment;
use App\Models\MarketingLeadCommunication;
use App\Models\MarketingLeadDocument;
use App\Models\MarketingLeadStatusHistory;
use App\Models\MarketingLeadTask;
use App\Models\MarketingMessageTemplate;
use App\Models\TrainingGroup;
use App\Models\TrainingProgram;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\CheckBox;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class LeadEditScreen extends Screen
{
    public ?MarketingLead $lead = null;

    /**
     * @var array<int, string>
     */
    private array $managers = [];

    /**
     * @var array<int, string>
     */
    private array $branches = [];

    /**
     * @var array<int, string>
     */
    private array $programs = [];

    /**
     * @var array<int, string>
     */
    private array $groups = [];

    /**
     * @var array<int, string>
     */
    private array $instructors = [];

    /**
     * @var array<string, string>
     */
    private array $sources = [];

    /**
     * @var array<string, string>
     */
    private array $lostReasons = [];

    /**
     * @var array<int, string>
     */
    private array $messageTemplates = [];

    /**
     * @return array<string, mixed>
     */
    public function query(MarketingLead $lead): iterable
    {
        $this->lead = MarketingLead::query()
            ->forCrmDetail()
            ->with([
                'responsibleManager:id,name,email',
                'branch:id,name,city',
                'trainingProgram:id,title,slug,license_category',
                'trainingGroup:id,name,code',
                'instructor:id,name',
                'marketingCampaign:id,name,channel',
                'convertedStudentProfile:id,first_name,last_name',
                'documents:id,marketing_lead_id,original_name,mime_type,size,created_at',
                'comments' => fn ($query) => $query
                    ->select(['id', 'marketing_lead_id', 'user_id', 'body', 'is_internal', 'created_at'])
                    ->with('user:id,name')
                    ->latest()
                    ->limit(10),
                'communications' => fn ($query) => $query
                    ->select([
                        'id',
                        'marketing_lead_id',
                        'user_id',
                        'marketing_message_template_id',
                        'channel',
                        'direction',
                        'subject',
                        'body',
                        'communicated_at',
                        'client_replied_at',
                        'callback_required_at',
                        'call_recording_url',
                        'call_recording_reference',
                    ])
                    ->with(['user:id,name', 'messageTemplate:id,name'])
                    ->latest('communicated_at')
                    ->limit(20),
                'statusHistories' => fn ($query) => $query
                    ->select(['id', 'marketing_lead_id', 'user_id', 'from_status', 'to_status', 'reason', 'changed_at'])
                    ->with('user:id,name')
                    ->latest('changed_at')
                    ->limit(10),
                'tasks' => fn ($query) => $query
                    ->select(['id', 'marketing_lead_id', 'assigned_to_user_id', 'title', 'status', 'priority', 'due_at', 'completed_at'])
                    ->with('assignedTo:id,name')
                    ->latest('due_at')
                    ->limit(10),
            ])
            ->whereKey($lead->id)
            ->firstOrFail();

        $this->managers = User::query()
            ->select(['id', 'name'])
            ->orderBy('name')
            ->limit(50)
            ->pluck('name', 'id')
            ->all();
        $this->branches = Branch::query()
            ->forAdminList()
            ->orderBy('city')
            ->pluck('name', 'id')
            ->all();
        $this->programs = TrainingProgram::query()
            ->forAcademyList()
            ->orderBy('title')
            ->pluck('title', 'id')
            ->all();
        $this->groups = TrainingGroup::query()
            ->operationalList()
            ->orderBy('starts_on')
            ->pluck('code', 'id')
            ->all();
        $this->instructors = Instructor::query()
            ->forAdminList()
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
        $this->sources = LeadSource::translatedLabels();
        $this->lostReasons = LeadLostReason::translatedLabels();
        $this->messageTemplates = MarketingMessageTemplate::query()
            ->active()
            ->select(['id', 'name', 'channel', 'sort_order'])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->limit(100)
            ->get()
            ->mapWithKeys(fn (MarketingMessageTemplate $template): array => [
                $template->id => $template->displayName(),
            ])
            ->all();

        return [
            'lead' => $this->lead,
            'lead_status' => $this->lead->status->value,
            'lead.next_follow_up_at' => $this->lead->next_follow_up_at?->format('Y-m-d\TH:i'),
            'lead_budget_eur' => $this->lead->budget_cents !== null
                ? number_format($this->lead->budget_cents / 100, 2, '.', '')
                : null,
        ];
    }

    public function name(): ?string
    {
        return tkey('crm.leads.edit_title', [
            'name' => $this->lead?->fullName() ?? tkey('crm.leads.fallback.lead'),
        ]);
    }

    public function description(): ?string
    {
        return tkey('crm.leads.edit_description');
    }

    public function permission(): iterable
    {
        return ['platform.marketing.leads', 'website.update_leads'];
    }

    public function commandBar(): iterable
    {
        return [
            Link::make(tkey('crm.leads.actions.back_to_leads'))
                ->icon('bs.arrow-left')
                ->route('platform.marketing.leads'),

            Button::make(tkey('crm.leads.actions.save_crm_card'))
                ->icon('bs.check2-circle')
                ->method('save'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::columns([
                Layout::rows([
                    Input::make('lead.first_name')
                        ->title(tkey('crm.leads.fields.first_name'))
                        ->required(),
                    Input::make('lead.last_name')
                        ->title(tkey('crm.leads.fields.last_name')),
                    Input::make('lead.phone')
                        ->title(tkey('crm.leads.fields.phone')),
                    Input::make('lead.email')
                        ->title(tkey('crm.leads.fields.email'))
                        ->type('email'),
                    Input::make('lead.messenger')
                        ->title(tkey('crm.leads.fields.preferred_messenger')),
                    Input::make('lead.city')
                        ->title(tkey('crm.leads.fields.city')),
                ])->title(tkey('crm.leads.sections.main_information')),

                Layout::rows([
                    Select::make('lead_status')
                        ->title(tkey('crm.leads.fields.status'))
                        ->options($this->leadStatusOptions())
                        ->required(),
                    Select::make('lead.responsible_manager_id')
                        ->title(tkey('crm.leads.fields.manager'))
                        ->options($this->managers)
                        ->empty(tkey('crm.leads.empty.no_manager')),
                    Select::make('lead.branch_id')
                        ->title(tkey('crm.leads.fields.branch'))
                        ->options($this->branches)
                        ->empty(tkey('crm.leads.empty.no_branch')),
                    Select::make('lead.training_program_id')
                        ->title(tkey('crm.leads.fields.course'))
                        ->options($this->programs)
                        ->empty(tkey('crm.leads.empty.no_course')),
                    Select::make('lead.training_group_id')
                        ->title(tkey('crm.leads.fields.training_group'))
                        ->options($this->groups)
                        ->empty(tkey('crm.leads.empty.no_group')),
                    Select::make('lead.instructor_id')
                        ->title(tkey('crm.leads.fields.instructor'))
                        ->options($this->instructors)
                        ->empty(tkey('crm.leads.empty.no_instructor')),
                ])->title(tkey('crm.leads.sections.crm_information')),
            ]),

            Layout::columns([
                Layout::rows([
                    Input::make('lead.license_category')
                        ->title(tkey('crm.leads.fields.course_category')),
                    Input::make('lead.preferred_format')
                        ->title(tkey('crm.leads.fields.preferred_format')),
                    Input::make('lead.preferred_language')
                        ->title(tkey('crm.leads.fields.locale')),
                    Input::make('lead.preferred_time')
                        ->title(tkey('crm.leads.fields.preferred_time')),
                    Input::make('lead_budget_eur')
                        ->title(tkey('crm.leads.fields.budget'))
                        ->type('number')
                        ->step('0.01'),
                    Select::make('lead.is_hot')
                        ->title(tkey('crm.leads.fields.priority'))
                        ->options([
                            0 => tkey('common.status.no'),
                            1 => tkey('common.status.yes'),
                        ]),
                    Input::make('lead.next_follow_up_at')
                        ->title(tkey('crm.leads.fields.next_follow_up_at'))
                        ->type('datetime-local'),
                ])->title(tkey('crm.leads.sections.training_interest')),

                Layout::rows([
                    Select::make('lead.source')
                        ->title(tkey('crm.leads.fields.source'))
                        ->options($this->sources)
                        ->required(),
                    Input::make('lead.utm_source')
                        ->title(tkey('crm.leads.fields.utm_source'))
                        ->disabled(),
                    Input::make('lead.utm_medium')
                        ->title(tkey('crm.leads.fields.utm_medium'))
                        ->disabled(),
                    Input::make('lead.utm_campaign')
                        ->title(tkey('crm.leads.fields.utm_campaign'))
                        ->disabled(),
                    Input::make('lead.utm_content')
                        ->title(tkey('crm.leads.fields.utm_content'))
                        ->disabled(),
                    Input::make('lead.utm_term')
                        ->title(tkey('crm.leads.fields.utm_term'))
                        ->disabled(),
                    Input::make('lead.referrer_url')
                        ->title(tkey('crm.leads.fields.referrer'))
                        ->disabled(),
                ])->title(tkey('crm.leads.sections.marketing_data')),
            ]),

            Layout::rows([
                TextArea::make('lead.message')
                    ->title(tkey('crm.leads.fields.comment'))
                    ->rows(4),
                Select::make('lead.lost_reason_code')
                    ->title(tkey('crm.leads.fields.lost_reason'))
                    ->options($this->lostReasons)
                    ->empty(tkey('crm.leads.empty.no_lost_reason')),
                TextArea::make('lead.rejection_reason')
                    ->title(tkey('crm.leads.fields.internal_comment'))
                    ->rows(3),
            ])->title(tkey('crm.leads.sections.system_data')),

            Layout::columns([
                Layout::rows([
                    TextArea::make('comment.body')
                        ->title(tkey('crm.leads.fields.comment'))
                        ->rows(3),
                    Button::make(tkey('crm.leads.actions.add_note'))
                        ->icon('bs.chat-left-text')
                        ->method('addComment'),
                ])->title(tkey('crm.leads.sections.activity_timeline')),

                Layout::rows([
                    Select::make('communication.channel')
                        ->title(tkey('crm.communications.fields.channel'))
                        ->options($this->communicationChannels())
                        ->empty(tkey('crm.communications.empty.select_channel')),
                    Select::make('communication.template_id')
                        ->title(tkey('crm.communications.fields.message_template'))
                        ->options($this->messageTemplates)
                        ->empty(tkey('crm.communications.empty.no_template')),
                    Select::make('communication.direction')
                        ->title(tkey('crm.communications.fields.direction'))
                        ->options($this->communicationDirections())
                        ->empty(tkey('crm.communications.empty.select_direction')),
                    Input::make('communication.subject')
                        ->title(tkey('crm.communications.fields.subject')),
                    TextArea::make('communication.body')
                        ->title(tkey('crm.communications.fields.body'))
                        ->rows(3),
                    CheckBox::make('communication.client_replied')
                        ->sendTrueOrFalse()
                        ->title(tkey('crm.communications.fields.client_replied'))
                        ->placeholder(tkey('crm.communications.fields.client_replied')),
                    CheckBox::make('communication.callback_required')
                        ->sendTrueOrFalse()
                        ->title(tkey('crm.communications.fields.callback_required'))
                        ->placeholder(tkey('crm.communications.fields.callback_required')),
                    Input::make('communication.callback_required_at')
                        ->title(tkey('crm.communications.fields.callback_required_at'))
                        ->type('datetime-local'),
                    Input::make('communication.call_recording_url')
                        ->title(tkey('crm.communications.fields.call_recording_url'))
                        ->type('url'),
                    Input::make('communication.call_recording_reference')
                        ->title(tkey('crm.communications.fields.call_recording_reference')),
                    Button::make(tkey('crm.leads.actions.log_call'))
                        ->icon('bs.telephone')
                        ->method('addCommunication'),
                ])->title(tkey('crm.communications.title')),
            ]),

            Layout::table('lead.comments', [
                TD::make('created_at', tkey('crm.leads.columns.created_at'))
                    ->render(fn (MarketingLeadComment $comment): string => $comment->created_at->format('Y-m-d H:i')),
                TD::make('user', tkey('crm.leads.columns.user'))
                    ->render(fn (MarketingLeadComment $comment): string => $comment->user?->name ?? tkey('common.system')),
                TD::make('body', tkey('crm.leads.fields.comment'))
                    ->render(fn (MarketingLeadComment $comment): string => $comment->body),
            ])->title(tkey('crm.leads.sections.latest_comments')),

            Layout::table('lead.communications', [
                TD::make('communicated_at', tkey('crm.leads.columns.created_at'))
                    ->render(fn (MarketingLeadCommunication $communication): string => $communication->communicated_at->format('Y-m-d H:i')),
                TD::make('channel', tkey('crm.communications.fields.channel'))
                    ->render(fn (MarketingLeadCommunication $communication): string => tkey('crm.communication.channels.'.$communication->channel)),
                TD::make('direction', tkey('crm.communications.fields.direction'))
                    ->render(fn (MarketingLeadCommunication $communication): string => tkey('crm.communications.directions.'.$communication->direction)),
                TD::make('messageTemplate', tkey('crm.communications.fields.message_template'))
                    ->render(fn (MarketingLeadCommunication $communication): string => $communication->messageTemplate?->name ?? '-'),
                TD::make('subject', tkey('crm.communications.fields.subject'))
                    ->render(fn (MarketingLeadCommunication $communication): string => $communication->subject ?? '-'),
                TD::make('body', tkey('crm.communications.fields.body'))
                    ->render(fn (MarketingLeadCommunication $communication): string => $communication->body ?? '-'),
                TD::make('flags', tkey('crm.communications.fields.flags'))
                    ->render(fn (MarketingLeadCommunication $communication): string => $this->communicationFlags($communication)),
                TD::make('recording', tkey('crm.communications.fields.recording'))
                    ->render(fn (MarketingLeadCommunication $communication): string => $communication->call_recording_url ?? $communication->call_recording_reference ?? '-'),
            ])->title(tkey('crm.communications.recent_title')),

            Layout::table('lead.tasks', [
                TD::make('due_at', tkey('crm.tasks.fields.due_at'))
                    ->render(fn (MarketingLeadTask $task): string => $task->due_at?->format('Y-m-d H:i') ?? '-'),
                TD::make('title', tkey('crm.tasks.fields.title'))
                    ->render(fn (MarketingLeadTask $task): string => $task->title),
                TD::make('assignedTo', tkey('crm.tasks.fields.assigned_to'))
                    ->render(fn (MarketingLeadTask $task): string => $task->assignedTo?->name ?? '-'),
                TD::make('priority', tkey('crm.tasks.fields.priority'))
                    ->render(fn (MarketingLeadTask $task): string => $task->priority->label()),
                TD::make('status', tkey('crm.tasks.fields.status'))
                    ->render(fn (MarketingLeadTask $task): string => $task->status->label()),
            ])->title(tkey('crm.leads.sections.tasks')),

            Layout::table('lead.statusHistories', [
                TD::make('changed_at', tkey('crm.status_history.fields.changed_at'))
                    ->render(fn (MarketingLeadStatusHistory $history): string => $history->changed_at->format('Y-m-d H:i')),
                TD::make('from_status', tkey('crm.status_history.fields.from_status'))
                    ->render(fn (MarketingLeadStatusHistory $history): string => $history->from_status?->label() ?? '-'),
                TD::make('to_status', tkey('crm.status_history.fields.to_status'))
                    ->render(fn (MarketingLeadStatusHistory $history): string => $history->to_status->label()),
                TD::make('user', tkey('crm.leads.columns.user'))
                    ->render(fn (MarketingLeadStatusHistory $history): string => $history->user?->name ?? tkey('common.system')),
                TD::make('reason', tkey('crm.status_history.fields.reason'))
                    ->render(fn (MarketingLeadStatusHistory $history): string => $history->reason ?? '-'),
            ])->title(tkey('crm.status_history.title')),

            Layout::table('lead.documents', [
                TD::make('original_name', tkey('crm.documents.fields.document'))
                    ->render(fn (MarketingLeadDocument $document): string => $document->original_name),
                TD::make('mime_type', tkey('crm.documents.fields.type'))
                    ->render(fn (MarketingLeadDocument $document): string => $document->mime_type ?? '-'),
                TD::make('size', tkey('crm.documents.fields.size'))
                    ->render(fn (MarketingLeadDocument $document): string => number_format($document->size / 1024, 1).' KB'),
                TD::make('created_at', tkey('crm.documents.fields.uploaded_at'))
                    ->render(fn (MarketingLeadDocument $document): string => $document->created_at->format('Y-m-d H:i')),
            ])->title(tkey('crm.documents.title')),
        ];
    }

    public function save(
        MarketingLead $lead,
        Request $request,
        UpdateMarketingLeadCrmAction $updateLead,
        MoveLeadToStatusAction $moveLead,
    ): RedirectResponse {
        $data = $request->validate([
            'lead.responsible_manager_id' => ['nullable', 'integer', 'exists:users,id'],
            'lead.branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'lead.training_program_id' => ['nullable', 'integer', 'exists:training_programs,id'],
            'lead.training_group_id' => ['nullable', 'integer', 'exists:training_groups,id'],
            'lead.instructor_id' => ['nullable', 'integer', 'exists:instructors,id'],
            'lead.first_name' => ['required', 'string', 'max:120'],
            'lead.last_name' => ['nullable', 'string', 'max:120'],
            'lead.email' => ['nullable', 'email:rfc', 'max:190'],
            'lead.phone' => ['nullable', 'string', 'max:60'],
            'lead.messenger' => ['nullable', 'string', 'max:80'],
            'lead.city' => ['nullable', 'string', 'max:120'],
            'lead.source' => ['required', 'string', 'max:120'],
            'lead_status' => ['required', Rule::enum(LeadStatus::class)],
            'lead.license_category' => ['nullable', 'string', 'max:40'],
            'lead.preferred_format' => ['nullable', 'string', 'max:60'],
            'lead.preferred_language' => ['nullable', 'string', 'max:60'],
            'lead.preferred_time' => ['nullable', 'string', 'max:120'],
            'lead.is_hot' => ['nullable', 'boolean'],
            'lead.next_follow_up_at' => ['nullable', 'date'],
            'lead.message' => ['nullable', 'string', 'max:2000'],
            'lead.lost_reason_code' => ['nullable', 'string', Rule::in(array_keys(LeadLostReason::translatedLabels()))],
            'lead.rejection_reason' => ['nullable', 'string', 'max:2000'],
            'lead_budget_eur' => ['nullable', 'numeric', 'min:0', 'max:100000'],
        ]);

        $targetStatus = LeadStatus::from($data['lead_status']);
        $currentStatus = $lead->status;

        $updateLead->handle($lead, [
            ...$data['lead'],
            'status' => $currentStatus,
            'budget_eur' => $data['lead_budget_eur'] ?? null,
        ]);

        if ($currentStatus !== $targetStatus) {
            $moveLead->handle($lead->refresh(), $targetStatus, $request->user(), tkey('crm.activities.reasons.crm_card_status_update'));
        }

        Toast::info(tkey('crm.leads.messages.updated'));

        return redirect()->route('platform.marketing.leads.edit', $lead);
    }

    public function addComment(MarketingLead $lead, Request $request, AddLeadCommentAction $addComment): RedirectResponse
    {
        $data = $request->validate([
            'comment.body' => ['required', 'string', 'max:2000'],
        ]);

        $addComment->handle($lead, $request->user(), $data['comment']['body']);

        Toast::info(tkey('crm.leads.messages.comment_added'));

        return redirect()->route('platform.marketing.leads.edit', $lead);
    }

    public function addCommunication(MarketingLead $lead, Request $request, AddLeadCommunicationAction $addCommunication): RedirectResponse
    {
        $data = $request->validate([
            'communication.channel' => ['required', 'string', 'max:60', Rule::in(array_keys($this->communicationChannels()))],
            'communication.template_id' => ['nullable', 'integer', 'exists:marketing_message_templates,id'],
            'communication.direction' => ['required', Rule::in(array_keys($this->communicationDirections()))],
            'communication.subject' => ['nullable', 'string', 'max:190'],
            'communication.body' => ['nullable', 'required_without_all:communication.template_id,communication.subject,communication.call_recording_url,communication.call_recording_reference', 'string', 'max:2000'],
            'communication.client_replied' => ['nullable', 'boolean'],
            'communication.callback_required' => ['nullable', 'boolean'],
            'communication.callback_required_at' => ['nullable', 'date'],
            'communication.call_recording_url' => ['nullable', 'url', 'max:500'],
            'communication.call_recording_reference' => ['nullable', 'string', 'max:190'],
        ]);
        $payload = $data['communication'];
        $template = filled($payload['template_id'] ?? null)
            ? MarketingMessageTemplate::query()
                ->active()
                ->forChannel($payload['channel'])
                ->whereKey($payload['template_id'])
                ->firstOrFail()
            : null;

        $addCommunication->handle(
            $lead,
            $request->user(),
            $payload['channel'],
            $payload['direction'],
            $payload['subject'] ?? null,
            $payload['body'] ?? null,
            null,
            $template,
            (bool) ($payload['client_replied'] ?? false),
            (bool) ($payload['callback_required'] ?? false),
            filled($payload['callback_required_at'] ?? null) ? Carbon::parse($payload['callback_required_at']) : null,
            $payload['call_recording_url'] ?? null,
            $payload['call_recording_reference'] ?? null,
        );

        Toast::info(tkey('crm.leads.messages.communication_added'));

        return redirect()->route('platform.marketing.leads.edit', $lead);
    }

    /**
     * @return array<string, string>
     */
    private function leadStatusOptions(): array
    {
        return collect(LeadStatus::cases())
            ->mapWithKeys(fn (LeadStatus $status): array => [
                $status->value => $status->label(),
            ])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    private function communicationChannels(): array
    {
        return MarketingMessageTemplate::channelOptions();
    }

    /**
     * @return array<string, string>
     */
    private function communicationDirections(): array
    {
        return [
            'inbound' => tkey('crm.communications.directions.inbound'),
            'outbound' => tkey('crm.communications.directions.outbound'),
        ];
    }

    private function communicationFlags(MarketingLeadCommunication $communication): string
    {
        return collect([
            $communication->hasClientReply() ? tkey('crm.communications.flags.client_replied') : null,
            $communication->needsCallback() ? tkey('crm.communications.flags.callback_at', [
                'time' => $communication->callback_required_at?->format('Y-m-d H:i'),
            ]) : null,
        ])->filter()->join(' / ') ?: '-';
    }
}
