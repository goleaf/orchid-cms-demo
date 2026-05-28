<?php

declare(strict_types=1);

namespace App\Orchid\Screens\School;

use App\Actions\AddLeadCommentAction;
use App\Actions\AddLeadCommunicationAction;
use App\Actions\AddLeadNoteAction;
use App\Actions\AssignLeadManagerAction;
use App\Actions\CancelLeadTaskAction;
use App\Actions\ChangeLeadStatusAction;
use App\Actions\CompleteLeadTaskAction;
use App\Actions\CreateLeadTaskAction;
use App\Actions\LogLeadCallAction;
use App\Actions\MarkLeadDuplicateAction;
use App\Actions\MarkLeadLostAction;
use App\Actions\MarkLeadSpamAction;
use App\Actions\PrepareLeadForEnrollmentAction;
use App\Actions\PrepareLeadForStudentConversionAction;
use App\Actions\ReopenLeadAction;
use App\Actions\SaveMarketingLeadCrmAction;
use App\Enums\LeadStatus;
use App\Http\Requests\Marketing\AddLeadNoteRequest;
use App\Http\Requests\Marketing\AssignLeadManagerRequest;
use App\Http\Requests\Marketing\CancelLeadTaskRequest;
use App\Http\Requests\Marketing\ChangeLeadStatusRequest;
use App\Http\Requests\Marketing\LeadCommentRequest;
use App\Http\Requests\Marketing\LeadCommunicationRequest;
use App\Http\Requests\Marketing\LeadCrmRequest;
use App\Http\Requests\Marketing\LeadDuplicateRequest;
use App\Http\Requests\Marketing\LeadLostRequest;
use App\Http\Requests\Marketing\LeadStatusActionRequest;
use App\Http\Requests\Marketing\LeadTaskCompletionRequest;
use App\Http\Requests\Marketing\LeadTaskRequest;
use App\Http\Requests\Marketing\LogLeadCallRequest;
use App\Http\Requests\Marketing\MarkLeadDuplicateRequest;
use App\Http\Requests\Marketing\MarkLeadLostRequest;
use App\Http\Requests\Marketing\MarkLeadSpamRequest;
use App\Http\Requests\Marketing\ReopenLeadRequest;
use App\Http\Requests\Marketing\StoreLeadTaskRequest;
use App\Models\Branch;
use App\Models\Instructor;
use App\Models\LeadLostReason;
use App\Models\LeadSource;
use App\Models\LeadTag;
use App\Models\MarketingLead;
use App\Models\MarketingLeadActivity;
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
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
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
    /** @var MarketingLead|null */
    public $lead = null;

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
    private array $tags = [];

    /**
     * @var array<int, string>
     */
    private array $messageTemplates = [];

    /**
     * @return array<string, mixed>
     */
    public function query(?MarketingLead $lead = null): iterable
    {
        $this->lead = $lead?->exists
            ? MarketingLead::query()
                ->forCrmDetail()
                ->with([
                    'responsibleManager:id,name,email',
                    'assignedBy:id,name',
                    'branch:id,name,name_translations,city,city_translations',
                    'trainingProgram:id,title,title_translations,slug,license_category',
                    'trainingGroup:id,name,name_translations,code',
                    'instructor:id,name',
                    'marketingCampaign:id,name,channel',
                    'convertedStudentProfile:id,first_name,last_name',
                    'convertedEnrollment:id,status',
                    'createdBy:id,name',
                    'updatedBy:id,name',
                    'duplicateOf:id,first_name,last_name,phone,email,status',
                    'duplicates:id,duplicate_of_id,first_name,last_name,phone,email,status,created_at',
                    'tags:id,slug,name,name_translations,color',
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
                            'call_result',
                            'duration_seconds',
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
                        ->select(['id', 'marketing_lead_id', 'assigned_to_user_id', 'title', 'status', 'priority', 'due_at', 'completed_at', 'notes'])
                        ->with('assignedTo:id,name')
                        ->latest('due_at')
                        ->limit(10),
                    'activities' => fn ($query) => $query
                        ->select(['id', 'marketing_lead_id', 'user_id', 'type', 'title', 'body', 'old_value', 'new_value', 'created_at'])
                        ->with('user:id,name')
                        ->latest()
                        ->limit(20),
                ])
                ->whereKey($lead->id)
                ->firstOrFail()
            : new MarketingLead([
                'status' => LeadStatus::New,
                'source' => 'phone',
                'priority' => 'normal',
                'lead_score' => 0,
                'consent_accepted' => false,
            ]);

        $this->managers = User::query()
            ->select(['id', 'name'])
            ->orderBy('name')
            ->limit(50)
            ->pluck('name', 'id')
            ->all();
        $this->branches = Branch::query()
            ->forAdminList()
            ->orderBy('city')
            ->get()
            ->mapWithKeys(fn (Branch $branch): array => [$branch->id => $branch->displayName()])
            ->all();
        $this->programs = TrainingProgram::query()
            ->forAcademyList()
            ->orderBy('title')
            ->get()
            ->mapWithKeys(fn (TrainingProgram $program): array => [$program->id => $program->displayTitle()])
            ->all();
        $this->groups = TrainingGroup::query()
            ->operationalList()
            ->orderBy('starts_on')
            ->get()
            ->mapWithKeys(fn (TrainingGroup $group): array => [$group->id => $group->displayName()])
            ->all();
        $this->instructors = Instructor::query()
            ->forAdminList()
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
        $this->sources = LeadSource::translatedLabels();
        $this->lostReasons = LeadLostReason::translatedLabels();
        $this->tags = LeadTag::query()
            ->active()
            ->ordered()
            ->get(['id', 'slug', 'name', 'name_translations'])
            ->mapWithKeys(fn (LeadTag $tag): array => [$tag->id => $tag->displayName()])
            ->all();
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
            'lead.tag_ids' => $this->lead->exists ? $this->lead->tags->pluck('id')->all() : [],
            'lead.next_follow_up_at' => $this->lead->next_follow_up_at?->format('Y-m-d\TH:i'),
            'lead.last_contacted_at' => $this->lead->last_contacted_at?->format('Y-m-d\TH:i'),
            'lead.created_by_name' => $this->lead->createdBy?->name,
            'lead.updated_by_name' => $this->lead->updatedBy?->name,
            'lead.converted_student_name' => $this->lead->convertedStudentProfile?->fullName(),
            'lead.converted_enrollment_label' => $this->lead->convertedEnrollment !== null
                ? '#'.$this->lead->convertedEnrollment->id
                : null,
            'lead_budget_eur' => $this->lead->budget_cents !== null
                ? number_format($this->lead->budget_cents / 100, 2, '.', '')
                : null,
        ];
    }

    public function name(): ?string
    {
        if (! ($this->lead?->exists ?? false)) {
            return tkey('crm.leads.create_title');
        }

        return tkey('crm.leads.edit_title', [
            'name' => $this->lead->fullName(),
        ]);
    }

    public function description(): ?string
    {
        return tkey('crm.leads.edit_description');
    }

    public function permission(): iterable
    {
        return ['crm.leads.create', 'crm.leads.update', 'platform.marketing.leads'];
    }

    public function commandBar(): iterable
    {
        return [
            Link::make(tkey('crm.leads.actions.back_to_leads'))
                ->icon('bs.arrow-left')
                ->route($this->leadIndexRoute()),

            Button::make(tkey('crm.leads.actions.save_crm_card'))
                ->icon('bs.check2-circle')
                ->method('save'),

            Button::make(tkey('crm.leads.actions.save_and_return'))
                ->icon('bs.check2-all')
                ->method('saveAndReturn'),

            ModalToggle::make(tkey('crm.leads.actions.change_status'))
                ->icon('bs.arrow-repeat')
                ->modal('changeStatusModal')
                ->canSee($this->lead?->exists && $this->hasCrmAccess('crm.leads.change_status')),

            ModalToggle::make(tkey('crm.leads.actions.assign_manager'))
                ->icon('bs.person-check')
                ->modal('assignManagerModal')
                ->canSee($this->lead?->exists && $this->hasCrmAccess('crm.leads.assign')),

            ModalToggle::make(tkey('crm.leads.actions.add_note'))
                ->icon('bs.chat-left-text')
                ->modal('addNoteModal')
                ->canSee($this->lead?->exists && $this->hasCrmAccess('crm.leads.update')),

            ModalToggle::make(tkey('crm.leads.actions.log_call'))
                ->icon('bs.telephone')
                ->modal('logCallModal')
                ->canSee($this->lead?->exists && $this->hasCrmAccess('crm.leads.update')),

            ModalToggle::make(tkey('crm.leads.actions.create_task'))
                ->icon('bs.check2-square')
                ->modal('createTaskModal')
                ->canSee($this->lead?->exists && $this->hasCrmAccess('crm.leads.manage_tasks')),

            ModalToggle::make(tkey('crm.leads.actions.mark_lost'))
                ->icon('bs.x-octagon')
                ->modal('markLostModal')
                ->canSee($this->lead?->exists && $this->hasCrmAccess('crm.leads.change_status')),

            ModalToggle::make(tkey('crm.leads.actions.mark_duplicate'))
                ->icon('bs.files')
                ->modal('markDuplicateModal')
                ->canSee($this->lead?->exists && $this->hasCrmAccess('crm.leads.change_status')),

            Button::make(tkey('crm.leads.actions.mark_spam'))
                ->icon('bs.exclamation-octagon')
                ->method('markSpam')
                ->confirm(tkey('crm.leads.messages.marked_spam'))
                ->canSee($this->lead?->exists && $this->hasCrmAccess('crm.leads.change_status')),

            ModalToggle::make(tkey('crm.leads.actions.reopen'))
                ->icon('bs.arrow-counterclockwise')
                ->modal('reopenModal')
                ->canSee($this->lead?->exists && $this->lead->is_closed && $this->hasCrmAccess('crm.leads.change_status')),

            Button::make(tkey('crm.leads.actions.prepare_conversion'))
                ->icon('bs.person-check')
                ->method('prepareConversion')
                ->canSee($this->lead?->exists && $this->hasCrmAccess('crm.leads.convert')),

            Link::make(tkey('crm.leads.actions.convert_to_student'))
                ->icon('bs.person-plus')
                ->href($this->lead?->exists ? route('platform.crm.leads.convert', $this->lead) : '#')
                ->canSee($this->lead?->exists && ! $this->lead?->is_converted && $this->canConvertToStudent()),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::columns([
                Layout::rows([
                    Input::make('lead.id')
                        ->type('hidden'),
                    Input::make('lead.lead_number')
                        ->title(tkey('crm.leads.fields.lead_number'))
                        ->disabled()
                        ->canSee($this->lead?->exists ?? false),
                    Input::make('lead.full_name')
                        ->title(tkey('crm.leads.fields.full_name')),
                    Input::make('lead.first_name')
                        ->title(tkey('crm.leads.fields.first_name'))
                        ->required(),
                    Input::make('lead.middle_name')
                        ->title(tkey('crm.leads.fields.middle_name')),
                    Input::make('lead.last_name')
                        ->title(tkey('crm.leads.fields.last_name')),
                    Input::make('lead.phone')
                        ->title(tkey('crm.leads.fields.phone')),
                    Input::make('lead.normalized_phone')
                        ->title(tkey('crm.leads.fields.normalized_phone'))
                        ->disabled()
                        ->canSee($this->lead?->exists ?? false),
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
                    Select::make('lead.source')
                        ->title(tkey('crm.leads.fields.source'))
                        ->options($this->sources)
                        ->required(),
                    Select::make('lead.responsible_manager_id')
                        ->title(tkey('crm.leads.fields.manager'))
                        ->options($this->managers)
                        ->empty(tkey('crm.leads.empty.no_manager')),
                    Select::make('lead.tag_ids')
                        ->title(tkey('crm.leads.fields.tags'))
                        ->options($this->tags)
                        ->multiple(),
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
                    Input::make('lead.desired_start_date')
                        ->title(tkey('crm.leads.fields.desired_start_date'))
                        ->type('date'),
                    Input::make('lead.preferred_gearbox')
                        ->title(tkey('crm.leads.fields.preferred_gearbox')),
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
                    Select::make('lead.priority')
                        ->title(tkey('crm.leads.fields.priority'))
                        ->options($this->leadPriorityOptions())
                        ->required(),
                    Input::make('lead.lead_score')
                        ->title(tkey('crm.leads.fields.lead_score'))
                        ->type('number')
                        ->min(0)
                        ->max(100),
                    Input::make('lead.last_contacted_at')
                        ->title(tkey('crm.leads.fields.last_contacted_at'))
                        ->type('datetime-local'),
                    Input::make('lead.next_follow_up_at')
                        ->title(tkey('crm.leads.fields.next_follow_up_at'))
                        ->type('datetime-local'),
                ])->title(tkey('crm.leads.sections.training_interest')),

                Layout::rows([
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
                    Input::make('lead.landing_page')
                        ->title(tkey('crm.leads.fields.landing_page'))
                        ->disabled(),
                    Input::make('lead.form_page')
                        ->title(tkey('crm.leads.fields.form_page'))
                        ->disabled(),
                    Input::make('lead.form_name')
                        ->title(tkey('crm.leads.fields.form_name'))
                        ->disabled(),
                    Input::make('lead.locale')
                        ->title(tkey('crm.leads.fields.locale'))
                        ->disabled(),
                    Input::make('lead.ip_address')
                        ->title(tkey('crm.leads.fields.ip_address'))
                        ->disabled(),
                    TextArea::make('lead.user_agent')
                        ->title(tkey('crm.leads.fields.user_agent'))
                        ->rows(3)
                        ->disabled(),
                ])
                    ->title(tkey('crm.leads.sections.marketing_data'))
                    ->canSee($this->canViewMarketing()),
            ]),

            Layout::rows([
                TextArea::make('lead.message')
                    ->title(tkey('crm.leads.fields.comment'))
                    ->rows(4),
                TextArea::make('lead.internal_comment')
                    ->title(tkey('crm.leads.fields.internal_comment'))
                    ->rows(3),
                Select::make('lead.lost_reason_code')
                    ->title(tkey('crm.leads.fields.lost_reason'))
                    ->options($this->lostReasons)
                    ->empty(tkey('crm.leads.empty.no_lost_reason')),
                TextArea::make('lead.rejection_reason')
                    ->title(tkey('crm.leads.fields.lost_comment'))
                    ->rows(3),
                Input::make('lead.duplicate_of_id')
                    ->title(tkey('crm.leads.fields.duplicate_of'))
                    ->type('number')
                    ->min(1)
                    ->disabled(),
                CheckBox::make('lead.consent_accepted')
                    ->sendTrueOrFalse()
                    ->title(tkey('crm.leads.fields.consent_accepted'))
                    ->placeholder(tkey('crm.leads.fields.consent_accepted'))
                    ->disabled(),
                Input::make('lead.consent_accepted_at')
                    ->title(tkey('crm.leads.fields.consent_accepted_at'))
                    ->disabled(),
                Input::make('lead.consent_text_version')
                    ->title(tkey('crm.leads.fields.consent_text_version'))
                    ->disabled(),
                Input::make('lead.uuid')
                    ->title(tkey('crm.leads.fields.uuid'))
                    ->disabled()
                    ->canSee($this->lead?->exists ?? false),
                Input::make('lead.created_by_name')
                    ->title(tkey('crm.leads.fields.created_by'))
                    ->disabled()
                    ->canSee($this->lead?->exists ?? false),
                Input::make('lead.updated_by_name')
                    ->title(tkey('crm.leads.fields.updated_by'))
                    ->disabled()
                    ->canSee($this->lead?->exists ?? false),
                Input::make('lead.created_at')
                    ->title(tkey('crm.leads.fields.created_at'))
                    ->disabled()
                    ->canSee($this->lead?->exists ?? false),
                Input::make('lead.updated_at')
                    ->title(tkey('crm.leads.fields.updated_at'))
                    ->disabled()
                    ->canSee($this->lead?->exists ?? false),
                Input::make('lead.closed_at')
                    ->title(tkey('crm.leads.fields.closed_at'))
                    ->disabled()
                    ->canSee($this->lead?->exists ?? false),
                Input::make('lead.converted_at')
                    ->title(tkey('crm.leads.fields.converted_at'))
                    ->disabled()
                    ->canSee($this->lead?->exists ?? false),
                Input::make('lead.converted_student_name')
                    ->title(tkey('crm.leads.fields.converted_student'))
                    ->disabled()
                    ->canSee($this->lead?->exists ?? false),
                Input::make('lead.converted_enrollment_label')
                    ->title(tkey('crm.leads.fields.converted_enrollment'))
                    ->disabled()
                    ->canSee($this->lead?->exists ?? false),
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
                    Select::make('communication.call_result')
                        ->title(tkey('crm.communications.fields.call_result'))
                        ->options($this->callResults())
                        ->empty(tkey('crm.communications.empty.no_call_result')),
                    Input::make('communication.duration_minutes')
                        ->title(tkey('crm.communications.fields.duration_minutes'))
                        ->type('number')
                        ->min(0),
                    Button::make(tkey('crm.leads.actions.log_call'))
                        ->icon('bs.telephone')
                        ->method('addCommunication'),
                ])->title(tkey('crm.communications.title')),
            ]),

            Layout::rows([
                Input::make('task.title')
                    ->title(tkey('crm.tasks.fields.title')),
                TextArea::make('task.notes')
                    ->title(tkey('crm.tasks.fields.description'))
                    ->rows(3),
                Select::make('task.assigned_to_user_id')
                    ->title(tkey('crm.tasks.fields.assigned_to'))
                    ->options($this->managers)
                    ->empty(tkey('crm.leads.empty.no_manager')),
                Select::make('task.priority')
                    ->title(tkey('crm.tasks.fields.priority'))
                    ->options($this->taskPriorityOptions()),
                Input::make('task.due_at')
                    ->title(tkey('crm.tasks.fields.due_at'))
                    ->type('datetime-local'),
                Button::make(tkey('crm.leads.actions.create_task'))
                    ->icon('bs.check2-square')
                    ->method('createTask')
                    ->canSee($this->lead?->exists ?? false),
            ])->title(tkey('crm.leads.sections.tasks')),

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
                TD::make('actions', tkey('crm.leads.columns.actions'))
                    ->alignRight()
                    ->render(fn (MarketingLeadTask $task): string => collect([
                        (string) Button::make(tkey('crm.tasks.actions.complete'))
                            ->icon('bs.check2')
                            ->method('completeTask')
                            ->parameters(['task' => $task->id])
                            ->canSee($task->completed_at === null && $task->cancelled_at === null),
                        (string) Button::make(tkey('crm.leads.actions.cancel_task'))
                            ->icon('bs.x-circle')
                            ->method('cancelTask')
                            ->parameters(['task' => $task->id])
                            ->canSee($task->completed_at === null && $task->cancelled_at === null),
                    ])->join(' ')),
            ])->title(tkey('crm.leads.sections.tasks')),

            Layout::table('lead.activities', [
                TD::make('created_at', tkey('crm.leads.columns.created_at'))
                    ->render(fn (MarketingLeadActivity $activity): string => $activity->created_at->format('Y-m-d H:i')),
                TD::make('type', tkey('crm.activities.fields.type'))
                    ->render(fn (MarketingLeadActivity $activity): string => $activity->typeLabel()),
                TD::make('user', tkey('crm.leads.columns.user'))
                    ->render(fn (MarketingLeadActivity $activity): string => $activity->user?->name ?? tkey('common.system')),
                TD::make('body', tkey('crm.activities.fields.body'))
                    ->render(fn (MarketingLeadActivity $activity): string => $activity->body ?? $activity->title ?? '-'),
                TD::make('change', tkey('crm.activities.fields.change'))
                    ->render(fn (MarketingLeadActivity $activity): string => collect([$activity->old_value, $activity->new_value])->filter()->join(' -> ') ?: '-'),
                TD::make('meta', tkey('crm.activities.fields.meta'))
                    ->render(fn (MarketingLeadActivity $activity): string => $this->activityMeta($activity)),
            ])->title(tkey('crm.leads.sections.activity_timeline')),

            Layout::table('lead.duplicates', [
                TD::make('id', tkey('crm.leads.columns.id'))
                    ->render(fn (MarketingLead $duplicate): string => (string) $duplicate->id),
                TD::make('name', tkey('crm.leads.columns.full_name'))
                    ->render(fn (MarketingLead $duplicate): string => (string) Link::make($duplicate->fullName())
                        ->route($this->leadEditRoute(), $duplicate)),
                TD::make('phone', tkey('crm.leads.columns.phone'))
                    ->render(fn (MarketingLead $duplicate): string => $duplicate->phone ?? '-'),
                TD::make('status', tkey('crm.leads.columns.status'))
                    ->render(fn (MarketingLead $duplicate): string => $duplicate->status->label()),
            ])->title(tkey('crm.leads.sections.duplicates')),

            Layout::rows([
                Select::make('lost.reason')
                    ->title(tkey('crm.leads.fields.lost_reason'))
                    ->options($this->lostReasons)
                    ->empty(tkey('crm.leads.empty.no_lost_reason')),
                TextArea::make('lost.comment')
                    ->title(tkey('crm.leads.fields.lost_comment'))
                    ->rows(3),
                Button::make(tkey('crm.leads.actions.mark_lost'))
                    ->icon('bs.x-octagon')
                    ->method('markLost')
                    ->canSee($this->lead?->exists ?? false),
            ])->title(tkey('crm.leads.sections.lost')),

            Layout::rows([
                Input::make('duplicate.original_id')
                    ->title(tkey('crm.leads.fields.duplicate_of'))
                    ->type('number')
                    ->min(1),
                TextArea::make('duplicate.comment')
                    ->title(tkey('crm.leads.fields.comment'))
                    ->rows(3),
                Button::make(tkey('crm.leads.actions.mark_duplicate'))
                    ->icon('bs.files')
                    ->method('markDuplicate')
                    ->canSee($this->lead?->exists ?? false),
                Button::make(tkey('crm.leads.actions.mark_spam'))
                    ->icon('bs.exclamation-octagon')
                    ->method('markSpam')
                    ->canSee($this->lead?->exists ?? false),
            ])->title(tkey('crm.leads.sections.duplicates')),

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

            Layout::modal('changeStatusModal', Layout::rows([
                Select::make('status')
                    ->title(tkey('crm.leads.fields.status'))
                    ->options($this->leadStatusOptions())
                    ->required(),
                Select::make('lost_reason_code')
                    ->title(tkey('crm.leads.fields.lost_reason'))
                    ->options($this->lostReasons)
                    ->empty(tkey('crm.leads.empty.no_lost_reason')),
                TextArea::make('reason')
                    ->title(tkey('crm.leads.fields.comment'))
                    ->rows(3),
            ]))
                ->title(tkey('crm.leads.actions.change_status'))
                ->method('changeStatus')
                ->applyButton(tkey('crm.leads.actions.change_status'))
                ->canSee($this->lead?->exists ?? false),

            Layout::modal('assignManagerModal', Layout::rows([
                Select::make('manager_id')
                    ->title(tkey('crm.leads.fields.manager'))
                    ->options($this->managers)
                    ->empty(tkey('crm.leads.empty.no_manager')),
            ]))
                ->title(tkey('crm.leads.actions.assign_manager'))
                ->method('assignManager')
                ->applyButton(tkey('crm.leads.actions.assign_manager'))
                ->canSee($this->lead?->exists ?? false),

            Layout::modal('addNoteModal', Layout::rows([
                TextArea::make('comment.body')
                    ->title(tkey('crm.leads.fields.comment'))
                    ->rows(4)
                    ->required(),
            ]))
                ->title(tkey('crm.leads.actions.add_note'))
                ->method('addNote')
                ->applyButton(tkey('crm.leads.actions.add_note'))
                ->canSee($this->lead?->exists ?? false),

            Layout::modal('logCallModal', Layout::rows([
                Select::make('call.result')
                    ->title(tkey('crm.calls.fields.result'))
                    ->options($this->callResults())
                    ->required(),
                Input::make('call.duration_seconds')
                    ->title(tkey('crm.calls.fields.duration_seconds'))
                    ->type('number')
                    ->min(0),
                TextArea::make('call.comment')
                    ->title(tkey('crm.calls.fields.comment'))
                    ->rows(3),
                Input::make('call.next_follow_up_at')
                    ->title(tkey('crm.calls.fields.next_follow_up_at'))
                    ->type('datetime-local'),
                Select::make('call.lost_reason_code')
                    ->title(tkey('crm.leads.fields.lost_reason'))
                    ->options($this->lostReasons)
                    ->empty(tkey('crm.leads.empty.no_lost_reason')),
            ]))
                ->title(tkey('crm.leads.actions.log_call'))
                ->method('logCall')
                ->applyButton(tkey('crm.leads.actions.log_call'))
                ->canSee($this->lead?->exists ?? false),

            Layout::modal('createTaskModal', Layout::rows([
                Input::make('task.title')
                    ->title(tkey('crm.tasks.fields.title'))
                    ->required(),
                TextArea::make('task.notes')
                    ->title(tkey('crm.tasks.fields.description'))
                    ->rows(3),
                Select::make('task.assigned_to_user_id')
                    ->title(tkey('crm.tasks.fields.assigned_to'))
                    ->options($this->managers)
                    ->empty(tkey('crm.leads.empty.no_manager')),
                Select::make('task.priority')
                    ->title(tkey('crm.tasks.fields.priority'))
                    ->options($this->taskPriorityOptions()),
                Input::make('task.due_at')
                    ->title(tkey('crm.tasks.fields.due_at'))
                    ->type('datetime-local'),
            ]))
                ->title(tkey('crm.leads.actions.create_task'))
                ->method('createTask')
                ->applyButton(tkey('crm.leads.actions.create_task'))
                ->canSee($this->lead?->exists ?? false),

            Layout::modal('markLostModal', Layout::rows([
                Select::make('lost.reason')
                    ->title(tkey('crm.leads.fields.lost_reason'))
                    ->options($this->lostReasons)
                    ->empty(tkey('crm.leads.empty.no_lost_reason'))
                    ->required(),
                TextArea::make('lost.comment')
                    ->title(tkey('crm.leads.fields.lost_comment'))
                    ->rows(3),
            ]))
                ->title(tkey('crm.leads.actions.mark_lost'))
                ->method('markLost')
                ->applyButton(tkey('crm.leads.actions.mark_lost'))
                ->canSee($this->lead?->exists ?? false),

            Layout::modal('markDuplicateModal', Layout::rows([
                Input::make('duplicate.original_id')
                    ->title(tkey('crm.leads.fields.duplicate_of'))
                    ->type('number')
                    ->min(1)
                    ->required(),
                TextArea::make('duplicate.comment')
                    ->title(tkey('crm.leads.fields.comment'))
                    ->rows(3),
            ]))
                ->title(tkey('crm.leads.actions.mark_duplicate'))
                ->method('markDuplicate')
                ->applyButton(tkey('crm.leads.actions.mark_duplicate'))
                ->canSee($this->lead?->exists ?? false),

            Layout::modal('reopenModal', Layout::rows([
                Select::make('status')
                    ->title(tkey('crm.leads.fields.status'))
                    ->options($this->openStatusOptions())
                    ->empty(tkey('crm.leads.filters.all_statuses')),
                TextArea::make('reason')
                    ->title(tkey('crm.leads.fields.comment'))
                    ->rows(3),
            ]))
                ->title(tkey('crm.leads.actions.reopen'))
                ->method('reopen')
                ->applyButton(tkey('crm.leads.actions.reopen'))
                ->canSee($this->lead?->exists ?? false),
        ];
    }

    public function save(
        LeadCrmRequest $request,
        SaveMarketingLeadCrmAction $saveLead,
    ): RedirectResponse {
        $lead = $this->persistLead($request, $saveLead);

        return redirect()->route($this->leadEditRoute(), $lead);
    }

    public function saveAndReturn(
        LeadCrmRequest $request,
        SaveMarketingLeadCrmAction $saveLead,
    ): RedirectResponse {
        $this->persistLead($request, $saveLead);

        return redirect()->route($this->leadIndexRoute());
    }

    public function addComment(MarketingLead $lead, LeadCommentRequest $request, AddLeadCommentAction $addComment): RedirectResponse
    {
        $addComment->handle($lead, $request->user(), $request->body());

        Toast::info(tkey('crm.leads.messages.comment_added'));

        return redirect()->route($this->leadEditRoute(), $lead);
    }

    public function addCommunication(MarketingLead $lead, LeadCommunicationRequest $request, AddLeadCommunicationAction $addCommunication): RedirectResponse
    {
        $payload = $request->communicationData();

        $addCommunication->handle(
            $lead,
            $request->user(),
            $payload['channel'],
            $payload['direction'],
            $payload['subject'] ?? null,
            $payload['body'] ?? null,
            null,
            $request->template(),
            (bool) ($payload['client_replied'] ?? false),
            (bool) ($payload['callback_required'] ?? false),
            $request->callbackRequiredAt(),
            $payload['call_recording_url'] ?? null,
            $payload['call_recording_reference'] ?? null,
            $payload['call_result'] ?? null,
            $request->durationSeconds(),
        );

        Toast::info(tkey('crm.leads.messages.communication_added'));

        return redirect()->route($this->leadEditRoute(), $lead);
    }

    public function createTask(MarketingLead $lead, StoreLeadTaskRequest $request, CreateLeadTaskAction $createTask): RedirectResponse
    {
        $payload = $request->taskData();
        $createTask->handle(
            $lead,
            $request->user(),
            $payload['title'],
            $request->dueAt(),
            $request->priority(),
            $payload['notes'] ?? null,
            $request->assignedToUserId(),
        );

        Toast::info(tkey('crm.tasks.messages.created'));

        return redirect()->route($this->leadEditRoute(), $lead);
    }

    public function completeTask(MarketingLead $lead, LeadTaskCompletionRequest $request, CompleteLeadTaskAction $completeTask): RedirectResponse
    {
        $task = $lead->tasks()->findOrFail($request->taskId());

        $completeTask->handle($task, $request->user());

        Toast::info(tkey('crm.tasks.messages.completed'));

        return redirect()->route($this->leadEditRoute(), $lead);
    }

    public function cancelTask(MarketingLead $lead, CancelLeadTaskRequest $request, CancelLeadTaskAction $cancelTask): RedirectResponse
    {
        $task = $lead->tasks()->findOrFail($request->taskId());

        $cancelTask->handle($task, $request->user(), $request->reason());

        Toast::info(tkey('crm.leads.messages.task_cancelled'));

        return redirect()->route($this->leadEditRoute(), $lead);
    }

    public function markLost(MarketingLead $lead, MarkLeadLostRequest $request, MarkLeadLostAction $markLeadLost): RedirectResponse
    {
        $markLeadLost->handle($lead, $request->reason(), $request->comment(), $request->user());

        Toast::info(tkey('crm.leads.messages.marked_lost'));

        return redirect()->route($this->leadEditRoute(), $lead);
    }

    public function markDuplicate(MarketingLead $lead, MarkLeadDuplicateRequest $request, MarkLeadDuplicateAction $markLeadDuplicate): RedirectResponse
    {
        $markLeadDuplicate->handle($lead, $request->originalId(), $request->comment(), $request->user());

        Toast::info(tkey('crm.leads.messages.marked_duplicate'));

        return redirect()->route($this->leadEditRoute(), $lead);
    }

    public function markSpam(MarketingLead $lead, MarkLeadSpamRequest $request, MarkLeadSpamAction $markLeadSpam): RedirectResponse
    {
        $markLeadSpam->handle($lead, $request->user(), tkey('crm.activities.reasons.marked_spam'));

        Toast::info(tkey('crm.leads.messages.marked_spam'));

        return redirect()->route($this->leadEditRoute(), $lead);
    }

    public function prepareEnrollment(
        MarketingLead $lead,
        LeadStatusActionRequest $request,
        PrepareLeadForEnrollmentAction $prepareLead,
    ): RedirectResponse {
        $prepareLead->handle($lead, $request->user());

        Toast::info(tkey('crm.leads.messages.student_module_next_block'));

        return redirect()->route($this->leadEditRoute(), $lead);
    }

    public function changeStatus(
        MarketingLead $lead,
        ChangeLeadStatusRequest $request,
        ChangeLeadStatusAction $changeLeadStatus,
    ): RedirectResponse {
        $changeLeadStatus->handle(
            $lead,
            $request->status(),
            $request->user(),
            $request->reason(),
            $request->lostReasonCode(),
        );

        Toast::info(tkey('crm.leads.messages.status_changed'));

        return redirect()->route($this->leadEditRoute(), $lead);
    }

    public function assignManager(
        MarketingLead $lead,
        AssignLeadManagerRequest $request,
        AssignLeadManagerAction $assignLeadManager,
    ): RedirectResponse {
        $manager = $request->managerId() === null
            ? null
            : User::query()->findOrFail($request->managerId());

        $assignLeadManager->handle($lead, $manager, $request->user());

        Toast::info(tkey('crm.leads.messages.manager_assigned'));

        return redirect()->route($this->leadEditRoute(), $lead);
    }

    public function addNote(MarketingLead $lead, AddLeadNoteRequest $request, AddLeadNoteAction $addLeadNote): RedirectResponse
    {
        $addLeadNote->handle($lead, $request->user(), $request->body());

        Toast::info(tkey('crm.leads.messages.note_added'));

        return redirect()->route($this->leadEditRoute(), $lead);
    }

    public function logCall(MarketingLead $lead, LogLeadCallRequest $request, LogLeadCallAction $logLeadCall): RedirectResponse
    {
        $payload = $request->callData();

        $logLeadCall->handle(
            $lead,
            $request->user(),
            $payload['result'],
            $payload['duration_seconds'] ?? null,
            $payload['comment'] ?? null,
            $request->nextFollowUpAt(),
            $payload['lost_reason_code'] ?? null,
        );

        Toast::info(tkey('crm.leads.messages.call_logged'));

        return redirect()->route($this->leadEditRoute(), $lead);
    }

    public function reopen(MarketingLead $lead, ReopenLeadRequest $request, ReopenLeadAction $reopenLead): RedirectResponse
    {
        $reopenLead->handle($lead, $request->user(), $request->status(), $request->reason());

        Toast::info(tkey('crm.leads.messages.reopened'));

        return redirect()->route($this->leadEditRoute(), $lead);
    }

    public function prepareConversion(
        MarketingLead $lead,
        LeadStatusActionRequest $request,
        PrepareLeadForStudentConversionAction $prepareLead,
    ): RedirectResponse {
        $result = $prepareLead->handle($lead, $request->user());

        Toast::info($result['message']);

        return redirect()->route($this->leadEditRoute(), $lead);
    }

    public function convertToStudent(MarketingLead $lead, LeadStatusActionRequest $request): RedirectResponse
    {
        Toast::info(tkey('crm.leads.messages.student_module_next_block'));

        return redirect()->route($this->leadEditRoute(), $lead);
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
    private function openStatusOptions(): array
    {
        return collect(LeadStatus::cases())
            ->reject(fn (LeadStatus $status): bool => $status->isFinal())
            ->mapWithKeys(fn (LeadStatus $status): array => [
                $status->value => $status->label(),
            ])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    private function leadPriorityOptions(): array
    {
        return [
            'low' => tkey('crm.tasks.priorities.low'),
            'normal' => tkey('crm.tasks.priorities.normal'),
            'high' => tkey('crm.tasks.priorities.high'),
            'urgent' => tkey('crm.tasks.priorities.urgent'),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function taskPriorityOptions(): array
    {
        return $this->leadPriorityOptions();
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

    /**
     * @return array<string, string>
     */
    private function callResults(): array
    {
        return MarketingLeadCommunication::callResultOptions();
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

    private function activityMeta(MarketingLeadActivity $activity): string
    {
        return collect($activity->meta ?? [])
            ->filter(fn (mixed $value): bool => is_scalar($value) && filled((string) $value))
            ->map(fn (mixed $value, string $key): string => tkey('crm.activities.meta.'.$key).': '.$this->metaValue($value))
            ->join(' / ') ?: '-';
    }

    private function metaValue(mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? tkey('common.status.yes') : tkey('common.status.no');
        }

        return (string) $value;
    }

    private function persistLead(LeadCrmRequest $request, SaveMarketingLeadCrmAction $saveLead): MarketingLead
    {
        $isNew = $request->leadId() === null;
        $lead = $isNew
            ? null
            : MarketingLead::query()
                ->forCrmDetail()
                ->whereKey($request->leadId())
                ->firstOrFail();

        $lead = $saveLead->handle(
            $lead,
            $request->leadData(),
            $request->targetStatus(),
            $request->user(),
            $request->budgetEur(),
            $request->tagIds(),
        );

        Toast::info($isNew ? tkey('crm.leads.messages.created') : tkey('crm.leads.messages.updated'));

        return $lead;
    }

    private function leadIndexRoute(): string
    {
        return request()->routeIs('platform.marketing.*')
            ? 'platform.marketing.leads'
            : 'platform.crm.leads';
    }

    private function leadEditRoute(): string
    {
        return request()->routeIs('platform.marketing.*')
            ? 'platform.marketing.leads.edit'
            : 'platform.crm.leads.edit';
    }

    private function canViewMarketing(): bool
    {
        return request()->user()?->hasAccess('crm.leads.view_marketing') ?? false;
    }

    private function canConvertToStudent(): bool
    {
        return request()->user()?->hasAnyAccess(['crm.leads.convert', 'students.convert_from_lead']) ?? false;
    }

    private function hasCrmAccess(string $permission): bool
    {
        return request()->user()?->hasAnyAccess([$permission, 'platform.marketing.leads']) ?? false;
    }
}
