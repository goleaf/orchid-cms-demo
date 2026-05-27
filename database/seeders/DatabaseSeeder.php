<?php

namespace Database\Seeders;

use App\Enums\CampaignStatus;
use App\Enums\DocumentStatus;
use App\Enums\EnrollmentStatus;
use App\Enums\ExamStatus;
use App\Enums\GroupStatus;
use App\Enums\InstructorStatus;
use App\Enums\LeadStatus;
use App\Enums\LessonStatus;
use App\Enums\PaymentStatus;
use App\Enums\StudentStatus;
use App\Enums\VehicleStatus;
use App\Models\Branch;
use App\Models\CourseModule;
use App\Models\DrivingLesson;
use App\Models\Enrollment;
use App\Models\Exam;
use App\Models\Instructor;
use App\Models\LandingPage;
use App\Models\MarketingCampaign;
use App\Models\MarketingLead;
use App\Models\Payment;
use App\Models\StudentDocument;
use App\Models\StudentProfile;
use App\Models\TrainingGroup;
use App\Models\TrainingProgram;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        LandingPage::query()->updateOrCreate(
            ['slug' => 'home'],
            LandingPage::factory()
                ->home()
                ->published()
                ->make()
                ->only((new LandingPage)->getFillable()),
        );

        $branch = Branch::query()->firstOrCreate(
            ['slug' => 'vilnius-main'],
            [
                'name' => 'DrivePro Academy Vilnius',
                'city' => 'Vilnius',
                'address' => 'Gedimino pr. 1',
                'phone' => '+370 600 00000',
                'email' => 'vilnius@drivepro.test',
                'is_active' => true,
            ],
        );

        $program = TrainingProgram::query()->firstOrCreate(
            ['slug' => 'category-b-manual'],
            [
                'title' => 'Category B Manual',
                'license_category' => 'B',
                'transmission' => 'manual',
                'theory_hours' => 40,
                'practice_hours' => 30,
                'price_cents' => 129000,
                'description' => 'Complete category B training with theory, practice, exams, and document tracking.',
                'is_active' => true,
            ],
        );

        collect([
            ['title' => 'Traffic rules foundation', 'module_type' => 'theory', 'sort_order' => 1, 'duration_minutes' => 90],
            ['title' => 'Vehicle control basics', 'module_type' => 'practice', 'sort_order' => 2, 'duration_minutes' => 90],
            ['title' => 'City driving and junctions', 'module_type' => 'practice', 'sort_order' => 3, 'duration_minutes' => 90],
            ['title' => 'Exam route preparation', 'module_type' => 'practice', 'sort_order' => 4, 'duration_minutes' => 90],
        ])->each(fn (array $module): CourseModule => CourseModule::query()->firstOrCreate(
            [
                'training_program_id' => $program->id,
                'sort_order' => $module['sort_order'],
            ],
            [
                ...$module,
                'is_required' => true,
            ],
        ));

        $instructor = Instructor::query()->firstOrCreate(
            ['email' => 'instructor@drivepro.test'],
            [
                'branch_id' => $branch->id,
                'name' => 'Jonas Petrauskas',
                'phone' => '+370 600 11111',
                'license_number' => 'INS-10001',
                'categories' => ['B'],
                'status' => InstructorStatus::Active,
                'hired_at' => now()->subYears(3)->toDateString(),
            ],
        );

        $group = TrainingGroup::query()->firstOrCreate(
            ['code' => 'B-VNO-001'],
            [
                'branch_id' => $branch->id,
                'training_program_id' => $program->id,
                'instructor_id' => $instructor->id,
                'name' => 'Evening Category B Group',
                'status' => GroupStatus::Recruiting,
                'capacity' => 14,
                'starts_on' => now()->addDays(10)->toDateString(),
                'ends_on' => now()->addMonths(4)->toDateString(),
                'meeting_days' => ['tuesday', 'thursday'],
                'meeting_time' => '18:00',
                'classroom' => 'Classroom 2',
            ],
        );

        $campaign = MarketingCampaign::query()->firstOrCreate(
            ['utm_campaign' => 'spring-category-b'],
            [
                'branch_id' => $branch->id,
                'name' => 'Spring Category B Intake',
                'channel' => 'google_ads',
                'status' => CampaignStatus::Active,
                'budget_cents' => 150000,
                'starts_on' => now()->subDays(12)->toDateString(),
                'ends_on' => now()->addDays(35)->toDateString(),
                'utm_source' => 'google',
                'notes' => 'Main acquisition campaign for category B groups.',
            ],
        );

        $vehicle = Vehicle::query()->firstOrCreate(
            ['registration_number' => 'ABC-123'],
            [
                'branch_id' => $branch->id,
                'instructor_id' => $instructor->id,
                'make' => 'Toyota',
                'model' => 'Yaris',
                'year' => 2023,
                'license_category' => 'B',
                'transmission' => 'manual',
                'odometer_km' => 24800,
                'status' => VehicleStatus::Active,
                'next_service_at' => now()->addDays(45)->toDateString(),
                'next_inspection_at' => now()->addMonths(9)->toDateString(),
            ],
        );

        $student = StudentProfile::query()->firstOrCreate(
            ['email' => 'student@drivepro.test'],
            [
                'branch_id' => $branch->id,
                'first_name' => 'Marta',
                'last_name' => 'Kazlauskaite',
                'phone' => '+370 600 22222',
                'date_of_birth' => now()->subYears(22)->toDateString(),
                'national_id' => '50001010000',
                'address' => 'Vilnius',
                'source' => 'website',
                'status' => StudentStatus::Enrolled,
                'notes' => 'Prefers evening lessons.',
                'registered_at' => now()->subDays(18),
            ],
        );

        MarketingLead::query()->firstOrCreate(
            ['email' => 'student@drivepro.test'],
            [
                'marketing_campaign_id' => $campaign->id,
                'branch_id' => $branch->id,
                'converted_student_profile_id' => $student->id,
                'first_name' => 'Marta',
                'last_name' => 'Kazlauskaite',
                'phone' => '+370 600 22222',
                'source' => 'website',
                'status' => LeadStatus::Converted,
                'license_category' => 'B',
                'contacted_at' => now()->subDays(18),
                'converted_at' => now()->subDays(14),
                'message' => 'Interested in evening manual category B lessons.',
            ],
        );

        MarketingLead::query()->firstOrCreate(
            ['email' => 'lead@drivepro.test'],
            [
                'marketing_campaign_id' => $campaign->id,
                'branch_id' => $branch->id,
                'converted_student_profile_id' => null,
                'first_name' => 'Tomas',
                'last_name' => 'Jankauskas',
                'phone' => '+370 600 33333',
                'source' => 'google_ads',
                'status' => LeadStatus::Qualified,
                'license_category' => 'B',
                'contacted_at' => now()->subDays(1),
                'converted_at' => null,
                'message' => 'Asked for intensive course availability.',
            ],
        );

        $enrollment = Enrollment::query()->updateOrCreate(
            [
                'student_profile_id' => $student->id,
                'training_program_id' => $program->id,
            ],
            [
                'training_group_id' => $group->id,
                'instructor_id' => $instructor->id,
                'status' => EnrollmentStatus::Active,
                'started_at' => now()->subDays(14)->toDateString(),
                'completed_at' => null,
                'contracted_price_cents' => $program->price_cents,
                'paid_cents' => 45000,
            ],
        );

        collect([1, 3, 6])->each(fn (int $days, int $index): DrivingLesson => DrivingLesson::query()->firstOrCreate(
            [
                'enrollment_id' => $enrollment->id,
                'starts_at' => now()->addDays($days)->setTime(17, 0),
            ],
            [
                'branch_id' => $branch->id,
                'instructor_id' => $instructor->id,
                'vehicle_id' => $vehicle->id,
                'lesson_type' => 'practice',
                'status' => LessonStatus::Scheduled,
                'ends_at' => now()->addDays($days)->setTime(18, 30),
                'topic' => ['Parking control', 'City traffic', 'Exam route practice'][$index],
                'location' => 'Vilnius training area',
            ],
        ));

        Exam::query()->firstOrCreate(
            [
                'enrollment_id' => $enrollment->id,
                'exam_type' => 'theory',
                'attempt_number' => 1,
            ],
            [
                'instructor_id' => $instructor->id,
                'status' => ExamStatus::Scheduled,
                'scheduled_at' => now()->addDays(21)->setTime(10, 0),
                'score' => null,
            ],
        );

        Payment::query()->firstOrCreate(
            ['reference' => 'PAY-DEMO-001'],
            [
                'student_profile_id' => $student->id,
                'enrollment_id' => $enrollment->id,
                'amount_cents' => 45000,
                'currency' => 'EUR',
                'method' => 'card',
                'status' => PaymentStatus::Paid,
                'paid_at' => now()->subDays(10),
                'notes' => 'Initial installment.',
            ],
        );

        collect([
            ['document_type' => 'id_card', 'status' => DocumentStatus::Verified, 'title' => 'Identity document'],
            ['document_type' => 'medical_certificate', 'status' => DocumentStatus::Submitted, 'title' => 'Medical certificate'],
            ['document_type' => 'training_contract', 'status' => DocumentStatus::Verified, 'title' => 'Training contract'],
        ])->each(fn (array $document): StudentDocument => StudentDocument::query()->firstOrCreate(
            [
                'student_profile_id' => $student->id,
                'document_type' => $document['document_type'],
            ],
            [
                'enrollment_id' => $enrollment->id,
                'status' => $document['status'],
                'title' => $document['title'],
                'number' => null,
                'issued_at' => now()->subDays(12)->toDateString(),
                'expires_at' => now()->addYear()->toDateString(),
            ],
        ));

        $permissions = [
            'platform.index' => true,
            'platform.content.home' => true,
            'platform.operations.branches' => true,
            'platform.operations.instructors' => true,
            'platform.operations.groups' => true,
            'platform.crm.students' => true,
            'platform.lms.programs' => true,
            'platform.schedule.lessons' => true,
            'platform.fleet.vehicles' => true,
            'platform.exams' => true,
            'platform.finance.payments' => true,
            'platform.documents' => true,
            'platform.marketing.campaigns' => true,
            'platform.marketing.leads' => true,
            'platform.systems.roles' => true,
            'platform.systems.users' => true,
            'platform.systems.attachment' => true,
        ];

        User::query()->updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'permissions' => $permissions,
            ],
        );

        Cache::forget('driving-school.dashboard.metrics');
    }
}
