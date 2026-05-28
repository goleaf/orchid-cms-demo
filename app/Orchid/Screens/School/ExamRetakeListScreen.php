<?php

namespace App\Orchid\Screens\School;

use App\Models\ExamRetake;
use App\Orchid\Screens\School\Concerns\InteractsWithExamScreens;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class ExamRetakeListScreen extends Screen
{
    use InteractsWithExamScreens;

    public function query(): iterable
    {
        return [
            'retakes' => ExamRetake::query()
                ->select([
                    'id',
                    'student_id',
                    'enrollment_id',
                    'previous_attempt_id',
                    'new_attempt_id',
                    'reason',
                    'planned_at',
                    'status',
                    'created_at',
                ])
                ->planned()
                ->with([
                    'student:id,first_name,last_name,full_name,student_number',
                    'enrollment:id,enrollment_number,student_profile_id,training_program_id',
                    'previousAttempt:id,attempt_no,attempt_number,exam_session_id',
                    'newAttempt:id,attempt_no,attempt_number,exam_session_id',
                ])
                ->orderBy('planned_at')
                ->simplePaginate(20)
                ->withQueryString(),
        ];
    }

    public function name(): ?string
    {
        return tkey('exams.retakes.title');
    }

    public function description(): ?string
    {
        return tkey('operations.exams.description');
    }

    public function permission(): iterable
    {
        return ['exams.retakes.view'];
    }

    public function commandBar(): iterable
    {
        return [];
    }

    public function layout(): iterable
    {
        return [
            Layout::table('retakes', [
                TD::make('student_id', tkey('exams.fields.student'))
                    ->render(fn (ExamRetake $retake): string => $retake->student?->display_name ?? $this->dash()),
                TD::make('enrollment_id', tkey('exams.fields.enrollment'))
                    ->render(fn (ExamRetake $retake): string => $this->enrollmentLabel($retake->enrollment)),
                TD::make('previous_attempt_id', tkey('exams.fields.previous_attempt'))
                    ->render(fn (ExamRetake $retake): string => $retake->previousAttempt === null
                        ? $this->dash()
                        : (string) Link::make((string) ($retake->previousAttempt->attempt_no ?? $retake->previousAttempt->attempt_number ?? $retake->previousAttempt->id))
                            ->route('platform.exams.attempts.edit', $retake->previousAttempt)),
                TD::make('new_attempt_id', tkey('exams.fields.new_attempt'))
                    ->render(fn (ExamRetake $retake): string => $retake->newAttempt === null
                        ? $this->dash()
                        : (string) Link::make((string) ($retake->newAttempt->attempt_no ?? $retake->newAttempt->attempt_number ?? $retake->newAttempt->id))
                            ->route('platform.exams.attempts.edit', $retake->newAttempt)),
                TD::make('planned_at', tkey('exams.fields.planned_at'))
                    ->render(fn (ExamRetake $retake): string => $this->dateTime($retake->planned_at)),
                TD::make('status', tkey('exams.fields.status'))
                    ->render(fn (ExamRetake $retake): string => $retake->displayStatus()),
                TD::make('reason', tkey('exams.fields.reason'))
                    ->render(fn (ExamRetake $retake): string => $retake->reason ?? $this->dash()),
            ]),
        ];
    }
}
