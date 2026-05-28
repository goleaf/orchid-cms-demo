<?php

declare(strict_types=1);

namespace App\Orchid\Screens\School;

use App\Actions\ConvertLeadToStudentAction;
use App\Actions\FindStudentMatchesForLeadAction;
use App\Actions\PrepareLeadConversionDataAction;
use App\Actions\ValidateLeadForStudentConversionAction;
use App\Http\Requests\Students\ConvertLeadToStudentRequest;
use App\Models\Branch;
use App\Models\MarketingLead;
use App\Models\Student;
use App\Models\TrainingGroup;
use App\Models\TrainingProgram;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
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

class LeadConvertToStudentScreen extends Screen
{
    public ?MarketingLead $lead = null;

    /**
     * @var array<string, mixed>
     */
    private array $validation = [];

    /**
     * @var Collection<int, array{student: Student, reason: string}>
     */
    private Collection $matches;

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

    public function query(MarketingLead $lead): iterable
    {
        $this->lead = MarketingLead::query()
            ->with([
                'branch:id,name,name_translations,city,city_translations',
                'trainingProgram:id,title,title_translations,license_category',
                'trainingGroup:id,name,name_translations,code,capacity',
                'tasks:id,marketing_lead_id,status,due_at',
            ])
            ->whereKey($lead->id)
            ->firstOrFail();

        $this->branches = Branch::query()
            ->forAdminList()
            ->orderBy('sort_order')
            ->orderBy('city')
            ->get()
            ->mapWithKeys(fn (Branch $branch): array => [$branch->id => $branch->displayName()])
            ->all();
        $this->programs = TrainingProgram::query()
            ->forAcademyList()
            ->orderBy('sort_order')
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

        $prepared = app(PrepareLeadConversionDataAction::class)->handle($this->lead);
        $this->validation = app(ValidateLeadForStudentConversionAction::class)->handle($this->lead, request()->user(), $prepared['enrollment']);
        $this->matches = app(FindStudentMatchesForLeadAction::class)->handle($this->lead);

        return array_merge([
            'lead' => $this->lead,
            'lead.full_name' => $this->lead->fullName(),
            'lead.status' => $this->lead->status->value,
            'matches' => $this->matches,
            'use_existing_student' => false,
            'existing_student_id' => null,
            'create_onboarding_tasks' => true,
            'create_document_placeholders' => true,
            'create_payment_placeholder' => true,
            'conversion_errors' => $this->localizedMessages($this->validation['blocking_errors'] ?? []),
            'conversion_warnings' => $this->localizedMessages($this->validation['warnings'] ?? []),
        ], [
            'student' => $prepared['student'],
            'enrollment' => $prepared['enrollment'],
        ]);
    }

    public function name(): ?string
    {
        return tkey('students.conversion.title');
    }

    public function description(): ?string
    {
        return tkey('students.conversion.description');
    }

    public function permission(): iterable
    {
        return ['students.convert_from_lead', 'crm.leads.convert'];
    }

    public function commandBar(): iterable
    {
        return [
            Link::make(tkey('students.conversion.actions.cancel'))
                ->icon('bs.arrow-left')
                ->route('platform.crm.leads.edit', $this->lead),

            Button::make(tkey('students.conversion.actions.convert'))
                ->icon('bs.person-plus')
                ->method('convert'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::rows([
                Input::make('lead.lead_number')
                    ->title(tkey('crm.leads.fields.lead_number'))
                    ->disabled(),
                Input::make('lead.full_name')
                    ->title(tkey('crm.leads.fields.full_name'))
                    ->disabled(),
                Input::make('lead.phone')
                    ->title(tkey('crm.leads.fields.phone'))
                    ->disabled(),
                Input::make('lead.email')
                    ->title(tkey('crm.leads.fields.email'))
                    ->disabled(),
                Input::make('lead.status')
                    ->title(tkey('crm.leads.fields.status'))
                    ->disabled(),
                Input::make('lead.source')
                    ->title(tkey('crm.leads.fields.source'))
                    ->disabled(),
                TextArea::make('conversion_errors')
                    ->title(tkey('students.conversion.fields.blocking_errors'))
                    ->rows(3)
                    ->disabled(),
                TextArea::make('conversion_warnings')
                    ->title(tkey('students.conversion.fields.warnings'))
                    ->rows(4)
                    ->disabled(),
            ])->title(tkey('students.conversion.steps.lead_check')),

            Layout::columns([
                Layout::rows([
                    CheckBox::make('use_existing_student')
                        ->sendTrueOrFalse()
                        ->title(tkey('students.conversion.fields.use_existing_student'))
                        ->placeholder(tkey('students.conversion.fields.use_existing_student')),
                    Select::make('existing_student_id')
                        ->title(tkey('students.conversion.fields.existing_student'))
                        ->options($this->matchingStudentOptions())
                        ->empty(tkey('students.conversion.fields.create_new_student')),
                ])->title(tkey('students.conversion.steps.matching_students')),

                Layout::table('matches', [
                    TD::make('student_number', tkey('students.fields.student_number'))
                        ->render(fn (array $match): string => $match['student']->student_number ?? (string) $match['student']->id),
                    TD::make('name', tkey('students.fields.full_name'))
                        ->render(fn (array $match): string => $match['student']->display_name),
                    TD::make('phone', tkey('students.fields.phone'))
                        ->render(fn (array $match): string => $match['student']->phone ?? '-'),
                    TD::make('email', tkey('students.fields.email'))
                        ->render(fn (array $match): string => $match['student']->email ?? '-'),
                    TD::make('reason', tkey('students.conversion.fields.match_reason'))
                        ->render(fn (array $match): string => tkey('students.conversion.match_reasons.'.$match['reason'])),
                ]),
            ]),

            Layout::columns([
                Layout::rows([
                    Input::make('student.full_name')
                        ->title(tkey('students.fields.full_name')),
                    Input::make('student.first_name')
                        ->title(tkey('students.fields.first_name')),
                    Input::make('student.last_name')
                        ->title(tkey('students.fields.last_name')),
                    Input::make('student.middle_name')
                        ->title(tkey('students.fields.middle_name')),
                    Input::make('student.phone')
                        ->title(tkey('students.fields.phone')),
                    Input::make('student.email')
                        ->type('email')
                        ->title(tkey('students.fields.email')),
                    Input::make('student.city')
                        ->title(tkey('students.fields.city')),
                    Input::make('student.locale')
                        ->title(tkey('students.fields.locale')),
                    Input::make('student.date_of_birth')
                        ->type('date')
                        ->title(tkey('students.fields.date_of_birth')),
                    Input::make('student.personal_code')
                        ->title(tkey('students.fields.personal_code')),
                ])->title(tkey('students.conversion.steps.student_data')),

                Layout::rows([
                    Select::make('enrollment.training_program_id')
                        ->title(tkey('students.enrollments.fields.course'))
                        ->options($this->programs)
                        ->empty(tkey('students.filters.all_courses')),
                    Select::make('enrollment.branch_id')
                        ->title(tkey('students.enrollments.fields.branch'))
                        ->options($this->branches)
                        ->empty(tkey('students.filters.all_branches')),
                    Select::make('enrollment.training_group_id')
                        ->title(tkey('students.enrollments.fields.training_group'))
                        ->options($this->groups)
                        ->empty(tkey('students.filters.all_groups')),
                    Input::make('enrollment.start_date')
                        ->title(tkey('students.enrollments.fields.start_date'))
                        ->type('date'),
                    Input::make('enrollment.planned_end_date')
                        ->title(tkey('students.enrollments.fields.planned_end_date'))
                        ->type('date'),
                    Input::make('enrollment.preferred_time')
                        ->title(tkey('students.enrollments.fields.preferred_time')),
                    Input::make('enrollment.training_language')
                        ->title(tkey('students.enrollments.fields.training_language')),
                    Input::make('enrollment.gearbox_type')
                        ->title(tkey('students.enrollments.fields.gearbox_type')),
                    Input::make('enrollment.price')
                        ->title(tkey('students.enrollments.fields.price'))
                        ->type('number')
                        ->step('0.01'),
                    Select::make('enrollment.payment_status')
                        ->title(tkey('students.enrollments.fields.payment_status'))
                        ->options($this->paymentStatusOptions())
                        ->empty(tkey('students.filters.no_segment')),
                ])->title(tkey('students.conversion.steps.enrollment_data')),
            ]),

            Layout::rows([
                CheckBox::make('create_onboarding_tasks')
                    ->sendTrueOrFalse()
                    ->title(tkey('students.conversion.fields.create_onboarding_tasks'))
                    ->placeholder(tkey('students.conversion.fields.create_onboarding_tasks')),
                CheckBox::make('create_document_placeholders')
                    ->sendTrueOrFalse()
                    ->title(tkey('students.conversion.fields.create_document_placeholders'))
                    ->placeholder(tkey('students.conversion.fields.create_document_placeholders')),
                CheckBox::make('create_payment_placeholder')
                    ->sendTrueOrFalse()
                    ->title(tkey('students.conversion.fields.create_payment_placeholder'))
                    ->placeholder(tkey('students.conversion.fields.create_payment_placeholder')),
            ])->title(tkey('students.conversion.steps.confirmation')),
        ];
    }

    public function convert(MarketingLead $lead, ConvertLeadToStudentRequest $request, ConvertLeadToStudentAction $convertLead): RedirectResponse
    {
        $result = $convertLead->handle(
            $lead,
            $request->existingStudentId(),
            $request->studentData(),
            $request->enrollmentData(),
            $request->boolean('create_onboarding_tasks', true),
            $request->boolean('create_document_placeholders', true),
            $request->boolean('create_payment_placeholder', true),
            $request->user(),
        );

        Toast::info(tkey('students.conversion.messages.converted'));

        return redirect()->route('platform.students.edit', $result['student']);
    }

    /**
     * @param  array<int, string>  $keys
     */
    private function localizedMessages(array $keys): string
    {
        return collect($keys)
            ->map(fn (string $key): string => tkey($key))
            ->join(PHP_EOL);
    }

    /**
     * @return array<int, string>
     */
    private function matchingStudentOptions(): array
    {
        return $this->matches
            ->mapWithKeys(fn (array $match): array => [$match['student']->id => $match['student']->display_name])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    private function paymentStatusOptions(): array
    {
        return collect(['not_required', 'pending', 'partially_paid', 'paid', 'overdue'])
            ->mapWithKeys(fn (string $status): array => [$status => tkey('students.payment_statuses.'.$status)])
            ->all();
    }
}
