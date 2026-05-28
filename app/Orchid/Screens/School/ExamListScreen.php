<?php

declare(strict_types=1);

namespace App\Orchid\Screens\School;

use App\Models\Exam;
use App\Support\LocalizedLabel;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class ExamListScreen extends Screen
{
    public function query(): iterable
    {
        return [
            'exams' => Exam::query()
                ->forExamList()
                ->with([
                    'instructor:id,name',
                    'enrollment:id,student_profile_id,training_program_id',
                    'enrollment.studentProfile:id,first_name,last_name',
                    'enrollment.trainingProgram:id,title,license_category',
                ])
                ->orderBy('scheduled_at')
                ->simplePaginate(15),
        ];
    }

    public function name(): ?string
    {
        return tkey('operations.exams.title');
    }

    public function description(): ?string
    {
        return tkey('operations.exams.description');
    }

    public function permission(): iterable
    {
        return ['platform.exams'];
    }

    public function commandBar(): iterable
    {
        return [];
    }

    public function layout(): iterable
    {
        return [
            Layout::table('exams', [
                TD::make('scheduled_at', tkey('operations.columns.scheduled'))
                    ->render(fn (Exam $exam): string => $exam->scheduled_at->format('Y-m-d H:i')),
                TD::make('student', tkey('operations.columns.student'))
                    ->render(fn (Exam $exam): string => $exam->enrollment->studentProfile->fullName()),
                TD::make('program', tkey('operations.columns.program'))
                    ->render(fn (Exam $exam): string => $exam->enrollment->trainingProgram->title),
                TD::make('exam_type', tkey('operations.columns.type'))
                    ->render(fn (Exam $exam): string => LocalizedLabel::for('operations.exam_types', $exam->exam_type)),
                TD::make('attempt_number', tkey('operations.columns.attempt'))
                    ->render(fn (Exam $exam): string => (string) $exam->attempt_number)
                    ->alignCenter(),
                TD::make('status', tkey('operations.columns.status'))
                    ->render(fn (Exam $exam): string => LocalizedLabel::for('operations.statuses.exams', $exam->status)),
                TD::make('score', tkey('operations.columns.score'))
                    ->alignCenter()
                    ->render(fn (Exam $exam): string => $exam->score ? (string) $exam->score : '-'),
            ]),
        ];
    }
}
