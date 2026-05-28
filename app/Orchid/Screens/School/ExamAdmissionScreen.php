<?php

namespace App\Orchid\Screens\School;

use App\Actions\ApproveExamAdmissionAction;
use App\Actions\BlockExamAdmissionAction;
use App\Actions\CheckExamAdmissionAction;
use App\Models\ExamAdmission;
use App\Models\ExamAdmissionChecklistItem;
use App\Models\ExamType;
use App\Models\StudentEnrollment;
use App\Orchid\Screens\School\Concerns\InteractsWithExamScreens;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class ExamAdmissionScreen extends Screen
{
    use InteractsWithExamScreens;

    /**
     * @var array<int, string>
     */
    private array $types = [];

    /**
     * @var array<int, string>
     */
    private array $enrollments = [];

    public function query(): iterable
    {
        $this->types = $this->examTypeOptions();
        $this->enrollments = $this->enrollmentOptions();

        return [
            'admissions' => ExamAdmission::query()
                ->forExamDashboard()
                ->with([
                    'student:id,first_name,last_name,full_name,student_number',
                    'enrollment:id,enrollment_number,student_profile_id,training_program_id',
                    'group:id,group_number,name,name_translations',
                    'checklistItems',
                ])
                ->orderByDesc('created_at')
                ->simplePaginate(20)
                ->withQueryString(),
        ];
    }

    public function name(): ?string
    {
        return tkey('exams.admissions.title');
    }

    public function description(): ?string
    {
        return tkey('operations.exams.description');
    }

    public function permission(): iterable
    {
        return ['exams.admissions.check'];
    }

    public function commandBar(): iterable
    {
        return [
            ModalToggle::make(tkey('exams.actions.check_admission'))
                ->icon('bs.ui-checks')
                ->modal('checkAdmissionModal')
                ->method('checkAdmission')
                ->canSee(request()->user()?->hasAccess('exams.admissions.check') ?? false),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::table('admissions', [
                TD::make('student_profile_id', tkey('exams.fields.student'))
                    ->render(fn (ExamAdmission $admission): string => $admission->student?->display_name ?? $this->dash()),
                TD::make('enrollment_id', tkey('exams.fields.enrollment'))
                    ->render(fn (ExamAdmission $admission): string => $this->enrollmentLabel($admission->enrollment)),
                TD::make('admission_type', tkey('exams.fields.type'))
                    ->render(fn (ExamAdmission $admission): string => tkey('exams.types.'.$admission->admission_type->value)),
                TD::make('status', tkey('exams.fields.status'))
                    ->render(fn (ExamAdmission $admission): string => tkey('exams.admission_statuses.'.$admission->status->value)),
                TD::make('documents_status', tkey('exams.checklist.items.identity_document'))
                    ->render(fn (ExamAdmission $admission): string => $this->statusOrChecklist($admission, ['documents'], $admission->documents_status)),
                TD::make('payment_status', tkey('exams.checklist.items.payment_clearance'))
                    ->render(fn (ExamAdmission $admission): string => $this->statusOrChecklist($admission, ['payments'], $admission->payment_status)),
                TD::make('theory_hours', tkey('exams.checklist.items.theory_hours'))
                    ->render(fn (ExamAdmission $admission): string => $this->hoursLabel($admission->completed_theory_hours, $admission->required_theory_hours)),
                TD::make('practice_hours', tkey('exams.checklist.items.practice_hours'))
                    ->render(fn (ExamAdmission $admission): string => $this->hoursLabel($admission->completed_practice_hours, $admission->required_practice_hours)),
                TD::make('internal_exam', tkey('exams.checklist.items.internal_exam_passed'))
                    ->render(fn (ExamAdmission $admission): string => $this->statusOrChecklist($admission, ['internal_theory', 'internal_practical'], $this->dash())),
                TD::make('actions', tkey('exams.fields.actions'))
                    ->alignRight()
                    ->render(fn (ExamAdmission $admission): DropDown => DropDown::make()
                        ->icon('bs.three-dots-vertical')
                        ->list([
                            Button::make(tkey('exams.actions.approve_admission'))
                                ->icon('bs.check-circle')
                                ->method('approve')
                                ->parameters(['exam_admission_id' => $admission->id])
                                ->canSee(request()->user()?->hasAccess('exams.admissions.approve') ?? false),
                            Button::make(tkey('exams.actions.block_admission'))
                                ->icon('bs.slash-circle')
                                ->method('block')
                                ->parameters(['exam_admission_id' => $admission->id])
                                ->confirm(tkey('exams.messages.block_confirm'))
                                ->canSee(request()->user()?->hasAccess('exams.admissions.block') ?? false),
                        ])),
            ]),

            Layout::modal('checkAdmissionModal', [
                Layout::rows([
                    Select::make('enrollment_id')
                        ->title(tkey('exams.fields.enrollment'))
                        ->options($this->enrollments)
                        ->required(),
                    Select::make('exam_type_id')
                        ->title(tkey('exams.fields.type'))
                        ->options($this->types)
                        ->required(),
                ]),
            ])
                ->title(tkey('exams.actions.check_admission'))
                ->applyButton(tkey('exams.actions.check_admission')),
        ];
    }

    public function checkAdmission(Request $request, CheckExamAdmissionAction $checkAdmission): RedirectResponse
    {
        abort_unless($request->user()?->hasAccess('exams.admissions.check'), 403);

        $data = $request->validate([
            'enrollment_id' => ['required', 'integer', Rule::exists(StudentEnrollment::class, 'id')],
            'exam_type_id' => ['required', 'integer', Rule::exists(ExamType::class, 'id')],
        ], $this->validationMessages());

        $enrollment = StudentEnrollment::query()->findOrFail($data['enrollment_id']);
        $checkAdmission->handle($enrollment, (int) $data['exam_type_id'], [], $request->user());

        Toast::info(tkey('exams.messages.admission_saved'));

        return redirect()->route('platform.exams.admissions');
    }

    public function approve(Request $request, ApproveExamAdmissionAction $approveAdmission): RedirectResponse
    {
        abort_unless($request->user()?->hasAccess('exams.admissions.approve'), 403);

        $admission = ExamAdmission::query()->findOrFail($request->integer('exam_admission_id'));
        $approveAdmission->handle($admission, $request->user());

        Toast::info(tkey('exams.messages.admission_approved'));

        return redirect()->route('platform.exams.admissions');
    }

    public function block(Request $request, BlockExamAdmissionAction $blockAdmission): RedirectResponse
    {
        abort_unless($request->user()?->hasAccess('exams.admissions.block'), 403);

        $admission = ExamAdmission::query()->findOrFail($request->integer('exam_admission_id'));
        $blockAdmission->handle($admission, $request->input('reason'), $request->user());

        Toast::info(tkey('exams.messages.admission_blocked'));

        return redirect()->route('platform.exams.admissions');
    }

    /**
     * @param  array<int, string>  $codes
     */
    private function statusOrChecklist(ExamAdmission $admission, array $codes, ?string $fallback): string
    {
        $items = $admission->checklistItems
            ->filter(fn (ExamAdmissionChecklistItem $item): bool => in_array($item->key ?: $item->code, $codes, true));

        if ($items->isEmpty()) {
            return filled($fallback) ? (string) $fallback : $this->dash();
        }

        return $items
            ->map(fn (ExamAdmissionChecklistItem $item): string => $item->displayTitle().': '.tkey('exams.checklist.statuses.'.$item->status->value))
            ->implode(', ');
    }

    private function hoursLabel(mixed $completed, mixed $required): string
    {
        return (string) $completed.'/'.$required;
    }

    /**
     * @return array<string, string>
     */
    private function validationMessages(): array
    {
        return [
            'required' => tkey('exams.validation.required'),
            'integer' => tkey('exams.validation.integer'),
            'exists' => tkey('exams.validation.exists'),
        ];
    }
}
