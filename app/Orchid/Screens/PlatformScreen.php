<?php

declare(strict_types=1);

namespace App\Orchid\Screens;

use App\Actions\GetDrivingSchoolDashboardAction;
use App\Models\DrivingLesson;
use Orchid\Screen\Action;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class PlatformScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(GetDrivingSchoolDashboardAction $dashboard): iterable
    {
        $data = $dashboard->handle();

        return [
            'active_students' => $data['activeStudents'],
            'active_enrollments' => $data['activeEnrollments'],
            'today_lessons' => $data['todayLessons'],
            'scheduled_exams' => $data['scheduledExams'],
            'active_groups' => $data['activeGroups'],
            'open_leads' => $data['openLeads'],
            'paid_revenue' => number_format($data['paidRevenue'] / 100, 2).' EUR',
            'upcomingLessons' => $data['upcomingLessons'],
        ];
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        return 'Auto-school operations';
    }

    /**
     * Display header description.
     */
    public function description(): ?string
    {
        return 'CRM, LMS, schedules, fleet, exams, payments, documents, and analytics.';
    }

    /**
     * The screen's action buttons.
     *
     * @return Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Link::make('Website')
                ->icon('bs.box-arrow-up-right')
                ->route('site.home')
                ->target('_blank'),

            Link::make('Students')
                ->icon('bs.people')
                ->route('platform.crm.students'),

            Link::make('Schedule')
                ->icon('bs.calendar-week')
                ->route('platform.schedule.lessons'),
        ];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]
     */
    public function layout(): iterable
    {
        return [
            Layout::metrics([
                'Active students' => 'active_students',
                'Active enrollments' => 'active_enrollments',
                'Active groups' => 'active_groups',
                'Open leads' => 'open_leads',
                'Lessons today' => 'today_lessons',
                'Scheduled exams' => 'scheduled_exams',
                'Paid revenue' => 'paid_revenue',
            ]),

            Layout::table('upcomingLessons', [
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
            ])->title('Upcoming lessons'),
        ];
    }
}
