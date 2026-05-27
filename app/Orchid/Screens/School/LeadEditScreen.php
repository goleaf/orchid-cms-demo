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
use App\Models\MarketingLead;
use App\Models\MarketingLeadComment;
use App\Models\MarketingLeadCommunication;
use App\Models\MarketingLeadDocument;
use App\Models\MarketingLeadStatusHistory;
use App\Models\MarketingLeadTask;
use App\Models\TrainingGroup;
use App\Models\TrainingProgram;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
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
                    ->select(['id', 'marketing_lead_id', 'user_id', 'channel', 'direction', 'subject', 'body', 'communicated_at'])
                    ->with('user:id,name')
                    ->latest('communicated_at')
                    ->limit(10),
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
        return 'CRM lead: '.($this->lead?->fullName() ?? 'Lead');
    }

    public function description(): ?string
    {
        return 'Sales card with contact data, UTM, manager, comments, communication history, and documents.';
    }

    public function permission(): iterable
    {
        return ['platform.marketing.leads'];
    }

    public function commandBar(): iterable
    {
        return [
            Link::make('Back to leads')
                ->icon('bs.arrow-left')
                ->route('platform.marketing.leads'),

            Button::make('Save CRM card')
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
                        ->title('First name')
                        ->required(),
                    Input::make('lead.last_name')
                        ->title('Last name'),
                    Input::make('lead.phone')
                        ->title('Phone'),
                    Input::make('lead.email')
                        ->title('Email')
                        ->type('email'),
                    Input::make('lead.messenger')
                        ->title('Messenger'),
                    Input::make('lead.city')
                        ->title('City'),
                ])->title('Contact'),

                Layout::rows([
                    Select::make('lead_status')
                        ->title('Status')
                        ->options($this->leadStatusOptions())
                        ->required(),
                    Select::make('lead.responsible_manager_id')
                        ->title('Responsible manager')
                        ->options($this->managers)
                        ->empty('No manager'),
                    Select::make('lead.branch_id')
                        ->title('Branch')
                        ->options($this->branches)
                        ->empty('No branch'),
                    Select::make('lead.training_program_id')
                        ->title('Desired course')
                        ->options($this->programs)
                        ->empty('No course'),
                    Select::make('lead.training_group_id')
                        ->title('Desired group')
                        ->options($this->groups)
                        ->empty('No group'),
                    Select::make('lead.instructor_id')
                        ->title('Preferred instructor')
                        ->options($this->instructors)
                        ->empty('No instructor'),
                ])->title('Sales ownership'),
            ]),

            Layout::columns([
                Layout::rows([
                    Input::make('lead.license_category')
                        ->title('License category'),
                    Input::make('lead.preferred_format')
                        ->title('Preferred format'),
                    Input::make('lead.preferred_language')
                        ->title('Training language'),
                    Input::make('lead.preferred_time')
                        ->title('Preferred training time'),
                    Input::make('lead_budget_eur')
                        ->title('Budget EUR')
                        ->type('number')
                        ->step('0.01'),
                    Select::make('lead.is_hot')
                        ->title('Hot lead')
                        ->options([
                            0 => 'No',
                            1 => 'Yes',
                        ]),
                    Input::make('lead.next_follow_up_at')
                        ->title('Next follow-up')
                        ->type('datetime-local'),
                ])->title('Training intent'),

                Layout::rows([
                    Input::make('lead.source')
                        ->title('Source')
                        ->required(),
                    Input::make('lead.utm_source')
                        ->title('UTM source')
                        ->disabled(),
                    Input::make('lead.utm_medium')
                        ->title('UTM medium')
                        ->disabled(),
                    Input::make('lead.utm_campaign')
                        ->title('UTM campaign')
                        ->disabled(),
                    Input::make('lead.referrer_url')
                        ->title('Referrer')
                        ->disabled(),
                ])->title('Attribution'),
            ]),

            Layout::rows([
                TextArea::make('lead.message')
                    ->title('Lead message')
                    ->rows(4),
                TextArea::make('lead.rejection_reason')
                    ->title('Rejection reason')
                    ->rows(3),
            ])->title('Notes'),

            Layout::columns([
                Layout::rows([
                    TextArea::make('comment.body')
                        ->title('New comment')
                        ->rows(3),
                    Button::make('Add comment')
                        ->icon('bs.chat-left-text')
                        ->method('addComment'),
                ])->title('Comments'),

                Layout::rows([
                    Select::make('communication.channel')
                        ->title('Channel')
                        ->options([
                            'phone' => 'Phone',
                            'email' => 'Email',
                            'whatsapp' => 'WhatsApp',
                            'telegram' => 'Telegram',
                            'viber' => 'Viber',
                            'web_form' => 'Web form',
                        ])
                        ->empty('Select channel'),
                    Select::make('communication.direction')
                        ->title('Direction')
                        ->options([
                            'inbound' => 'Inbound',
                            'outbound' => 'Outbound',
                        ])
                        ->empty('Select direction'),
                    Input::make('communication.subject')
                        ->title('Subject'),
                    TextArea::make('communication.body')
                        ->title('Communication note')
                        ->rows(3),
                    Button::make('Add communication')
                        ->icon('bs.telephone')
                        ->method('addCommunication'),
                ])->title('Communication history'),
            ]),

            Layout::table('lead.comments', [
                TD::make('created_at', 'Date')
                    ->render(fn (MarketingLeadComment $comment): string => $comment->created_at->format('Y-m-d H:i')),
                TD::make('user', 'User')
                    ->render(fn (MarketingLeadComment $comment): string => $comment->user?->name ?? 'System'),
                TD::make('body', 'Comment')
                    ->render(fn (MarketingLeadComment $comment): string => $comment->body),
            ])->title('Latest comments'),

            Layout::table('lead.communications', [
                TD::make('communicated_at', 'Date')
                    ->render(fn (MarketingLeadCommunication $communication): string => $communication->communicated_at->format('Y-m-d H:i')),
                TD::make('channel', 'Channel')
                    ->render(fn (MarketingLeadCommunication $communication): string => str($communication->channel)->replace('_', ' ')->title()->toString()),
                TD::make('direction', 'Direction')
                    ->render(fn (MarketingLeadCommunication $communication): string => str($communication->direction)->title()->toString()),
                TD::make('subject', 'Subject')
                    ->render(fn (MarketingLeadCommunication $communication): string => $communication->subject ?? '-'),
                TD::make('body', 'Body')
                    ->render(fn (MarketingLeadCommunication $communication): string => $communication->body ?? '-'),
            ])->title('Latest communications'),

            Layout::table('lead.tasks', [
                TD::make('due_at', 'Due')
                    ->render(fn (MarketingLeadTask $task): string => $task->due_at?->format('Y-m-d H:i') ?? '-'),
                TD::make('title', 'Task')
                    ->render(fn (MarketingLeadTask $task): string => $task->title),
                TD::make('assignedTo', 'Manager')
                    ->render(fn (MarketingLeadTask $task): string => $task->assignedTo?->name ?? '-'),
                TD::make('priority', 'Priority')
                    ->render(fn (MarketingLeadTask $task): string => str($task->priority->value)->title()->toString()),
                TD::make('status', 'Status')
                    ->render(fn (MarketingLeadTask $task): string => str($task->status->value)->title()->toString()),
            ])->title('Manager tasks'),

            Layout::table('lead.statusHistories', [
                TD::make('changed_at', 'Changed')
                    ->render(fn (MarketingLeadStatusHistory $history): string => $history->changed_at->format('Y-m-d H:i')),
                TD::make('from_status', 'From')
                    ->render(fn (MarketingLeadStatusHistory $history): string => $history->from_status?->label() ?? '-'),
                TD::make('to_status', 'To')
                    ->render(fn (MarketingLeadStatusHistory $history): string => $history->to_status->label()),
                TD::make('user', 'User')
                    ->render(fn (MarketingLeadStatusHistory $history): string => $history->user?->name ?? 'System'),
                TD::make('reason', 'Reason')
                    ->render(fn (MarketingLeadStatusHistory $history): string => $history->reason ?? '-'),
            ])->title('Status history'),

            Layout::table('lead.documents', [
                TD::make('original_name', 'Document')
                    ->render(fn (MarketingLeadDocument $document): string => $document->original_name),
                TD::make('mime_type', 'Type')
                    ->render(fn (MarketingLeadDocument $document): string => $document->mime_type ?? '-'),
                TD::make('size', 'Size')
                    ->render(fn (MarketingLeadDocument $document): string => number_format($document->size / 1024, 1).' KB'),
                TD::make('created_at', 'Uploaded')
                    ->render(fn (MarketingLeadDocument $document): string => $document->created_at->format('Y-m-d H:i')),
            ])->title('Attached documents'),
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
            $moveLead->handle($lead->refresh(), $targetStatus, $request->user(), 'CRM card status update.');
        }

        Toast::info('Lead CRM card updated.');

        return redirect()->route('platform.marketing.leads.edit', $lead);
    }

    public function addComment(MarketingLead $lead, Request $request, AddLeadCommentAction $addComment): RedirectResponse
    {
        $data = $request->validate([
            'comment.body' => ['required', 'string', 'max:2000'],
        ]);

        $addComment->handle($lead, $request->user(), $data['comment']['body']);

        Toast::info('Comment added.');

        return redirect()->route('platform.marketing.leads.edit', $lead);
    }

    public function addCommunication(MarketingLead $lead, Request $request, AddLeadCommunicationAction $addCommunication): RedirectResponse
    {
        $data = $request->validate([
            'communication.channel' => ['required', 'string', 'max:60'],
            'communication.direction' => ['required', Rule::in(['inbound', 'outbound'])],
            'communication.subject' => ['nullable', 'string', 'max:190'],
            'communication.body' => ['nullable', 'string', 'max:2000'],
        ]);

        $addCommunication->handle(
            $lead,
            $request->user(),
            $data['communication']['channel'],
            $data['communication']['direction'],
            $data['communication']['subject'] ?? null,
            $data['communication']['body'] ?? null,
        );

        Toast::info('Communication added.');

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
}
