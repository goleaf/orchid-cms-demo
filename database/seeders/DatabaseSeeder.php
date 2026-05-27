<?php

namespace Database\Seeders;

use App\Enums\ArticleStatus;
use App\Enums\CampaignStatus;
use App\Enums\DocumentStatus;
use App\Enums\EnrollmentStatus;
use App\Enums\ExamStatus;
use App\Enums\GroupStatus;
use App\Enums\InstructorStatus;
use App\Enums\LeadStatus;
use App\Enums\LeadTaskPriority;
use App\Enums\LeadTaskStatus;
use App\Enums\LessonStatus;
use App\Enums\PaymentStatus;
use App\Enums\ReviewStatus;
use App\Enums\StudentStatus;
use App\Enums\VehicleStatus;
use App\Models\Branch;
use App\Models\CourseModule;
use App\Models\DrivingLesson;
use App\Models\Enrollment;
use App\Models\Exam;
use App\Models\Instructor;
use App\Models\KnowledgeArticle;
use App\Models\LandingPage;
use App\Models\MarketingCampaign;
use App\Models\MarketingLead;
use App\Models\MarketingMessageTemplate;
use App\Models\Payment;
use App\Models\StudentDocument;
use App\Models\StudentProfile;
use App\Models\StudentReview;
use App\Models\TrainingGroup;
use App\Models\TrainingProgram;
use App\Models\User;
use App\Models\Vehicle;
use App\Support\Access\SuperadminPermissions;
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
        $this->call([
            LanguageSeeder::class,
            SystemTranslationSeeder::class,
        ]);

        LandingPage::query()->updateOrCreate(
            ['slug' => 'home'],
            LandingPage::factory()
                ->home()
                ->published()
                ->make()
                ->only((new LandingPage)->getFillable()),
        );

        $branch = Branch::query()->updateOrCreate(
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

        $kaunasBranch = Branch::query()->updateOrCreate(
            ['slug' => 'kaunas-center'],
            [
                'name' => 'DrivePro Academy Kaunas',
                'city' => 'Kaunas',
                'address' => 'Laisves al. 10',
                'phone' => '+370 600 00010',
                'email' => 'kaunas@drivepro.test',
                'is_active' => true,
            ],
        );

        $program = TrainingProgram::query()->updateOrCreate(
            ['slug' => 'category-b-manual'],
            [
                'title' => 'Category B Manual',
                'license_category' => 'B',
                'transmission' => 'manual',
                'theory_hours' => 40,
                'practice_hours' => 30,
                'duration_weeks' => 14,
                'format' => 'mixed',
                'available_languages' => ['Lithuanian', 'English', 'Russian'],
                'price_cents' => 129000,
                'description' => 'Complete category B training with theory, practice, exams, and document tracking.',
                'required_documents' => ['ID card', 'Medical certificate', 'Photo', 'Training contract'],
                'admission_requirements' => 'Students must meet local age and medical eligibility requirements before practical driving lessons.',
                'is_active' => true,
                'seo_title' => 'Category B driving course in Vilnius | DrivePro Academy',
                'meta_description' => 'Category B manual driving course with theory, practice, documents, groups, instructors, and exam preparation.',
                'canonical_url' => null,
                'open_graph_image' => asset('images/driving-school-hero.png'),
                'structured_data' => ['type' => 'Course', 'provider' => 'DrivePro Academy'],
            ],
        );

        collect([
            [
                'slug' => 'category-a-motorcycle',
                'title' => 'Category A Motorcycle',
                'license_category' => 'A',
                'transmission' => 'manual',
                'theory_hours' => 20,
                'practice_hours' => 12,
                'duration_weeks' => 8,
                'format' => 'mixed',
                'price_cents' => 89000,
                'description' => 'Motorcycle training with maneuver practice, road safety, and exam route preparation.',
            ],
            [
                'slug' => 'category-c-truck',
                'title' => 'Category C Truck',
                'license_category' => 'C',
                'transmission' => 'manual',
                'theory_hours' => 35,
                'practice_hours' => 20,
                'duration_weeks' => 12,
                'format' => 'offline',
                'price_cents' => 159000,
                'description' => 'Truck driving course for professional road transport preparation.',
            ],
            [
                'slug' => 'category-d-bus',
                'title' => 'Category D Bus',
                'license_category' => 'D',
                'transmission' => 'automatic',
                'theory_hours' => 35,
                'practice_hours' => 20,
                'duration_weeks' => 12,
                'format' => 'offline',
                'price_cents' => 169000,
                'description' => 'Bus driver training with route discipline, passenger safety, and exam readiness.',
            ],
            [
                'slug' => 'category-be-trailer',
                'title' => 'Category BE Trailer',
                'license_category' => 'BE',
                'transmission' => 'manual',
                'theory_hours' => 12,
                'practice_hours' => 10,
                'duration_weeks' => 6,
                'format' => 'offline',
                'price_cents' => 69000,
                'description' => 'Trailer handling course with reversing, coupling, and exam maneuvers.',
            ],
            [
                'slug' => 'skill-refresh-driving',
                'title' => 'Skill Refresh Lessons',
                'license_category' => 'B',
                'transmission' => 'manual',
                'theory_hours' => 4,
                'practice_hours' => 8,
                'duration_weeks' => 3,
                'format' => 'mixed',
                'price_cents' => 39000,
                'description' => 'Driving confidence recovery for licensed drivers returning to the road.',
            ],
            [
                'slug' => 'individual-driving-lessons',
                'title' => 'Individual Driving Lessons',
                'license_category' => 'B',
                'transmission' => 'manual',
                'theory_hours' => 0,
                'practice_hours' => 10,
                'duration_weeks' => 4,
                'format' => 'offline',
                'price_cents' => 49000,
                'description' => 'Flexible one-to-one driving lessons with instructor and vehicle selection.',
            ],
            [
                'slug' => 'intensive-category-b',
                'title' => 'Intensive Category B',
                'license_category' => 'B',
                'transmission' => 'manual',
                'theory_hours' => 40,
                'practice_hours' => 34,
                'duration_weeks' => 6,
                'format' => 'mixed',
                'price_cents' => 149000,
                'description' => 'Condensed category B course for students who need a faster training plan.',
            ],
            [
                'slug' => 'beginner-driving-course',
                'title' => 'Course for Beginners',
                'license_category' => 'B',
                'transmission' => 'manual',
                'theory_hours' => 44,
                'practice_hours' => 34,
                'duration_weeks' => 16,
                'format' => 'mixed',
                'price_cents' => 139000,
                'description' => 'Structured beginner program with extra city driving and confidence practice.',
            ],
            [
                'slug' => 'exam-retake-preparation',
                'title' => 'Exam Retake Preparation',
                'license_category' => 'B',
                'transmission' => 'manual',
                'theory_hours' => 6,
                'practice_hours' => 8,
                'duration_weeks' => 3,
                'format' => 'mixed',
                'price_cents' => 45000,
                'description' => 'Focused preparation for students who already attempted an exam.',
            ],
            [
                'slug' => 'corporate-driver-training',
                'title' => 'Corporate Driver Training',
                'license_category' => 'B',
                'transmission' => 'automatic',
                'theory_hours' => 10,
                'practice_hours' => 10,
                'duration_weeks' => 4,
                'format' => 'mixed',
                'price_cents' => 99000,
                'description' => 'Company driver training with group reporting and branch scheduling.',
            ],
        ])->each(fn (array $course): TrainingProgram => TrainingProgram::query()->updateOrCreate(
            ['slug' => $course['slug']],
            [
                ...$course,
                'available_languages' => ['Lithuanian', 'English', 'Russian'],
                'required_documents' => ['ID card', 'Medical certificate', 'Photo'],
                'admission_requirements' => 'Admission depends on age, medical eligibility, and document verification.',
                'is_active' => true,
                'seo_title' => $course['title'].' | DrivePro Academy',
                'meta_description' => $course['description'],
                'canonical_url' => null,
                'open_graph_image' => asset('images/driving-school-hero.png'),
                'structured_data' => ['type' => 'Course', 'provider' => 'DrivePro Academy'],
            ],
        ));

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

        $instructor = Instructor::query()->updateOrCreate(
            ['email' => 'instructor@drivepro.test'],
            [
                'branch_id' => $branch->id,
                'name' => 'Jonas Petrauskas',
                'phone' => '+370 600 11111',
                'photo_path' => null,
                'license_number' => 'INS-10001',
                'experience_years' => 9,
                'rating' => 4.9,
                'review_count' => 128,
                'categories' => ['B'],
                'languages' => ['Lithuanian', 'English', 'Russian'],
                'availability_summary' => 'Weekday evenings and Saturday mornings',
                'teaching_style' => 'Calm city-driving coach with structured feedback after every lesson.',
                'bio' => 'Jonas prepares beginners and exam-retake students for category B routes in Vilnius.',
                'status' => InstructorStatus::Active,
                'hired_at' => now()->subYears(3)->toDateString(),
            ],
        );

        $motorcycleInstructor = Instructor::query()->updateOrCreate(
            ['email' => 'motorcycle@drivepro.test'],
            [
                'branch_id' => $kaunasBranch->id,
                'name' => 'Aiste Vaitkute',
                'phone' => '+370 600 11112',
                'photo_path' => null,
                'license_number' => 'INS-10002',
                'experience_years' => 7,
                'rating' => 4.8,
                'review_count' => 94,
                'categories' => ['A', 'B'],
                'languages' => ['Lithuanian', 'English'],
                'availability_summary' => 'Mornings and selected weekends',
                'teaching_style' => 'Precise maneuver training with a strong focus on risk awareness.',
                'bio' => 'Aiste teaches motorcycle control, confidence recovery, and category B basics.',
                'status' => InstructorStatus::Active,
                'hired_at' => now()->subYears(2)->toDateString(),
            ],
        );

        $group = TrainingGroup::query()->updateOrCreate(
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

        $categoryAProgram = TrainingProgram::query()
            ->select(['id'])
            ->where('slug', 'category-a-motorcycle')
            ->firstOrFail();

        TrainingGroup::query()->updateOrCreate(
            ['code' => 'A-KAU-001'],
            [
                'branch_id' => $kaunasBranch->id,
                'training_program_id' => $categoryAProgram->id,
                'instructor_id' => $motorcycleInstructor->id,
                'name' => 'Morning Motorcycle Group',
                'status' => GroupStatus::Recruiting,
                'capacity' => 8,
                'starts_on' => now()->addDays(18)->toDateString(),
                'ends_on' => now()->addMonths(3)->toDateString(),
                'meeting_days' => ['monday', 'wednesday'],
                'meeting_time' => '09:00',
                'classroom' => 'Motorcycle yard',
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

        $vehicle = Vehicle::query()->updateOrCreate(
            ['registration_number' => 'ABC-123'],
            [
                'branch_id' => $branch->id,
                'instructor_id' => $instructor->id,
                'photo_path' => null,
                'make' => 'Toyota',
                'model' => 'Yaris',
                'year' => 2023,
                'license_category' => 'B',
                'transmission' => 'manual',
                'odometer_km' => 24800,
                'status' => VehicleStatus::Active,
                'availability_summary' => 'Available for evening category B lessons',
                'description' => 'Compact manual training car with city-friendly visibility and predictable clutch control.',
                'features' => ['Dual controls', 'Parking sensors', 'Manual gearbox'],
                'next_service_at' => now()->addDays(45)->toDateString(),
                'next_inspection_at' => now()->addMonths(9)->toDateString(),
            ],
        );

        Vehicle::query()->updateOrCreate(
            ['registration_number' => 'MOTO-01'],
            [
                'branch_id' => $kaunasBranch->id,
                'instructor_id' => $motorcycleInstructor->id,
                'photo_path' => null,
                'make' => 'Honda',
                'model' => 'CB500F',
                'year' => 2022,
                'license_category' => 'A',
                'transmission' => 'manual',
                'odometer_km' => 12800,
                'status' => VehicleStatus::Active,
                'availability_summary' => 'Available for morning motorcycle training',
                'description' => 'Balanced motorcycle for maneuver yard practice and controlled road lessons.',
                'features' => ['ABS', 'Maneuver setup', 'Protective equipment'],
                'next_service_at' => now()->addDays(60)->toDateString(),
                'next_inspection_at' => now()->addMonths(8)->toDateString(),
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

        $convertedLead = MarketingLead::query()->updateOrCreate(
            ['email' => 'student@drivepro.test'],
            [
                'marketing_campaign_id' => $campaign->id,
                'responsible_manager_id' => null,
                'branch_id' => $branch->id,
                'training_program_id' => $program->id,
                'training_group_id' => $group->id,
                'instructor_id' => $instructor->id,
                'converted_student_profile_id' => $student->id,
                'first_name' => 'Marta',
                'last_name' => 'Kazlauskaite',
                'phone' => '+370 600 22222',
                'messenger' => 'WhatsApp',
                'city' => 'Vilnius',
                'source' => 'website',
                'status' => LeadStatus::BecameStudent,
                'license_category' => 'B',
                'preferred_format' => 'mixed',
                'preferred_language' => 'Lithuanian',
                'preferred_time' => 'Evenings',
                'budget_cents' => 129000,
                'is_hot' => true,
                'next_follow_up_at' => null,
                'last_status_changed_at' => now()->subDays(14),
                'privacy_accepted_at' => now()->subDays(18),
                'contacted_at' => now()->subDays(18),
                'converted_at' => now()->subDays(14),
                'message' => 'Interested in evening manual category B lessons.',
                'rejection_reason' => null,
                'crm_snapshot' => ['form' => 'public_enrollment', 'captured_at' => now()->subDays(18)->toIso8601String()],
                'utm_source' => 'website',
                'utm_medium' => 'organic',
                'utm_campaign' => 'homepage',
            ],
        );

        $qualifiedLead = MarketingLead::query()->updateOrCreate(
            ['email' => 'lead@drivepro.test'],
            [
                'marketing_campaign_id' => $campaign->id,
                'responsible_manager_id' => null,
                'branch_id' => $branch->id,
                'training_program_id' => $program->id,
                'training_group_id' => $group->id,
                'instructor_id' => null,
                'converted_student_profile_id' => null,
                'first_name' => 'Tomas',
                'last_name' => 'Jankauskas',
                'phone' => '+370 600 33333',
                'messenger' => 'Telegram',
                'city' => 'Vilnius',
                'source' => 'google_ads',
                'status' => LeadStatus::ConsultationDone,
                'license_category' => 'B',
                'preferred_format' => 'mixed',
                'preferred_language' => 'English',
                'preferred_time' => 'Weekends',
                'budget_cents' => 150000,
                'is_hot' => true,
                'next_follow_up_at' => now()->subHours(3),
                'last_status_changed_at' => now()->subDay(),
                'privacy_accepted_at' => now()->subDays(2),
                'contacted_at' => now()->subDays(1),
                'converted_at' => null,
                'message' => 'Asked for intensive course availability.',
                'rejection_reason' => null,
                'crm_snapshot' => ['form' => 'public_enrollment', 'captured_at' => now()->subDays(2)->toIso8601String()],
                'utm_source' => 'google',
                'utm_medium' => 'cpc',
                'utm_campaign' => 'spring-category-b',
            ],
        );

        $rejectedLead = MarketingLead::query()->updateOrCreate(
            ['email' => 'lost@drivepro.test'],
            [
                'marketing_campaign_id' => $campaign->id,
                'responsible_manager_id' => null,
                'branch_id' => $branch->id,
                'training_program_id' => $program->id,
                'training_group_id' => null,
                'instructor_id' => null,
                'converted_student_profile_id' => null,
                'first_name' => 'Rasa',
                'last_name' => 'Paulauskaite',
                'phone' => '+370 600 55555',
                'messenger' => 'Viber',
                'city' => 'Vilnius',
                'source' => 'facebook',
                'status' => LeadStatus::Rejected,
                'license_category' => 'B',
                'preferred_format' => 'offline',
                'preferred_language' => 'Lithuanian',
                'preferred_time' => 'Mornings',
                'budget_cents' => 80000,
                'is_hot' => false,
                'next_follow_up_at' => null,
                'last_status_changed_at' => now()->subDays(4),
                'privacy_accepted_at' => now()->subDays(7),
                'contacted_at' => now()->subDays(6),
                'converted_at' => null,
                'message' => 'Asked for a lower budget option.',
                'rejection_reason' => 'Budget too low',
                'crm_snapshot' => ['form' => 'public_enrollment', 'captured_at' => now()->subDays(7)->toIso8601String()],
                'utm_source' => 'facebook',
                'utm_medium' => 'paid_social',
                'utm_campaign' => 'spring-category-b',
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

        collect([
            [
                'title' => 'Structured lessons helped me pass',
                'author_name' => 'Marta Kazlauskaite',
                'body' => 'The theory plan, practice schedule, and document reminders were clear from the first week.',
                'rating' => 5,
                'admin_reply' => 'Thank you, Marta. We are glad the evening group worked well for your schedule.',
            ],
            [
                'title' => 'Calm instructor and useful feedback',
                'author_name' => 'Tomas Jankauskas',
                'body' => 'I liked that every lesson had a topic, route notes, and clear next steps before the exam.',
                'rating' => 5,
                'admin_reply' => 'Good luck with the exam route preparation.',
            ],
            [
                'title' => 'Good recovery after a failed exam',
                'author_name' => 'Egle Stankeviciene',
                'body' => 'The retake preparation focused on my real mistakes and made the second attempt calmer.',
                'rating' => 4,
                'admin_reply' => null,
            ],
        ])->each(fn (array $review): StudentReview => StudentReview::query()->updateOrCreate(
            ['title' => $review['title']],
            [
                'student_profile_id' => $student->id,
                'training_program_id' => $program->id,
                'training_group_id' => $group->id,
                'instructor_id' => $instructor->id,
                'author_name' => $review['author_name'],
                'rating' => $review['rating'],
                'body' => $review['body'],
                'video_url' => null,
                'admin_reply' => $review['admin_reply'],
                'status' => ReviewStatus::Published,
                'published_at' => now()->subDays(6 + $review['rating']),
            ],
        ));

        collect([
            [
                'slug' => 'how-to-choose-driving-school',
                'title' => 'How to choose a driving school',
                'category' => 'selection',
                'excerpt' => 'A practical checklist for comparing programs, instructors, schedules, cars, and exam support.',
            ],
            [
                'slug' => 'how-driving-training-works',
                'title' => 'How driving training works',
                'category' => 'learning',
                'excerpt' => 'The full path from application and documents to LMS theory, practice lessons, and exam readiness.',
            ],
            [
                'slug' => 'how-to-pass-driving-exam',
                'title' => 'How to prepare for the driving exam',
                'category' => 'exam',
                'excerpt' => 'What to repeat before the exam, how to use practice routes, and how to avoid common mistakes.',
            ],
            [
                'slug' => 'common-practice-mistakes',
                'title' => 'Common practice mistakes',
                'category' => 'practice',
                'excerpt' => 'A short guide to parking, junctions, observation habits, speed control, and exam nerves.',
            ],
        ])->each(fn (array $article, int $index): KnowledgeArticle => KnowledgeArticle::query()->updateOrCreate(
            ['slug' => $article['slug']],
            [
                'title' => $article['title'],
                'category' => $article['category'],
                'excerpt' => $article['excerpt'],
                'body' => $article['excerpt']."\n\nDrivePro Academy keeps the training path transparent: the student sees the program, documents, schedule, instructor, car, payments, and exam preparation steps.\n\nManagers can connect each request to CRM leads, groups, instructors, and branches, so the public website and operating platform stay synchronized.",
                'status' => ArticleStatus::Published,
                'published_at' => now()->subDays(20 - $index),
                'seo_title' => $article['title'].' | DrivePro Academy',
                'meta_description' => $article['excerpt'],
                'canonical_url' => null,
                'open_graph_image' => asset('images/driving-school-hero.png'),
                'structured_data' => ['type' => 'Article', 'publisher' => 'DrivePro Academy'],
            ],
        ));

        $callTemplate = MarketingMessageTemplate::query()->updateOrCreate(
            ['name' => 'Call after consultation'],
            [
                'channel' => 'phone',
                'subject' => 'Consultation follow-up',
                'body' => 'Thank you for the consultation. We can reserve your place after documents and payment.',
                'is_active' => true,
                'sort_order' => 10,
            ],
        );
        $smsTemplate = MarketingMessageTemplate::query()->updateOrCreate(
            ['name' => 'SMS callback reminder'],
            [
                'channel' => 'sms',
                'subject' => null,
                'body' => 'DrivePro Academy: we tried to reach you. Please reply or choose a convenient callback time.',
                'is_active' => true,
                'sort_order' => 20,
            ],
        );
        $messengerTemplate = MarketingMessageTemplate::query()->updateOrCreate(
            ['name' => 'Messenger missing documents'],
            [
                'channel' => null,
                'subject' => 'Missing documents',
                'body' => 'Please send your ID copy and medical certificate so we can confirm your group place.',
                'is_active' => true,
                'sort_order' => 30,
            ],
        );

        $permissions = SuperadminPermissions::enabled();

        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'permissions' => $permissions,
            ],
        );
        $this->call(SuperadminRoleSeeder::class);

        collect([$convertedLead, $qualifiedLead, $rejectedLead])->each(function (MarketingLead $lead) use ($admin, $callTemplate, $smsTemplate, $messengerTemplate, $qualifiedLead): void {
            $lead->update(['responsible_manager_id' => $admin->id]);

            $lead->comments()->updateOrCreate(
                ['body' => 'Lead imported into sales CRM pipeline.'],
                [
                    'user_id' => $admin->id,
                    'is_internal' => true,
                ],
            );

            $lead->communications()->updateOrCreate(
                [
                    'channel' => 'web_form',
                    'direction' => 'inbound',
                    'subject' => 'Online enrollment request',
                ],
                [
                    'user_id' => $admin->id,
                    'marketing_message_template_id' => $messengerTemplate->id,
                    'body' => $lead->message,
                    'communicated_at' => $lead->created_at,
                    'client_replied_at' => null,
                    'callback_required_at' => null,
                    'call_recording_url' => null,
                    'call_recording_reference' => null,
                    'metadata' => [
                        'source' => $lead->source,
                        'utm_source' => $lead->utm_source,
                        'utm_medium' => $lead->utm_medium,
                        'utm_campaign' => $lead->utm_campaign,
                    ],
                ],
            );

            if ($lead->is($qualifiedLead)) {
                $lead->communications()->updateOrCreate(
                    [
                        'channel' => 'phone',
                        'direction' => 'outbound',
                        'subject' => 'Consultation call',
                    ],
                    [
                        'user_id' => $admin->id,
                        'marketing_message_template_id' => $callTemplate->id,
                        'body' => 'Client answered, asked for documents and weekend practice slots.',
                        'communicated_at' => now()->subHours(20),
                        'client_replied_at' => now()->subHours(20),
                        'callback_required_at' => $lead->next_follow_up_at,
                        'call_recording_url' => 'https://telephony.drivepro.test/recordings/lead-'.$lead->id,
                        'call_recording_reference' => 'REC-LEAD-'.$lead->id,
                        'metadata' => [
                            'sms_fallback_template_id' => $smsTemplate->id,
                        ],
                    ],
                );
            }

            $lead->statusHistories()->updateOrCreate(
                [
                    'to_status' => $lead->status->value,
                    'reason' => 'Seeded CRM pipeline state.',
                ],
                [
                    'user_id' => $admin->id,
                    'from_status' => LeadStatus::New->value,
                    'changed_at' => $lead->last_status_changed_at ?? now(),
                ],
            );

            $lead->documents()->updateOrCreate(
                ['original_name' => 'application-document.pdf'],
                [
                    'path' => 'lead-documents/application-document.pdf',
                    'mime_type' => 'application/pdf',
                    'size' => 128000,
                ],
            );

            if ($lead->next_follow_up_at !== null) {
                $lead->tasks()->updateOrCreate(
                    ['title' => 'Follow up: '.$lead->status->label()],
                    [
                        'assigned_to_user_id' => $admin->id,
                        'created_by_user_id' => $admin->id,
                        'status' => LeadTaskStatus::Open->value,
                        'priority' => ($lead->is_hot ? LeadTaskPriority::High : LeadTaskPriority::Normal)->value,
                        'due_at' => $lead->next_follow_up_at,
                        'completed_at' => null,
                        'notes' => 'Seeded reminder for CRM funnel demo.',
                    ],
                );
            }
        });

        Cache::forget('driving-school.dashboard.metrics');
    }
}
