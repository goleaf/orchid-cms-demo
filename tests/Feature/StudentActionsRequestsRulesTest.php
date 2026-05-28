<?php

namespace Tests\Feature;

use App\Actions\AddStudentNoteAction;
use App\Actions\ArchiveStudentAction;
use App\Actions\AssignEnrollmentGroupAction;
use App\Actions\AssignStudentManagerAction;
use App\Actions\CancelStudentTaskAction;
use App\Actions\ChangeEnrollmentStatusAction;
use App\Actions\ChangeStudentStatusAction;
use App\Actions\CompleteStudentTaskAction;
use App\Actions\CreatePortalAccessPlaceholderAction;
use App\Actions\CreateStudentAction;
use App\Actions\CreateStudentEnrollmentAction;
use App\Actions\CreateStudentOnboardingTasksAction;
use App\Actions\CreateStudentTaskAction;
use App\Actions\FindMatchingStudentsAction;
use App\Actions\GenerateEnrollmentNumberAction;
use App\Actions\GenerateStudentNumberAction;
use App\Actions\PrepareStudentDocumentsPlaceholderAction;
use App\Actions\PrepareStudentPaymentPlaceholderAction;
use App\Actions\UpdateStudentAction;
use App\Actions\UpdateStudentEnrollmentAction;
use App\Enums\EnrollmentStatus as EnrollmentStatusEnum;
use App\Enums\StudentStatus as StudentStatusEnum;
use App\Http\Requests\Students\AddStudentNoteRequest;
use App\Http\Requests\Students\ArchiveStudentRequest;
use App\Http\Requests\Students\AssignEnrollmentGroupRequest;
use App\Http\Requests\Students\AssignStudentManagerRequest;
use App\Http\Requests\Students\CancelStudentTaskRequest;
use App\Http\Requests\Students\ChangeEnrollmentStatusRequest;
use App\Http\Requests\Students\ChangeStudentStatusRequest;
use App\Http\Requests\Students\CompleteStudentTaskRequest;
use App\Http\Requests\Students\CreatePortalAccessRequest;
use App\Http\Requests\Students\StoreStudentEnrollmentRequest;
use App\Http\Requests\Students\StoreStudentRequest;
use App\Http\Requests\Students\StoreStudentTaskRequest;
use App\Http\Requests\Students\UpdateStudentEnrollmentRequest;
use App\Http\Requests\Students\UpdateStudentRequest;
use App\Models\Branch;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\StudentTask;
use App\Models\TrainingGroup;
use App\Models\TrainingProgram;
use App\Models\User;
use App\Rules\StudentPhoneOrEmailRequiredRule;
use App\Rules\UniqueStudentContactRule;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\StudentDictionarySeeder;
use Database\Seeders\StudentTranslationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class StudentActionsRequestsRulesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app()->setLocale('en');
        $this->seed(LanguageSeeder::class);
        $this->seed(StudentDictionarySeeder::class);
        $this->seed(StudentTranslationSeeder::class);
    }

    public function test_generate_student_and_enrollment_numbers_are_unique(): void
    {
        $year = (int) now()->format('Y');
        Student::factory()->create(['student_number' => 'STU-'.$year.'-0001']);
        StudentEnrollment::factory()->create(['enrollment_number' => 'ENR-'.$year.'-0001']);

        $studentNumber = app(GenerateStudentNumberAction::class)->handle($year);
        $enrollmentNumber = app(GenerateEnrollmentNumberAction::class)->handle($year);

        $this->assertMatchesRegularExpression('/^STU-'.$year.'-\d{4,}$/', $studentNumber);
        $this->assertMatchesRegularExpression('/^ENR-'.$year.'-\d{4,}$/', $enrollmentNumber);
        $this->assertDatabaseMissing('student_profiles', ['student_number' => $studentNumber]);
        $this->assertDatabaseMissing('enrollments', ['enrollment_number' => $enrollmentNumber]);
    }

    public function test_create_update_archive_and_status_actions_work(): void
    {
        $branch = Branch::factory()->create();
        $manager = User::factory()->create();

        $student = app(CreateStudentAction::class)->handle([
            'branch_id' => $branch->id,
            'first_name' => 'Action',
            'last_name' => 'Student',
            'email' => 'student-actions@example.test',
            'phone' => '+370 600 11111',
            'manager_id' => $manager->id,
            'consent_accepted' => true,
        ], $manager);

        $this->assertDatabaseHas('student_profiles', [
            'id' => $student->id,
            'student_number' => $student->student_number,
            'status' => StudentStatusEnum::Active->value,
            'manager_id' => $manager->id,
        ]);
        $this->assertDatabaseHas('student_activities', [
            'student_id' => $student->id,
            'type' => 'created_manually',
        ]);

        $student = app(UpdateStudentAction::class)->handle($student, [
            'phone' => '+370 600 22222',
            'city' => 'Vilnius',
        ], $manager);

        $this->assertSame('+37060022222', $student->phone);
        $this->assertSame('+37060022222', $student->normalized_phone);
        $this->assertDatabaseHas('student_activities', [
            'student_id' => $student->id,
            'type' => 'updated',
        ]);

        $student = app(ChangeStudentStatusAction::class)->handle($student, StudentStatusEnum::Inactive, $manager);
        $this->assertSame(StudentStatusEnum::Inactive, $student->status);

        $this->expectException(ValidationException::class);
        app(ChangeStudentStatusAction::class)->handle($student->refresh(), StudentStatusEnum::Blocked, $manager);
    }

    public function test_archive_student_action_archives_without_physical_delete(): void
    {
        $student = Student::factory()->active()->create();

        $student = app(ArchiveStudentAction::class)->handle($student);

        $this->assertSame(StudentStatusEnum::Archived, $student->status);
        $this->assertDatabaseHas('student_profiles', ['id' => $student->id, 'deleted_at' => null]);
        $this->assertDatabaseHas('student_activities', ['student_id' => $student->id, 'type' => 'archived']);
    }

    public function test_enrollment_create_update_status_and_group_actions_work(): void
    {
        $student = Student::factory()->active()->create();
        $program = TrainingProgram::factory()->create(['price_cents' => 129000, 'price' => 1290.00]);
        $group = TrainingGroup::factory()->create([
            'training_program_id' => $program->id,
            'capacity' => 12,
            'places_taken' => 2,
        ]);

        $enrollment = app(CreateStudentEnrollmentAction::class)->handle($student, [
            'training_program_id' => $program->id,
            'training_group_id' => $group->id,
            'training_language' => 'en',
            'format' => 'group',
            'gearbox_type' => 'manual',
        ]);

        $this->assertSame(EnrollmentStatusEnum::WaitingDocuments, $enrollment->status);
        $this->assertSame($program->id, $enrollment->training_program_id);
        $this->assertSame($group->id, $enrollment->training_group_id);
        $this->assertDatabaseHas('student_activities', [
            'student_id' => $student->id,
            'type' => 'enrollment_created',
        ]);

        $enrollment = app(UpdateStudentEnrollmentAction::class)->handle($enrollment, [
            'preferred_time' => 'evening',
            'format' => 'hybrid',
        ]);
        $this->assertSame('evening', $enrollment->preferred_time);
        $this->assertSame('hybrid', $enrollment->format);

        $enrollment = app(ChangeEnrollmentStatusAction::class)->handle($enrollment, EnrollmentStatusEnum::WaitingPayment);
        $this->assertSame(EnrollmentStatusEnum::WaitingPayment, $enrollment->status);

        $newGroup = TrainingGroup::factory()->create([
            'training_program_id' => $program->id,
            'capacity' => 10,
            'places_taken' => 0,
        ]);
        $enrollment = app(AssignEnrollmentGroupAction::class)->handle($enrollment, $newGroup);
        $this->assertSame($newGroup->id, $enrollment->training_group_id);

        $this->expectException(ValidationException::class);
        app(ChangeEnrollmentStatusAction::class)->handle($enrollment->refresh(), EnrollmentStatusEnum::Completed);
    }

    public function test_manager_note_task_and_placeholder_actions_work(): void
    {
        $manager = User::factory()->create();
        $student = Student::factory()->active()->create();
        $enrollment = StudentEnrollment::factory()->waitingDocuments()->create([
            'student_profile_id' => $student->id,
            'price' => 1290.00,
            'currency' => 'EUR',
            'payment_status' => 'pending',
        ]);

        $student = app(AssignStudentManagerAction::class)->handle($student, $manager->id, $manager);
        $this->assertSame($manager->id, $student->manager_id);

        $note = app(AddStudentNoteAction::class)->handle($student, 'Internal note', $manager, $enrollment);
        $this->assertSame('note_added', $note->type);

        $task = app(CreateStudentTaskAction::class)->handle(
            $student,
            ['en' => 'Call student', 'ru' => 'Call student', 'lt' => 'Call student', 'pl' => 'Call student'],
            $manager,
            now()->addDay(),
            'high',
            null,
            $manager->id,
            $enrollment,
        );
        $this->assertSame('open', $task->status);

        $task = app(CompleteStudentTaskAction::class)->handle($task, $manager);
        $this->assertSame('done', $task->status);
        $this->assertNotNull($task->completed_at);

        $cancelledTask = StudentTask::factory()->open()->create(['student_id' => $student->id]);
        $cancelledTask = app(CancelStudentTaskAction::class)->handle($cancelledTask, $manager);
        $this->assertSame('cancelled', $cancelledTask->status);

        $tasks = app(CreateStudentOnboardingTasksAction::class)->handle($student->refresh(), $manager, $enrollment);
        $this->assertCount(6, $tasks);

        $student = app(CreatePortalAccessPlaceholderAction::class)->handle($student->refresh(), $manager);
        $this->assertNotNull($student->portal_access_created_at);

        $student = app(PrepareStudentDocumentsPlaceholderAction::class)->handle($student->refresh(), $manager);
        $this->assertSame('missing', $student->documents_summary['identity_document']);

        $student = app(PrepareStudentPaymentPlaceholderAction::class)->handle($student->refresh(), $manager, $enrollment);
        $this->assertSame('pending', $student->payment_summary['payment_status']);
        $this->assertSame(1290.00, (float) $student->payment_summary['expected_price']);
    }

    public function test_duplicate_student_detection_works(): void
    {
        $student = Student::factory()->active()->create([
            'phone' => '+370 600 33333',
            'normalized_phone' => '+37060033333',
            'email' => 'duplicate-student@example.test',
            'personal_code' => '39001010000',
        ]);

        $matches = app(FindMatchingStudentsAction::class)->handle(['phone' => '+370 600 33333']);
        $this->assertTrue($matches->first()['student']->is($student));
        $this->assertSame('phone', $matches->first()['reason']);

        $matches = app(FindMatchingStudentsAction::class)->handle(['email' => 'duplicate-student@example.test']);
        $this->assertSame('email', $matches->first()['reason']);

        $matches = app(FindMatchingStudentsAction::class)->handle(['personal_code' => '39001010000']);
        $this->assertSame('personal_code', $matches->first()['reason']);
    }

    public function test_required_form_requests_exist(): void
    {
        foreach ([
            StoreStudentRequest::class,
            UpdateStudentRequest::class,
            ArchiveStudentRequest::class,
            ChangeStudentStatusRequest::class,
            StoreStudentEnrollmentRequest::class,
            UpdateStudentEnrollmentRequest::class,
            ChangeEnrollmentStatusRequest::class,
            AssignStudentManagerRequest::class,
            AssignEnrollmentGroupRequest::class,
            AddStudentNoteRequest::class,
            StoreStudentTaskRequest::class,
            CompleteStudentTaskRequest::class,
            CancelStudentTaskRequest::class,
            CreatePortalAccessRequest::class,
        ] as $request) {
            $this->assertTrue(class_exists($request), $request);
        }
    }

    public function test_validation_errors_are_translated(): void
    {
        $validator = Validator::make(
            ['student' => ['phone' => null, 'email' => null]],
            ['student' => [new StudentPhoneOrEmailRequiredRule]],
        );

        $this->assertTrue($validator->fails());
        $this->assertSame('Enter a student phone or email.', $validator->errors()->first('student'));

        Student::factory()->active()->create([
            'phone' => '+370 600 44444',
            'normalized_phone' => '+37060044444',
        ]);

        $validator = Validator::make(
            ['student' => ['phone' => '+370 600 44444']],
            ['student' => [new UniqueStudentContactRule]],
        );

        $this->assertTrue($validator->fails());
        $this->assertSame('A student with these contacts already exists.', $validator->errors()->first('student'));
    }
}
