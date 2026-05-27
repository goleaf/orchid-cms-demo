<?php

declare(strict_types=1);

namespace App\Orchid\Screens\School;

use App\Models\Exam;
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
        return 'Exams';
    }

    public function description(): ?string
    {
        return 'Theory and practice exam attempts.';
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
                TD::make('scheduled_at', 'Scheduled')
                    ->render(fn (Exam $exam): string => $exam->scheduled_at->format('Y-m-d H:i')),
                TD::make('student', 'Student')
                    ->render(fn (Exam $exam): string => $exam->enrollment->studentProfile->fullName()),
                TD::make('program', 'Program')
                    ->render(fn (Exam $exam): string => $exam->enrollment->trainingProgram->title),
                TD::make('exam_type', 'Type')
                    ->render(fn (Exam $exam): string => str($exam->exam_type)->title()->toString()),
                TD::make('attempt_number', 'Attempt')
                    ->render(fn (Exam $exam): string => (string) $exam->attempt_number)
                    ->alignCenter(),
                TD::make('status', 'Status')
                    ->render(fn (Exam $exam): string => str($exam->status->value)->replace('_', ' ')->title()->toString()),
                TD::make('score', 'Score')
                    ->alignCenter()
                    ->render(fn (Exam $exam): string => $exam->score ? (string) $exam->score : '-'),
            ]),
        ];
    }
}
