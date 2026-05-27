<?php

declare(strict_types=1);

namespace App\Orchid\Screens\School;

use App\Models\StudentProfile;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class StudentListScreen extends Screen
{
    public function query(): iterable
    {
        return [
            'students' => StudentProfile::query()
                ->forCrmList()
                ->with('branch:id,name,city')
                ->withCount('enrollments')
                ->orderByDesc('registered_at')
                ->simplePaginate(15),
        ];
    }

    public function name(): ?string
    {
        return 'Student CRM';
    }

    public function description(): ?string
    {
        return 'Leads, active students, and enrollment context.';
    }

    public function permission(): iterable
    {
        return ['platform.crm.students'];
    }

    public function commandBar(): iterable
    {
        return [];
    }

    public function layout(): iterable
    {
        return [
            Layout::table('students', [
                TD::make('name', 'Student')
                    ->render(fn (StudentProfile $student): string => $student->fullName()),

                TD::make('email', 'Email')
                    ->render(fn (StudentProfile $student): string => $student->email),

                TD::make('phone', 'Phone')
                    ->render(fn (StudentProfile $student): string => $student->phone ?? '-'),

                TD::make('branch', 'Branch')
                    ->render(fn (StudentProfile $student): string => $student->branch?->name ?? 'Unassigned'),

                TD::make('status', 'Status')
                    ->render(fn (StudentProfile $student): string => str($student->status->value)->replace('_', ' ')->title()->toString()),

                TD::make('enrollments_count', 'Enrollments')
                    ->render(fn (StudentProfile $student): string => (string) $student->enrollments_count)
                    ->alignCenter(),

                TD::make('registered_at', 'Registered')
                    ->render(fn (StudentProfile $student): string => $student->registered_at?->toDateString() ?? '-'),
            ]),
        ];
    }
}
