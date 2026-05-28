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
            CrmTranslationSeeder::class,
            CrmDictionarySeeder::class,
            StudentDictionarySeeder::class,
            StudentTranslationSeeder::class,
            CommunicationSeeder::class,
            EducationSeeder::class,
            ExamTranslationSeeder::class,
            SystemDesignVibecodingSeeder::class,
        ]);

        $this->call(LandingPageHomeSeeder::class);

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
                'available_languages' => ['Lithuanian', 'English', 'Russian', 'Polish'],
                'price_cents' => 129000,
                'description' => 'Complete Category B training with theory, practice, exams, and document tracking.',
                ...$this->programTranslations([
                    'title' => 'Category B Manual',
                    'duration_weeks' => 14,
                    'title_translations' => $this->translations('Категория B в Вильнюсе', 'Category B in Vilnius', 'B kategorija Vilniuje', 'Kategoria B w Wilnie'),
                    'description_translations' => $this->translations(
                        'Полный курс категории B с теорией, практикой, экзаменами и документами.',
                        'Complete Category B training with theory, practice, exams, and document tracking.',
                        'Pilnas B kategorijos kursas su teorija, praktika, egzaminais ir dokumentais.',
                        'Pelny kurs kategorii B z teoria, praktyka, egzaminami i dokumentami.'
                    ),
                ]),
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
                'title_translations' => $this->translations('Категория A мотоцикл', 'Category A Motorcycle', 'A kategorija motociklui', 'Kategoria A motocykl'),
                'description_translations' => $this->translations('Обучение на мотоцикл с маневрами, безопасностью и подготовкой к маршруту экзамена.', 'Motorcycle training with maneuver practice, road safety, and exam route preparation.', 'Motociklo mokymas su manevrais, saugumu ir pasirengimu egzamino marsrutui.', 'Szkolenie motocyklowe z manewrami, bezpieczenstwem i przygotowaniem do trasy egzaminu.'),
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
                'title_translations' => $this->translations('Категория C грузовик', 'Category C Truck', 'C kategorija sunkvezimiui', 'Kategoria C ciezarowka'),
                'description_translations' => $this->translations('Курс вождения грузовика для подготовки к профессиональным перевозкам.', 'Truck driving course for professional road transport preparation.', 'Sunkvezimio vairavimo kursas profesionaliam keliu transportui.', 'Kurs jazdy ciezarowka do przygotowania transportu zawodowego.'),
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
                'title_translations' => $this->translations('Категория D автобус', 'Category D Bus', 'D kategorija autobusui', 'Kategoria D autobus'),
                'description_translations' => $this->translations('Обучение водителей автобуса с маршрутной дисциплиной, безопасностью пассажиров и подготовкой к экзамену.', 'Bus driver training with route discipline, passenger safety, and exam readiness.', 'Autobuso vairuotoju mokymas su marsruto disciplina, keleiviu saugumu ir pasirengimu egzaminui.', 'Szkolenie kierowcow autobusu z dyscyplina trasy, bezpieczenstwem pasazerow i przygotowaniem do egzaminu.'),
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
                'title_translations' => $this->translations('Категория BE прицеп', 'Category BE Trailer', 'BE kategorija priekabai', 'Kategoria BE przyczepa'),
                'description_translations' => $this->translations('Курс управления прицепом с движением задним ходом, сцепкой и экзаменационными маневрами.', 'Trailer handling course with reversing, coupling, and exam maneuvers.', 'Priekabos valdymo kursas su atbuliniu vaziavimu, prikabinimu ir egzamino manevrais.', 'Kurs obslugi przyczepy z cofaniem, laczeniem i manewrami egzaminacyjnymi.'),
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
                'description' => 'Confidence-building driving lessons for licensed drivers returning to the road.',
                'title_translations' => $this->translations('Восстановление навыков вождения', 'Skill Refresh Lessons', 'Vairavimo igudziu atnaujinimas', 'Odswiezenie umiejetnosci jazdy'),
                'description_translations' => $this->translations('Уроки для водителей с правами, которые возвращаются за руль и хотят восстановить уверенность.', 'Confidence-building driving lessons for licensed drivers returning to the road.', 'Pamokos vairuotojams, griztantiems i kelia ir norintiems atkurti pasitikejima.', 'Lekcje dla kierowcow z prawem jazdy, ktorzy wracaja na droge i chca odzyskac pewnosc.'),
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
                'title_translations' => $this->translations('Индивидуальные уроки вождения', 'Individual Driving Lessons', 'Individualios vairavimo pamokos', 'Indywidualne lekcje jazdy'),
                'description_translations' => $this->translations('Гибкие индивидуальные уроки с выбором инструктора и автомобиля.', 'Flexible one-to-one driving lessons with instructor and vehicle selection.', 'Lankscios individualios pamokos su instruktoriaus ir automobilio pasirinkimu.', 'Elastyczne lekcje indywidualne z wyborem instruktora i samochodu.'),
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
                'description' => 'Condensed Category B course for students who need a faster training plan.',
                'title_translations' => $this->translations('Интенсив категории B', 'Intensive Category B', 'Intensyvus B kategorijos kursas', 'Intensywny kurs kategorii B'),
                'description_translations' => $this->translations('Сжатый курс категории B для учеников, которым нужен более быстрый план обучения.', 'Condensed Category B course for students who need a faster training plan.', 'Sutrumpintas B kategorijos kursas mokiniams, kuriems reikia greitesnio mokymo plano.', 'Skondensowany kurs kategorii B dla uczniow, ktorzy potrzebuja szybszego planu.'),
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
                'title_translations' => $this->translations('Курс для начинающих', 'Course for Beginners', 'Kursas pradedantiesiems', 'Kurs dla poczatkujacych'),
                'description_translations' => $this->translations('Структурированная программа для начинающих с дополнительной городской практикой и уверенностью за рулем.', 'Structured beginner program with extra city driving and confidence practice.', 'Strukturuota pradedanciuju programa su papildoma miesto praktika ir pasitikejimo ugdymu.', 'Ustrukturyzowany program dla poczatkujacych z dodatkowa jazda miejska i budowaniem pewnosci.'),
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
                'title_translations' => $this->translations('Подготовка к пересдаче экзамена', 'Exam Retake Preparation', 'Pasiruosimas perlaikyti egzamina', 'Przygotowanie do poprawki egzaminu'),
                'description_translations' => $this->translations('Целевая подготовка для учеников, которые уже пробовали сдавать экзамен.', 'Focused preparation for students who already attempted an exam.', 'Tikslinis pasirengimas mokiniams, kurie jau bande laikyti egzamina.', 'Skupione przygotowanie dla uczniow, ktorzy juz podchodzili do egzaminu.'),
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
                'title_translations' => $this->translations('Корпоративное обучение водителей', 'Corporate Driver Training', 'Imoniu vairuotoju mokymai', 'Szkolenie kierowcow firmowych'),
                'description_translations' => $this->translations('Обучение водителей для компаний с групповыми отчетами и расписанием по филиалам.', 'Company driver training with group reporting and branch scheduling.', 'Imoniu vairuotoju mokymai su grupinemis ataskaitomis ir filialu tvarkarasciais.', 'Szkolenie kierowcow firmowych z raportami grupowymi i harmonogramem oddzialow.'),
            ],
        ])->each(fn (array $course): TrainingProgram => TrainingProgram::query()->updateOrCreate(
            ['slug' => $course['slug']],
            [
                ...$course,
                'available_languages' => ['Lithuanian', 'English', 'Russian', 'Polish'],
                'required_documents' => ['ID card', 'Medical certificate', 'Photo'],
                'admission_requirements' => 'Admission depends on age, medical eligibility, and document verification.',
                ...$this->programTranslations($course),
                'is_active' => true,
                'seo_title' => $course['title'].' | DrivePro Academy',
                'meta_description' => $course['description'],
                'canonical_url' => null,
                'open_graph_image' => asset('images/driving-school-hero.png'),
                'structured_data' => ['type' => 'Course', 'provider' => 'DrivePro Academy'],
            ],
        ));

        collect([
            ['title' => 'Traffic rules fundamentals', 'module_type' => 'theory', 'sort_order' => 1, 'duration_minutes' => 90],
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
                'bio' => 'Jonas prepares beginners and exam-retake students for Category B routes in Vilnius.',
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
                'bio' => 'Aiste teaches motorcycle control, confidence-building lessons, and Category B basics.',
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
                'notes' => 'Main acquisition campaign for Category B groups.',
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
                'availability_summary' => 'Available for evening Category B lessons',
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
                'message' => 'Interested in evening manual Category B lessons.',
                'rejection_reason' => null,
                'lost_reason_code' => null,
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
                'message' => 'Asked about intensive course availability.',
                'rejection_reason' => null,
                'lost_reason_code' => null,
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
                'lost_reason_code' => 'budget_too_low',
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

        $this->call(ExamDemoSeeder::class);

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
                'body' => 'The retake preparation focused on my actual mistakes and made the second attempt calmer.',
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
                'body' => 'Thank you for the consultation. We can reserve your place after we receive your documents and payment.',
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
                'body' => 'Please send a copy of your ID and your medical certificate so we can confirm your group place.',
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
                ['body' => tkey('crm.activities.messages.seeded_pipeline_import')],
                [
                    'user_id' => $admin->id,
                    'is_internal' => true,
                ],
            );

            $lead->communications()->updateOrCreate(
                [
                    'channel' => 'web_form',
                    'direction' => 'inbound',
                    'subject' => tkey('crm.communications.system_subjects.online_enrollment_request'),
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
                        'subject' => tkey('crm.communications.system_subjects.consultation_call'),
                    ],
                    [
                        'user_id' => $admin->id,
                        'marketing_message_template_id' => $callTemplate->id,
                        'body' => tkey('crm.communications.system_bodies.consultation_call'),
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
                    'reason' => tkey('crm.activities.reasons.seeded_pipeline_state'),
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
                    ['title' => tkey('crm.tasks.system_titles.follow_up', ['status' => $lead->status->label()])],
                    [
                        'assigned_to_user_id' => $admin->id,
                        'created_by_user_id' => $admin->id,
                        'status' => LeadTaskStatus::Open->value,
                        'priority' => ($lead->is_hot ? LeadTaskPriority::High : LeadTaskPriority::Normal)->value,
                        'due_at' => $lead->next_follow_up_at,
                        'completed_at' => null,
                        'notes' => tkey('crm.tasks.system_notes.seeded_pipeline_reminder'),
                    ],
                );
            }
        });

        $this->call(PublicWebsiteSeeder::class);

        Cache::forget('driving-school.dashboard.metrics');
    }

    /**
     * @param  array<string, mixed>  $course
     * @return array<string, mixed>
     */
    private function programTranslations(array $course): array
    {
        $title = $course['title_translations'];
        $description = $course['description_translations'];
        $weeks = (int) $course['duration_weeks'];

        return [
            'title_translations' => $title,
            'name_translations' => $title,
            'duration_translations' => [
                'ru' => $weeks.' нед.',
                'en' => $weeks.' weeks',
                'lt' => $weeks.' sav.',
                'pl' => $weeks.' tyg.',
            ],
            'short_description_translations' => $description,
            'description_translations' => $description,
            'program_summary_translations' => $this->translations(
                'Теория, практика и поддержка до экзамена.',
                'Theory, practice, and support up to the exam.',
                'Teorija, praktika ir pagalba iki egzamino.',
                'Teoria, praktyka i wsparcie do egzaminu.'
            ),
            'requirements_translations' => $this->translations(
                'Нужны возрастное соответствие, медсправка и документы.',
                'Age eligibility, medical certificate, and documents are required.',
                'Reikia tinkamo amziaus, medicinines pazymos ir dokumentu.',
                'Wymagany jest odpowiedni wiek, zaswiadczenie lekarskie i dokumenty.'
            ),
            'included_items_translations' => $this->translations(
                'Теория, практика, материалы и консультация менеджера.',
                'Theory, practice, materials, and manager consultation.',
                'Teorija, praktika, medziaga ir vadybininko konsultacija.',
                'Teoria, praktyka, materialy i konsultacja menedzera.'
            ),
            'includes_translations' => $this->translations(
                'Теория, практика, материалы и консультация менеджера.',
                'Theory, practice, materials, and manager consultation.',
                'Teorija, praktika, medziaga ir vadybininko konsultacija.',
                'Teoria, praktyka, materialy i konsultacja menedzera.'
            ),
            'extra_costs_translations' => $this->translations(
                'Госэкзамены и медсправка оплачиваются отдельно.',
                'State exams and medical certificate are paid separately.',
                'Valstybiniai egzaminai ir medicinine pazyma apmokami atskirai.',
                'Egzaminy panstwowe i zaswiadczenie lekarskie sa platne osobno.'
            ),
            'excludes_translations' => $this->translations(
                'Госэкзамены и медсправка оплачиваются отдельно.',
                'State exams and medical certificate are paid separately.',
                'Valstybiniai egzaminai ir medicinine pazyma apmokami atskirai.',
                'Egzaminy panstwowe i zaswiadczenie lekarskie sa platne osobno.'
            ),
            'theory_program_translations' => $this->translations(
                'Правила дорожного движения, безопасность и подготовка к тесту.',
                'Traffic rules, safety, and test preparation.',
                'Keliu eismo taisykles, saugumas ir pasirengimas testui.',
                'Przepisy ruchu, bezpieczenstwo i przygotowanie do testu.'
            ),
            'practice_program_translations' => $this->translations(
                'Управление автомобилем, городские маршруты и экзаменационные упражнения.',
                'Vehicle control, city routes, and exam maneuvers.',
                'Automobilio valdymas, miesto marsrutai ir egzamino pratimai.',
                'Prowadzenie auta, trasy miejskie i manewry egzaminacyjne.'
            ),
            'seo_title_translations' => [
                'ru' => $title['ru'].' | DrivePro Academy',
                'en' => $title['en'].' | DrivePro Academy',
                'lt' => $title['lt'].' | DrivePro Academy',
                'pl' => $title['pl'].' | DrivePro Academy',
            ],
            'seo_description_translations' => $description,
            'og_title_translations' => [
                'ru' => $title['ru'].' | DrivePro Academy',
                'en' => $title['en'].' | DrivePro Academy',
                'lt' => $title['lt'].' | DrivePro Academy',
                'pl' => $title['pl'].' | DrivePro Academy',
            ],
            'og_description_translations' => $description,
        ];
    }

    /**
     * @return array<string, string>
     */
    private function translations(string $ru, string $en, string $lt, string $pl): array
    {
        return [
            'ru' => $ru,
            'en' => $en,
            'lt' => $lt,
            'pl' => $pl,
        ];
    }
}
