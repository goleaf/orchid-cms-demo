<?php

declare(strict_types=1);

namespace App\Orchid\Screens\School;

use App\Models\ExamSession;
use App\Support\LocalizedLabel;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class ExamListScreen extends Screen
{
    public function query(): iterable
    {
        return [
            'sessions' => ExamSession::query()
                ->forExamList()
                ->with([
                    'branch:id,name,name_translations,city,city_translations',
                    'instructor:id,name',
                    'trainingProgram:id,title,name_translations,license_category',
                    'group:id,group_number,name,name_translations',
                    'vehicle:id,registration_number,make,model',
                ])
                ->withCount('attempts')
                ->orderBy('starts_at')
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
        return ['platform.exams', 'exams.view', 'exams.sessions.view'];
    }

    public function commandBar(): iterable
    {
        return [];
    }

    public function layout(): iterable
    {
        return [
            Layout::table('sessions', [
                TD::make('starts_at', tkey('exams.columns.starts_at'))
                    ->sort()
                    ->render(fn (ExamSession $session): string => $session->starts_at->format('Y-m-d H:i')),
                TD::make('exam_type', tkey('operations.columns.type'))
                    ->render(fn (ExamSession $session): string => LocalizedLabel::for('exams.types', $session->exam_type)),
                TD::make('provider', tkey('exams.columns.provider'))
                    ->render(fn (ExamSession $session): string => LocalizedLabel::for('exams.providers', $session->provider)),
                TD::make('program', tkey('operations.columns.program'))
                    ->render(fn (ExamSession $session): string => $session->trainingProgram?->displayTitle() ?? '-'),
                TD::make('group', tkey('exams.columns.group'))
                    ->render(fn (ExamSession $session): string => $session->group?->displayName() ?? '-'),
                TD::make('instructor', tkey('exams.columns.instructor'))
                    ->render(fn (ExamSession $session): string => $session->instructor?->name ?? '-'),
                TD::make('capacity', tkey('exams.columns.capacity'))
                    ->alignCenter()
                    ->render(fn (ExamSession $session): string => $session->seats_taken.'/'.$session->capacity),
                TD::make('attempts_count', tkey('exams.columns.attempts'))
                    ->alignCenter()
                    ->render(fn (ExamSession $session): string => (string) $session->attempts_count),
                TD::make('status', tkey('operations.columns.status'))
                    ->render(fn (ExamSession $session): string => LocalizedLabel::for('exams.session_statuses', $session->status)),
                TD::make('location', tkey('exams.columns.location'))
                    ->render(fn (ExamSession $session): string => $session->location ?: ($session->branch?->displayName() ?? '-')),
            ]),
        ];
    }
}
