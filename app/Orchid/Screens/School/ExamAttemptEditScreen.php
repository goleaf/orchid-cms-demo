<?php

namespace App\Orchid\Screens\School;

use App\Actions\CancelExamAttemptAction;
use App\Actions\CompleteExamAttemptAction;
use App\Actions\CreateExamRetakeAction;
use App\Actions\MarkExamAttemptNoShowAction;
use App\Actions\MarkExamFailedAction;
use App\Actions\MarkExamPassedAction;
use App\Actions\RecordExamResultAction;
use App\Actions\StartExamAttemptAction;
use App\Models\ExamActivity;
use App\Models\ExamAttempt;
use App\Models\ExamResult;
use App\Models\ExamResultStatus;
use App\Orchid\Screens\School\Concerns\InteractsWithExamScreens;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class ExamAttemptEditScreen extends Screen
{
    use InteractsWithExamScreens;

    public ?ExamAttempt $attempt = null;

    /**
     * @var array<int, string>
     */
    private array $resultStatuses = [];

    /**
     * @var array<int, string>
     */
    private array $sessions = [];

    public function query(ExamAttempt $examAttempt): iterable
    {
        $this->attempt = $examAttempt->loadMissing([
            'session.typeRecord:id,code,name,name_translations',
            'session.statusRecord:id,code,name,name_translations',
            'student:id,first_name,last_name,full_name,student_number',
            'enrollment:id,enrollment_number,student_profile_id,training_program_id',
            'statusRecord:id,code,name,name_translations',
            'result.resultStatus:id,code,name,name_translations',
            'result.decidedBy:id,name',
            'activities.user:id,name',
            'previousRetake',
            'newRetake',
        ]);
        $this->resultStatuses = $this->resultStatusOptions();
        $this->sessions = $this->sessionOptions();

        return [
            'attempt' => $this->attempt,
            'attempt_id' => $this->attempt->id,
            'result_status_id' => $this->attempt->result?->result_status_id,
            'score' => $this->attempt->score,
            'max_score' => $this->attempt->max_score ?? 100,
            'passed' => $this->attempt->passed ? '1' : '0',
            'results' => $this->attempt->result === null ? collect() : collect([$this->attempt->result]),
            'activities' => $this->attempt->activities ?? collect(),
        ];
    }

    public function name(): ?string
    {
        return tkey('exams.attempts.edit_title');
    }

    public function description(): ?string
    {
        return tkey('exams.attempts.title');
    }

    public function permission(): iterable
    {
        return ['exams.attempts.view'];
    }

    public function commandBar(): iterable
    {
        return [
            Link::make(tkey('common.actions.back'))
                ->icon('bs.arrow-left')
                ->route('platform.exams.attempts'),

            Button::make(tkey('exams.actions.start_attempt'))
                ->icon('bs.play-circle')
                ->method('start')
                ->canSee(request()->user()?->hasAccess('exams.attempts.start') ?? false),

            ModalToggle::make(tkey('exams.actions.complete_attempt'))
                ->icon('bs.check2-circle')
                ->modal('completeAttemptModal')
                ->method('complete')
                ->canSee(request()->user()?->hasAccess('exams.attempts.complete') ?? false),

            Button::make(tkey('exams.actions.no_show'))
                ->icon('bs.person-x')
                ->method('noShow')
                ->confirm(tkey('exams.messages.no_show_confirm'))
                ->canSee(request()->user()?->hasAccess('exams.attempts.complete') ?? false),

            Button::make(tkey('exams.actions.cancel'))
                ->icon('bs.x-circle')
                ->method('cancel')
                ->confirm(tkey('exams.messages.cancel_confirm'))
                ->canSee(request()->user()?->hasAccess('exams.attempts.cancel') ?? false),

            ModalToggle::make(tkey('exams.actions.record_result'))
                ->icon('bs.clipboard2-check')
                ->modal('recordResultModal')
                ->method('recordResult')
                ->canSee(request()->user()?->hasAccess('exams.results.record') ?? false),

            Button::make(tkey('exams.actions.mark_passed'))
                ->icon('bs.patch-check')
                ->method('markPassed')
                ->canSee(request()->user()?->hasAccess('exams.results.update') ?? false),

            Button::make(tkey('exams.actions.mark_failed'))
                ->icon('bs.patch-exclamation')
                ->method('markFailed')
                ->canSee(request()->user()?->hasAccess('exams.results.update') ?? false),

            ModalToggle::make(tkey('exams.actions.create_retake'))
                ->icon('bs.arrow-counterclockwise')
                ->modal('createRetakeModal')
                ->method('createRetake')
                ->canSee(request()->user()?->hasAccess('exams.retakes.create') ?? false),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::rows([
                Input::make('attempt_id')->type('hidden'),
                Input::make('attempt_number_display')
                    ->title(tkey('exams.fields.exam_number'))
                    ->value((string) ($this->attempt?->attempt_number ?? $this->attempt?->id))
                    ->disabled(),
                Input::make('session_display')
                    ->title(tkey('exams.fields.session'))
                    ->value($this->attempt?->session?->exam_number ?? $this->dash())
                    ->disabled(),
                Input::make('student_display')
                    ->title(tkey('exams.fields.student'))
                    ->value($this->attempt?->student?->display_name ?? $this->dash())
                    ->disabled(),
                Input::make('enrollment_display')
                    ->title(tkey('exams.fields.enrollment'))
                    ->value($this->enrollmentLabel($this->attempt?->enrollment))
                    ->disabled(),
                Input::make('status_display')
                    ->title(tkey('exams.fields.status'))
                    ->value($this->attempt === null ? $this->dash() : $this->attemptStatusLabel($this->attempt))
                    ->disabled(),
                Input::make('score_display')
                    ->title(tkey('exams.fields.score'))
                    ->value($this->attempt?->score === null ? $this->dash() : (string) $this->attempt?->score)
                    ->disabled(),
                Input::make('passed_display')
                    ->title(tkey('exams.fields.passed'))
                    ->value($this->boolLabel($this->attempt?->passed))
                    ->disabled(),
            ])->title(tkey('exams.sections.overview')),

            Layout::table('results', [
                TD::make('result_status_id', tkey('exams.fields.result_status'))
                    ->render(fn (ExamResult $result): string => $this->resultStatusLabel($result)),
                TD::make('score', tkey('exams.fields.score'))
                    ->render(fn (ExamResult $result): string => trim((string) $result->score.'/'.$result->max_score, '/')),
                TD::make('passed', tkey('exams.fields.passed'))
                    ->render(fn (ExamResult $result): string => $this->boolLabel($result->passed)),
                TD::make('decided_by_id', tkey('exams.fields.decided_by'))
                    ->render(fn (ExamResult $result): string => $result->decidedBy?->name ?? $this->dash()),
                TD::make('decided_at', tkey('exams.fields.decided_at'))
                    ->render(fn (ExamResult $result): string => $this->dateTime($result->decided_at)),
            ])->title(tkey('exams.sections.results')),

            Layout::table('activities', [
                TD::make('created_at', tkey('exams.fields.created_at'))
                    ->render(fn (ExamActivity $activity): string => $this->dateTime($activity->created_at)),
                TD::make('type', tkey('exams.fields.type'))
                    ->render(fn (ExamActivity $activity): string => $this->activityTypeLabel($activity)),
                TD::make('user_id', tkey('exams.fields.user'))
                    ->render(fn (ExamActivity $activity): string => $activity->user?->name ?? $this->dash()),
                TD::make('new_value', tkey('exams.fields.new_value'))
                    ->render(fn (ExamActivity $activity): string => $activity->new_value ?? $this->dash()),
            ])->title(tkey('exams.sections.activities')),

            Layout::modal('completeAttemptModal', [
                Layout::rows([
                    Input::make('attempt_id')->type('hidden')->value($this->attempt?->id),
                    Input::make('score')
                        ->type('number')
                        ->step('0.01')
                        ->title(tkey('exams.fields.score')),
                    Input::make('max_score')
                        ->type('number')
                        ->step('0.01')
                        ->title(tkey('exams.fields.max_score')),
                    Select::make('passed')
                        ->title(tkey('exams.fields.passed'))
                        ->options($this->yesNoOptions())
                        ->required(),
                    TextArea::make('examiner_comment')
                        ->title(tkey('exams.fields.examiner_comment'))
                        ->rows(3),
                    TextArea::make('mistakes_summary')
                        ->title(tkey('exams.fields.mistakes_summary'))
                        ->rows(3),
                ]),
            ])
                ->title(tkey('exams.actions.complete_attempt'))
                ->applyButton(tkey('exams.actions.complete_attempt')),

            Layout::modal('recordResultModal', [
                Layout::rows([
                    Input::make('attempt_id')->type('hidden')->value($this->attempt?->id),
                    Select::make('result_status_id')
                        ->title(tkey('exams.fields.result_status'))
                        ->options($this->resultStatuses)
                        ->required(),
                    Input::make('score')
                        ->type('number')
                        ->step('0.01')
                        ->title(tkey('exams.fields.score')),
                    Input::make('max_score')
                        ->type('number')
                        ->step('0.01')
                        ->title(tkey('exams.fields.max_score')),
                    Select::make('passed')
                        ->title(tkey('exams.fields.passed'))
                        ->options($this->yesNoOptions())
                        ->required(),
                    TextArea::make('examiner_comment')
                        ->title(tkey('exams.fields.examiner_comment'))
                        ->rows(3),
                    TextArea::make('mistakes_summary')
                        ->title(tkey('exams.fields.mistakes_summary'))
                        ->rows(3),
                ]),
            ])
                ->title(tkey('exams.actions.record_result'))
                ->applyButton(tkey('exams.actions.record_result')),

            Layout::modal('createRetakeModal', [
                Layout::rows([
                    Input::make('attempt_id')->type('hidden')->value($this->attempt?->id),
                    Select::make('exam_session_id')
                        ->title(tkey('exams.fields.session'))
                        ->empty(tkey('exams.filters.no_session'), '')
                        ->options($this->sessions),
                    Input::make('planned_at')
                        ->type('datetime-local')
                        ->title(tkey('exams.fields.planned_at')),
                    TextArea::make('reason')
                        ->title(tkey('exams.fields.reason'))
                        ->rows(3),
                ]),
            ])
                ->title(tkey('exams.actions.create_retake'))
                ->applyButton(tkey('exams.actions.create_retake')),
        ];
    }

    public function start(Request $request, StartExamAttemptAction $startAttempt): RedirectResponse
    {
        abort_unless($request->user()?->hasAccess('exams.attempts.start'), 403);

        $attempt = ExamAttempt::query()->findOrFail($request->integer('attempt_id'));
        $startAttempt->handle($attempt, $request->user());

        Toast::info(tkey('exams.messages.attempt_started'));

        return redirect()->route('platform.exams.attempts.edit', $attempt);
    }

    public function complete(Request $request, CompleteExamAttemptAction $completeAttempt): RedirectResponse
    {
        abort_unless($request->user()?->hasAccess('exams.attempts.complete'), 403);

        $attempt = ExamAttempt::query()->findOrFail($request->integer('attempt_id'));
        $completeAttempt->handle($attempt, $this->resultData($request), $request->user());

        Toast::info(tkey('exams.messages.attempt_completed'));

        return redirect()->route('platform.exams.attempts.edit', $attempt);
    }

    public function noShow(Request $request, MarkExamAttemptNoShowAction $markNoShow): RedirectResponse
    {
        abort_unless($request->user()?->hasAccess('exams.attempts.complete'), 403);

        $attempt = ExamAttempt::query()->findOrFail($request->integer('attempt_id'));
        $markNoShow->handle($attempt, $request->user());

        Toast::info(tkey('exams.messages.attempt_no_show'));

        return redirect()->route('platform.exams.attempts.edit', $attempt);
    }

    public function cancel(Request $request, CancelExamAttemptAction $cancelAttempt): RedirectResponse
    {
        abort_unless($request->user()?->hasAccess('exams.attempts.cancel'), 403);

        $attempt = ExamAttempt::query()->findOrFail($request->integer('attempt_id'));
        $cancelAttempt->handle($attempt, $request->user(), $request->string('reason')->toString() ?: null);

        Toast::info(tkey('exams.messages.attempt_cancelled'));

        return redirect()->route('platform.exams.attempts.edit', $attempt);
    }

    public function recordResult(Request $request, RecordExamResultAction $recordResult): RedirectResponse
    {
        abort_unless($request->user()?->hasAccess('exams.results.record'), 403);

        $attempt = ExamAttempt::query()->findOrFail($request->integer('attempt_id'));
        $recordResult->handle($attempt, $this->resultData($request), $request->user());

        Toast::info(tkey('exams.messages.attempt_recorded'));

        return redirect()->route('platform.exams.attempts.edit', $attempt);
    }

    public function markPassed(Request $request, MarkExamPassedAction $markPassed): RedirectResponse
    {
        abort_unless($request->user()?->hasAccess('exams.results.update'), 403);

        $attempt = ExamAttempt::query()->findOrFail($request->integer('attempt_id'));
        $markPassed->handle($attempt, [], $request->user());

        Toast::info(tkey('exams.messages.attempt_recorded'));

        return redirect()->route('platform.exams.attempts.edit', $attempt);
    }

    public function markFailed(Request $request, MarkExamFailedAction $markFailed): RedirectResponse
    {
        abort_unless($request->user()?->hasAccess('exams.results.update'), 403);

        $attempt = ExamAttempt::query()->findOrFail($request->integer('attempt_id'));
        $markFailed->handle($attempt, [], $request->user());

        Toast::info(tkey('exams.messages.attempt_recorded'));

        return redirect()->route('platform.exams.attempts.edit', $attempt);
    }

    public function createRetake(Request $request, CreateExamRetakeAction $createRetake): RedirectResponse
    {
        abort_unless($request->user()?->hasAccess('exams.retakes.create'), 403);

        $attempt = ExamAttempt::query()->findOrFail($request->integer('attempt_id'));
        $createRetake->handle($attempt, $request->validate([
            'exam_session_id' => ['nullable', 'integer', Rule::exists(\App\Models\ExamSession::class, 'id')],
            'planned_at' => ['nullable', 'date'],
            'reason' => ['nullable', 'string', 'max:2000'],
        ], $this->validationMessages()), $request->user());

        Toast::info(tkey('exams.messages.retake_scheduled'));

        return redirect()->route('platform.exams.attempts.edit', $attempt);
    }

    /**
     * @return array<string, mixed>
     */
    private function resultData(Request $request): array
    {
        return $request->validate([
            'attempt_id' => ['required', 'integer', Rule::exists(ExamAttempt::class, 'id')],
            'result_status_id' => ['nullable', 'integer', Rule::exists(ExamResultStatus::class, 'id')],
            'score' => ['nullable', 'numeric', 'min:0'],
            'max_score' => ['nullable', 'numeric', 'min:0'],
            'passed' => ['required', 'boolean'],
            'examiner_comment' => ['nullable', 'string', 'max:2000'],
            'mistakes_summary' => ['nullable', 'string', 'max:2000'],
        ], $this->validationMessages());
    }

    /**
     * @return array<string, string>
     */
    private function validationMessages(): array
    {
        return [
            'required' => tkey('exams.validation.required'),
            'integer' => tkey('exams.validation.integer'),
            'numeric' => tkey('exams.validation.numeric'),
            'boolean' => tkey('exams.validation.boolean'),
            'date' => tkey('exams.validation.date'),
            'string' => tkey('exams.validation.string'),
            'exists' => tkey('exams.validation.exists'),
            'min' => tkey('exams.validation.min'),
            'max' => tkey('exams.validation.max'),
        ];
    }
}
