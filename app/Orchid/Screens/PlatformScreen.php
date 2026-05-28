<?php

declare(strict_types=1);

namespace App\Orchid\Screens;

use App\Actions\GetDrivingSchoolDashboardAction;
use App\Models\DrivingLesson;
use App\Models\Enrollment;
use App\Models\MarketingLead;
use App\Models\TrainingGroup;
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
            'recentOpenLeads' => $data['recentOpenLeads'],
            'activeTrainingGroups' => $data['activeTrainingGroups'],
            'activeEnrollments' => $data['activeEnrollments'],
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

            Layout::table('recentOpenLeads', [
                TD::make('lead', tkey('operations.columns.lead'))
                    ->render(fn (MarketingLead $lead): string => $lead->fullName()),
                TD::make('phone', tkey('operations.columns.phone'))
                    ->render(fn (MarketingLead $lead): string => $lead->phone ?: '-'),
                TD::make('program', tkey('operations.columns.program'))
                    ->render(fn (MarketingLead $lead): string => $lead->trainingProgram?->displayTitle() ?? '-'),
                TD::make('group', tkey('operations.columns.group'))
                    ->render(fn (MarketingLead $lead): string => $lead->trainingGroup?->displayName() ?? '-'),
                TD::make('manager', tkey('operations.columns.manager'))
                    ->render(fn (MarketingLead $lead): string => $lead->responsibleManager?->name ?? tkey('operations.empty.unassigned')),
                TD::make('follow_up', tkey('operations.columns.follow_up'))
                    ->render(fn (MarketingLead $lead): string => $lead->next_follow_up_at?->format('Y-m-d H:i') ?? '-'),
                TD::make('status', tkey('operations.columns.status'))
                    ->render(fn (MarketingLead $lead): string => $lead->status->label()),
            ])->title(tkey('operations.tables.open_leads')),

            Layout::table('activeEnrollments', [
                TD::make('student', tkey('operations.columns.student'))
                    ->render(fn (Enrollment $enrollment): string => $enrollment->studentProfile?->fullName() ?: '-'),
                TD::make('program', tkey('operations.columns.program'))
                    ->render(fn (Enrollment $enrollment): string => $enrollment->trainingProgram?->displayTitle() ?? '-'),
                TD::make('group', tkey('operations.columns.group'))
                    ->render(fn (Enrollment $enrollment): string => $enrollment->trainingGroup?->displayName() ?? '-'),
                TD::make('branch', tkey('operations.columns.branch'))
                    ->render(fn (Enrollment $enrollment): string => $enrollment->branch?->name ?? '-'),
                TD::make('start', tkey('operations.columns.start'))
                    ->render(fn (Enrollment $enrollment): string => $enrollment->start_date?->toDateString() ?? $enrollment->started_at?->toDateString() ?? '-'),
                TD::make('balance', tkey('operations.columns.balance'))
                    ->render(fn (Enrollment $enrollment): string => number_format($enrollment->balanceCents() / 100, 2).' EUR'),
                TD::make('status', tkey('operations.columns.status'))
                    ->render(fn (Enrollment $enrollment): string => tkey('students.enrollments.statuses.'.$enrollment->status->value)),
            ])->title(tkey('operations.tables.active_enrollments')),

            Layout::table('activeTrainingGroups', [
                TD::make('group', tkey('operations.columns.group'))
                    ->render(fn (TrainingGroup $group): string => $group->displayName()),
                TD::make('program', tkey('operations.columns.program'))
                    ->render(fn (TrainingGroup $group): string => $group->trainingProgram?->displayTitle() ?? '-'),
                TD::make('branch', tkey('operations.columns.branch'))
                    ->render(fn (TrainingGroup $group): string => $group->branch?->name ?? '-'),
                TD::make('instructor', tkey('operations.columns.instructor'))
                    ->render(fn (TrainingGroup $group): string => $group->instructor?->name ?? '-'),
                TD::make('start', tkey('operations.columns.start'))
                    ->render(fn (TrainingGroup $group): string => $group->start_date?->toDateString() ?? $group->starts_on?->toDateString() ?? '-'),
                TD::make('seats', tkey('operations.columns.seats'))
                    ->render(fn (TrainingGroup $group): string => $group->seatsAvailable().' / '.($group->capacity_total ?? $group->capacity ?? '-')),
                TD::make('status', tkey('operations.columns.status'))
                    ->render(fn (TrainingGroup $group): string => $group->status->label()),
            ])->title(tkey('operations.tables.active_training_groups')),

            Layout::table('upcomingLessons', [
                TD::make('starts_at', tkey('operations.columns.start'))
                    ->render(fn (DrivingLesson $lesson): string => $lesson->starts_at->format('Y-m-d H:i')),
                TD::make('student', tkey('operations.columns.student'))
                    ->render(fn (DrivingLesson $lesson): string => $lesson->enrollment->studentProfile?->fullName() ?: '-'),
                TD::make('program', tkey('operations.columns.program'))
                    ->render(fn (DrivingLesson $lesson): string => $lesson->enrollment->trainingProgram?->displayTitle() ?? '-'),
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
