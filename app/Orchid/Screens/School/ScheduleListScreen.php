<?php

declare(strict_types=1);

namespace App\Orchid\Screens\School;

use App\Models\DrivingLesson;
use App\Support\LocalizedLabel;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class ScheduleListScreen extends Screen
{
    public function query(): iterable
    {
        return [
            'lessons' => DrivingLesson::query()
                ->forScheduleList()
                ->with([
                    'branch:id,name,city',
                    'instructor:id,name',
                    'vehicle:id,registration_number,make,model',
                    'enrollment:id,student_profile_id,training_program_id',
                    'enrollment.studentProfile:id,first_name,last_name',
                    'enrollment.trainingProgram:id,title,license_category',
                ])
                ->orderBy('starts_at')
                ->simplePaginate(15),
        ];
    }

    public function name(): ?string
    {
        return tkey('operations.schedule.title');
    }

    public function description(): ?string
    {
        return tkey('operations.schedule.description');
    }

    public function permission(): iterable
    {
        return ['platform.schedule.lessons'];
    }

    public function commandBar(): iterable
    {
        return [];
    }

    public function layout(): iterable
    {
        return [
            Layout::table('lessons', [
                TD::make('starts_at', tkey('operations.columns.start'))
                    ->render(fn (DrivingLesson $lesson): string => $lesson->starts_at->format('Y-m-d H:i')),
                TD::make('student', tkey('operations.columns.student'))
                    ->render(fn (DrivingLesson $lesson): string => $lesson->enrollment->studentProfile->fullName()),
                TD::make('program', tkey('operations.columns.program'))
                    ->render(fn (DrivingLesson $lesson): string => $lesson->enrollment->trainingProgram->title),
                TD::make('instructor', tkey('operations.columns.instructor'))
                    ->render(fn (DrivingLesson $lesson): string => $lesson->instructor->name),
                TD::make('vehicle', tkey('operations.columns.vehicle'))
                    ->render(fn (DrivingLesson $lesson): string => $lesson->vehicle?->registration_number ?? '-'),
                TD::make('topic', tkey('operations.columns.topic'))
                    ->render(fn (DrivingLesson $lesson): string => $lesson->topic),
                TD::make('status', tkey('operations.columns.status'))
                    ->render(fn (DrivingLesson $lesson): string => LocalizedLabel::for('operations.statuses.lessons', $lesson->status)),
            ]),
        ];
    }
}
