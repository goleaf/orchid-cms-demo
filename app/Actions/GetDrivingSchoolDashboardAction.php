<?php

namespace App\Actions;

use App\Enums\EnrollmentStatus;
use App\Enums\ExamStatus;
use App\Enums\GroupStatus;
use App\Enums\LeadStatus;
use App\Enums\LessonStatus;
use App\Enums\PaymentStatus;
use App\Models\DrivingLesson;
use App\Models\Enrollment;
use App\Models\Exam;
use App\Models\MarketingLead;
use App\Models\Payment;
use App\Models\StudentProfile;
use App\Models\TrainingGroup;
use Illuminate\Support\Facades\Cache;

class GetDrivingSchoolDashboardAction
{
    /**
     * @return array<string, mixed>
     */
    public function handle(): array
    {
        return [
            ...Cache::remember('driving-school.dashboard.metrics', now()->addMinute(), fn (): array => [
                'activeStudents' => StudentProfile::query()
                    ->whereIn('status', ['lead', 'enrolled'])
                    ->count(),
                'activeEnrollments' => Enrollment::query()
                    ->where('status', EnrollmentStatus::Active->value)
                    ->count(),
                'todayLessons' => DrivingLesson::query()
                    ->where('status', LessonStatus::Scheduled->value)
                    ->whereBetween('starts_at', [now()->startOfDay(), now()->endOfDay()])
                    ->count(),
                'scheduledExams' => Exam::query()
                    ->where('status', ExamStatus::Scheduled->value)
                    ->count(),
                'activeGroups' => TrainingGroup::query()
                    ->whereIn('status', [GroupStatus::Recruiting->value, GroupStatus::Active->value])
                    ->count(),
                'openLeads' => MarketingLead::query()
                    ->whereIn('status', [LeadStatus::New->value, LeadStatus::Contacted->value, LeadStatus::Qualified->value])
                    ->count(),
                'paidRevenue' => Payment::query()
                    ->where('status', PaymentStatus::Paid->value)
                    ->sum('amount_cents'),
            ]),
            'upcomingLessons' => DrivingLesson::query()
                ->forScheduleList()
                ->with([
                    'branch:id,name,city',
                    'instructor:id,name',
                    'vehicle:id,registration_number,make,model',
                    'enrollment:id,student_profile_id,training_program_id',
                    'enrollment.studentProfile:id,first_name,last_name',
                    'enrollment.trainingProgram:id,title,license_category',
                ])
                ->upcoming()
                ->limit(8)
                ->get(),
        ];
    }
}
