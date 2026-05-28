<?php

namespace App\Orchid\Screens\School;

use App\Models\ExamAttempt;
use App\Orchid\Screens\School\Concerns\InteractsWithExamScreens;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class ExamAttemptListScreen extends Screen
{
    use InteractsWithExamScreens;

    /**
     * @var array<string, mixed>
     */
    private array $filters = [];

    /**
     * @var array<int, string>
     */
    private array $statuses = [];

    public function query(Request $request): iterable
    {
        $this->filters = collect($request->only(['status_id', 'student_id']))
            ->filter(fn (mixed $value): bool => filled($value))
            ->all();
        $this->statuses = $this->attemptStatusOptions(false);

        return [
            'attempts' => ExamAttempt::query()
                ->select([
                    'id',
                    'exam_session_id',
                    'enrollment_id',
                    'student_id',
                    'student_profile_id',
                    'status_id',
                    'attempt_number',
                    'attempt_no',
                    'score',
                    'max_score',
                    'passed',
                    'no_show',
                    'status',
                    'created_at',
                ])
                ->with([
                    'session:id,exam_number,type_id,status_id,scheduled_at',
                    'session.typeRecord:id,code,name,name_translations',
                    'student:id,first_name,last_name,full_name,student_number',
                    'enrollment:id,enrollment_number,student_profile_id,training_program_id',
                    'statusRecord:id,code,name,name_translations',
                ])
                ->when(filled($this->filters['status_id'] ?? null), fn (Builder $query): Builder => $query->where('status_id', $this->filters['status_id']))
                ->when(filled($this->filters['student_id'] ?? null), fn (Builder $query): Builder => $query->where(function (Builder $studentQuery): void {
                    $studentQuery->where('student_id', $this->filters['student_id'])
                        ->orWhere('student_profile_id', $this->filters['student_id']);
                }))
                ->orderByDesc('created_at')
                ->simplePaginate(20)
                ->withQueryString(),
        ];
    }

    public function name(): ?string
    {
        return tkey('exams.attempts.title');
    }

    public function description(): ?string
    {
        return tkey('operations.exams.description');
    }

    public function permission(): iterable
    {
        return ['exams.attempts.view'];
    }

    public function commandBar(): iterable
    {
        return [];
    }

    public function layout(): iterable
    {
        return [
            Layout::rows([
                Select::make('status_id')
                    ->title(tkey('exams.fields.status'))
                    ->empty(tkey('exams.filters.all_statuses'), '')
                    ->options($this->statuses)
                    ->value($this->filters['status_id'] ?? ''),
                Input::make('student_id')
                    ->type('number')
                    ->title(tkey('exams.fields.student'))
                    ->value($this->filters['student_id'] ?? ''),
            ])->title(tkey('exams.sections.filters')),

            Layout::table('attempts', [
                TD::make('attempt_number', tkey('exams.fields.exam_number'))
                    ->render(fn (ExamAttempt $attempt): string => (string) Link::make((string) ($attempt->attempt_number ?? $attempt->id))
                        ->route('platform.exams.attempts.edit', $attempt)),
                TD::make('session', tkey('exams.fields.session'))
                    ->render(fn (ExamAttempt $attempt): string => $attempt->session?->exam_number ?? $this->dash()),
                TD::make('student_id', tkey('exams.fields.student'))
                    ->render(fn (ExamAttempt $attempt): string => $attempt->student?->display_name ?? $this->dash()),
                TD::make('enrollment_id', tkey('exams.fields.enrollment'))
                    ->render(fn (ExamAttempt $attempt): string => $this->enrollmentLabel($attempt->enrollment)),
                TD::make('status_id', tkey('exams.fields.status'))
                    ->render(fn (ExamAttempt $attempt): string => $this->attemptStatusLabel($attempt)),
                TD::make('attempt_no', tkey('exams.fields.attempt_no'))
                    ->alignCenter()
                    ->render(fn (ExamAttempt $attempt): string => (string) ($attempt->attempt_no ?? $attempt->attempt_number ?? $this->dash())),
                TD::make('score', tkey('exams.fields.score'))
                    ->render(fn (ExamAttempt $attempt): string => $attempt->score === null ? $this->dash() : (string) $attempt->score),
                TD::make('passed', tkey('exams.fields.passed'))
                    ->render(fn (ExamAttempt $attempt): string => $this->boolLabel($attempt->passed)),
                TD::make('actions', tkey('exams.fields.actions'))
                    ->alignRight()
                    ->render(fn (ExamAttempt $attempt): string => (string) Link::make(tkey('exams.actions.open'))
                        ->icon('bs.box-arrow-up-right')
                        ->route('platform.exams.attempts.edit', $attempt)),
            ]),
        ];
    }
}
