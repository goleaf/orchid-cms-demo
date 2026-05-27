<?php

declare(strict_types=1);

namespace App\Orchid\Screens\School;

use App\Models\DrivingLesson;
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
        return 'Schedule';
    }

    public function description(): ?string
    {
        return 'Theory, practice, simulator, instructor, and fleet scheduling.';
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
                TD::make('starts_at', 'Start')
                    ->render(fn (DrivingLesson $lesson): string => $lesson->starts_at->format('Y-m-d H:i')),
                TD::make('student', 'Student')
                    ->render(fn (DrivingLesson $lesson): string => $lesson->enrollment->studentProfile->fullName()),
                TD::make('program', 'Program')
                    ->render(fn (DrivingLesson $lesson): string => $lesson->enrollment->trainingProgram->title),
                TD::make('instructor', 'Instructor')
                    ->render(fn (DrivingLesson $lesson): string => $lesson->instructor->name),
                TD::make('vehicle', 'Vehicle')
                    ->render(fn (DrivingLesson $lesson): string => $lesson->vehicle?->registration_number ?? '-'),
                TD::make('topic', 'Topic')
                    ->render(fn (DrivingLesson $lesson): string => $lesson->topic),
                TD::make('status', 'Status')
                    ->render(fn (DrivingLesson $lesson): string => str($lesson->status->value)->replace('_', ' ')->title()->toString()),
            ]),
        ];
    }
}
