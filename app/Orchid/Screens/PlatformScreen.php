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
        return tkey('operations.dashboard.title');
    }

    /**
     * Display header description.
     */
    public function description(): ?string
    {
        return tkey('operations.dashboard.description');
    }

    /**
     * The screen's action buttons.
     *
     * @return Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Link::make(tkey('operations.dashboard.actions.website'))
                ->icon('bs.box-arrow-up-right')
                ->route('site.home')
                ->target('_blank'),

            Link::make(tkey('operations.dashboard.actions.students'))
                ->icon('bs.people')
                ->route('platform.crm.students'),

            Link::make(tkey('operations.dashboard.actions.schedule'))
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
                tkey('operations.metrics.active_students') => 'active_students',
                tkey('operations.metrics.active_enrollments') => 'active_enrollments',
                tkey('operations.metrics.active_groups') => 'active_groups',
                tkey('operations.metrics.open_leads') => 'open_leads',
                tkey('operations.metrics.lessons_today') => 'today_lessons',
                tkey('operations.metrics.scheduled_exams') => 'scheduled_exams',
                tkey('operations.metrics.paid_revenue') => 'paid_revenue',
            ]),

            Layout::table('upcomingLessons', [
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
            ])->title(tkey('operations.tables.upcoming_lessons')),
        ];
    }
}
