<?php

namespace Tests\Feature;

use App\Actions\CreateExamRetakeAction;
use App\Actions\CreateOrUpdateExamAdmissionAction;
use App\Actions\RecordExamAttemptResultAction;
use App\Actions\ScheduleExamSessionAction;
use App\Enums\DocumentStatus;
use App\Enums\ExamAdmissionStatus;
use App\Enums\ExamAttemptStatus;
use App\Enums\ExamChecklistItemStatus;
use App\Enums\ExamSessionStatus;
use App\Enums\ExamType;
use App\Enums\LessonStatus;
use App\Enums\PaymentStatus;
use App\Http\Requests\Exams\CreateExamRetakeRequest;
use App\Http\Requests\Exams\ExamAdmissionRequest;
use App\Http\Requests\Exams\ExamSessionRequest;
use App\Http\Requests\Exams\RecordExamAttemptRequest;
use App\Models\Branch;
use App\Models\DrivingLesson;
use App\Models\ExamActivity;
use App\Models\ExamAdmission;
use App\Models\ExamAdmissionChecklistItem;
use App\Models\ExamAttempt;
use App\Models\ExamSession;
use App\Models\Instructor;
use App\Models\Payment;
use App\Models\Student;
use App\Models\StudentDocument;
use App\Models\StudentEnrollment;
use App\Models\TrainingGroup;
use App\Models\TrainingProgram;
use App\Models\User;
use App\Rules\ExamAdmissionReadyRule;
use App\Rules\ExamAttemptCanBeRetakenRule;
use App\Rules\ExamSessionCanAcceptAttemptRule;
use App\Rules\ValidExamTypeRule;
use App\Support\Access\SuperadminPermissions;
use Database\Seeders\ExamDemoSeeder;
use Database\Seeders\ExamTranslationSeeder;
use Database\Seeders\LanguageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ExamBlockFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_block_ten_database_foundation_reuses_existing_school_tables(): void
    {
        foreach ([
            'exam_admissions',
            'exam_admission_checklist_items',
            'exam_sessions',
            'exam_attempts',
            'exam_activities',
        ] as $table) {
            $this->assertTrue(Schema::hasTable($table), $table);
        }

        foreach ([
            'enrollment_id',
            'student_profile_id',
            'training_group_id',
            'training_program_id',
            'branch_id',
            'instructor_id',
            'admission_type',
            'status',
            'documents_status',
            'payment_status',
            'checklist_status',
        ] as $column) {
            $this->assertTrue(Schema::hasColumn('exam_admissions', $column), $column);
        }

        foreach (['exam_type', 'provider', 'status', 'starts_at', 'capacity', 'seats_taken', 'official_placeholder_payload'] as $column) {
            $this->assertTrue(Schema::hasColumn('exam_sessions', $column), $column);
        }

        foreach ([
            'exam_admission_id',
            'exam_session_id',
            'enrollment_id',
            'student_profile_id',
            'driving_lesson_id',
            'student_document_id',
            'payment_id',
            'retake_of_attempt_id',
            'official_reference',
            'official_payload',
        ] as $column) {
            $this->assertTrue(Schema::hasColumn('exam_attempts', $column), $column);
        }

        foreach ([
            'exam_question_banks',
            'government_exam_syncs',
            'tenants',
            'subscriptions',
            'resellers',
        ] as $table) {
            $this->assertFalse(Schema::hasTable($table), $table);
        }
    }

    public function test_exam_models_relationships_scopes_and_helpers_work(): void
    {
        $fixture = $this->examFixture();
        $admission = ExamAdmission::factory()
            ->forEnrollment($fixture['enrollment'])
            ->ready()
            ->create(['admission_type' => ExamType::InternalPractical]);

        ExamAdmissionChecklistItem::factory()
            ->forAdmission($admission)
            ->forDocument($fixture['document'])
            ->passed()
            ->create(['code' => 'identity_document']);

        $session = ExamSession::factory()
            ->forGroup($fixture['group'])
            ->internalPractical()
            ->open()
            ->create([
                'starts_at' => now()->addDays(5),
                'ends_at' => now()->addDays(5)->addHour(),
            ]);

        $attempt = ExamAttempt::factory()
            ->forAdmission($admission)
            ->forSession($session)
            ->forDrivingLesson($fixture['lesson'])
            ->forDocument($fixture['document'])
            ->forPayment($fixture['payment'])
            ->failed()
            ->create();

        $retake = ExamAttempt::factory()
            ->forAdmission($admission)
            ->forSession($session)
            ->create([
                'retake_of_attempt_id' => $attempt->id,
                'attempt_number' => 2,
            ]);

        $activity = ExamActivity::factory()->forAttempt($attempt)->create(['type' => 'attempt_recorded']);

        $this->assertTrue($fixture['enrollment']->examAdmissions()->whereKey($admission->id)->exists());
        $this->assertTrue($fixture['student']->examAttempts()->whereKey($attempt->id)->exists());
        $this->assertTrue($fixture['group']->examSessions()->whereKey($session->id)->exists());
        $this->assertTrue($fixture['lesson']->examAttempts()->whereKey($attempt->id)->exists());
        $this->assertTrue($fixture['document']->examChecklistItems()->where('code', 'identity_document')->exists());
        $this->assertTrue($fixture['payment']->examAttempts()->whereKey($attempt->id)->exists());
        $this->assertTrue($admission->fresh(['checklistItems'])->isReady());
        $this->assertTrue(ExamAdmission::query()->ready()->whereKey($admission->id)->exists());
        $this->assertTrue(ExamSession::query()->upcoming()->whereKey($session->id)->exists());
        $this->assertTrue(ExamAttempt::query()->completed()->whereKey($attempt->id)->exists());
        $this->assertTrue($retake->retakeOf->is($attempt));
        $this->assertTrue($attempt->fresh('retakes')->retakes->first()->is($retake));
        $this->assertSame('exams.activities.types.attempt_recorded', $activity->display_type);
    }

    public function test_exam_actions_manage_admissions_sessions_results_retakes_and_activity(): void
    {
        $this->seed([LanguageSeeder::class, ExamTranslationSeeder::class]);

        $fixture = $this->examFixture();
        $user = $this->userWithPermissions([
            'exams.manage_admissions',
            'exams.manage_sessions',
            'exams.record_results',
            'exams.schedule_retakes',
        ]);

        $admission = app(CreateOrUpdateExamAdmissionAction::class)->handle(
            $fixture['enrollment'],
            ['admission_type' => ExamType::InternalPractical->value],
            $user,
        );

        $this->assertSame(ExamAdmissionStatus::Ready, $admission->status);
        $this->assertTrue($admission->isReady());
        $this->assertSame(6, $admission->checklistItems()->count());
        $this->assertSame(0, $admission->checklistItems()->where('status', ExamChecklistItemStatus::Pending->value)->count());

        $session = app(ScheduleExamSessionAction::class)->handle([
            'branch_id' => $fixture['branch']->id,
            'training_program_id' => $fixture['program']->id,
            'training_group_id' => $fixture['group']->id,
            'instructor_id' => $fixture['instructor']->id,
            'exam_type' => ExamType::InternalPractical->value,
            'status' => ExamSessionStatus::Open->value,
            'starts_at' => now()->addDays(10),
            'ends_at' => now()->addDays(10)->addHour(),
            'location' => 'Practice yard',
            'capacity' => 3,
            'seats_taken' => 0,
        ], $user);

        $attempt = app(RecordExamAttemptResultAction::class)->handle($admission, $session, [
            'status' => ExamAttemptStatus::Failed->value,
            'score' => 58,
            'max_score' => 100,
            'driving_lesson_id' => $fixture['lesson']->id,
            'student_document_id' => $fixture['document']->id,
            'payment_id' => $fixture['payment']->id,
            'next_eligible_at' => now()->addWeek(),
        ], $user);

        $this->assertSame(ExamAttemptStatus::Failed, $attempt->status);
        $this->assertFalse($attempt->passed);
        $this->assertSame(ExamAdmissionStatus::RetakeRequired, $admission->refresh()->status);
        $this->assertSame(1, $session->refresh()->seats_taken);

        $retake = app(CreateExamRetakeAction::class)->handle($attempt, [
            'exam_session_id' => $session->id,
            'next_eligible_at' => now()->addWeek(),
        ], $user);

        $this->assertSame(ExamAttemptStatus::Scheduled, $retake->status);
        $this->assertSame($attempt->id, $retake->retake_of_attempt_id);
        $this->assertSame(2, $retake->attempt_number);
        $this->assertSame(ExamAdmissionStatus::RetakeScheduled, $admission->refresh()->status);
        $this->assertGreaterThanOrEqual(4, ExamActivity::query()->count());
    }

    public function test_exam_rules_form_requests_translations_and_permissions_are_wired(): void
    {
        $this->seed([LanguageSeeder::class, ExamTranslationSeeder::class]);

        $invalidType = Validator::make(['exam_type' => 'external_api'], [
            'exam_type' => [new ValidExamTypeRule],
        ]);
        $this->assertTrue($invalidType->fails());
        $this->assertSame(tkey('exams.validation.invalid_exam_type'), $invalidType->errors()->first('exam_type'));

        $notReadyAdmission = ExamAdmission::factory()->create(['status' => ExamAdmissionStatus::Checking]);
        $notReady = Validator::make(['admission' => $notReadyAdmission->id], [
            'admission' => [new ExamAdmissionReadyRule],
        ]);
        $this->assertTrue($notReady->fails());

        $fullSession = ExamSession::factory()->open()->create(['capacity' => 1, 'seats_taken' => 1]);
        $full = Validator::make(['session' => $fullSession->id], [
            'session' => [new ExamSessionCanAcceptAttemptRule],
        ]);
        $this->assertTrue($full->fails());

        $passedAttempt = ExamAttempt::factory()->passed()->create();
        $cannotRetake = Validator::make(['attempt' => $passedAttempt->id], [
            'attempt' => [new ExamAttemptCanBeRetakenRule],
        ]);
        $this->assertTrue($cannotRetake->fails());

        foreach ([
            ExamAdmissionRequest::class => 'exams.manage_admissions',
            ExamSessionRequest::class => 'exams.manage_sessions',
            RecordExamAttemptRequest::class => 'exams.record_results',
            CreateExamRetakeRequest::class => 'exams.schedule_retakes',
        ] as $requestClass => $permission) {
            $request = $requestClass::create('/', 'POST');
            $request->setUserResolver(fn (): User => $this->userWithPermissions([$permission]));
            $this->assertTrue($request->authorize(), $requestClass);
        }

        foreach ([
            'exams.view',
            'exams.manage_admissions',
            'exams.manage_sessions',
            'exams.record_results',
            'exams.schedule_retakes',
            'exams.view_activities',
        ] as $permission) {
            $this->assertContains($permission, SuperadminPermissions::all());
            $this->assertNotSame('permissions.'.$permission, tkey('permissions.'.$permission));
        }
    }

    public function test_exam_seeders_and_orchid_route_are_available(): void
    {
        $this->seed([LanguageSeeder::class, ExamTranslationSeeder::class]);
        $this->examFixture();
        $this->seed(ExamDemoSeeder::class);

        $this->assertNotSame('exams.title', tkey('exams.title'));
        $this->assertTrue(ExamSession::query()->where('external_reference', 'DEMO-INTERNAL-PRACTICAL-001')->exists());
        $this->assertTrue(ExamAttempt::query()->where('status', ExamAttemptStatus::Scheduled->value)->exists());
        $this->assertTrue(Route::has('platform.exams'));

        $this->actingAs($this->userWithPermissions(['exams.view']))
            ->get(route('platform.exams'))
            ->assertOk()
            ->assertSee(tkey('operations.exams.title'));
    }

    /**
     * @return array{
     *     branch: Branch,
     *     instructor: Instructor,
     *     program: TrainingProgram,
     *     group: TrainingGroup,
     *     student: Student,
     *     enrollment: StudentEnrollment,
     *     document: StudentDocument,
     *     payment: Payment,
     *     lesson: DrivingLesson
     * }
     */
    private function examFixture(): array
    {
        $branch = Branch::factory()->create();
        $instructor = Instructor::factory()->create(['branch_id' => $branch->id]);
        $program = TrainingProgram::factory()->create();
        $group = TrainingGroup::factory()->create([
            'branch_id' => $branch->id,
            'training_program_id' => $program->id,
            'course_id' => $program->id,
            'instructor_id' => $instructor->id,
            'capacity' => 12,
            'places_taken' => 0,
            'capacity_taken' => 0,
        ]);
        $student = Student::factory()->active()->create(['branch_id' => $branch->id]);
        $enrollment = StudentEnrollment::factory()->active()->create([
            'student_profile_id' => $student->id,
            'training_program_id' => $program->id,
            'branch_id' => $branch->id,
            'training_group_id' => $group->id,
            'instructor_id' => $instructor->id,
            'payment_status' => PaymentStatus::Paid->value,
            'total_theory_hours' => 40,
            'completed_theory_hours' => 40,
            'total_practice_hours' => 30,
            'completed_practice_hours' => 30,
        ]);

        foreach (['id_card', 'medical_certificate', 'training_contract'] as $documentType) {
            StudentDocument::factory()->create([
                'student_profile_id' => $student->id,
                'enrollment_id' => $enrollment->id,
                'document_type' => $documentType,
                'status' => DocumentStatus::Verified,
            ]);
        }

        $document = StudentDocument::query()
            ->where('student_profile_id', $student->id)
            ->where('document_type', 'id_card')
            ->firstOrFail();
        $payment = Payment::factory()->create([
            'student_profile_id' => $student->id,
            'enrollment_id' => $enrollment->id,
            'status' => PaymentStatus::Paid,
        ]);
        $lesson = DrivingLesson::factory()->create([
            'branch_id' => $branch->id,
            'enrollment_id' => $enrollment->id,
            'instructor_id' => $instructor->id,
            'status' => LessonStatus::Completed,
        ]);

        return compact('branch', 'instructor', 'program', 'group', 'student', 'enrollment', 'document', 'payment', 'lesson');
    }

    /**
     * @param  array<int, string>  $permissions
     */
    private function userWithPermissions(array $permissions = []): User
    {
        $user = User::factory()->create();

        $user->forceFill([
            'permissions' => collect(['platform.index', 'platform.main'])
                ->merge($permissions)
                ->mapWithKeys(fn (string $permission): array => [$permission => true])
                ->all(),
        ])->save();

        return $user;
    }
}
