<?php

namespace Tests\Feature;

use App\Actions\CancelStudentTaskAction;
use App\Actions\CompleteStudentTaskAction;
use App\Actions\CreatePortalAccessPlaceholderAction;
use App\Actions\CreateStudentOnboardingTasksAction;
use App\Actions\CreateStudentTaskAction;
use App\Actions\PrepareStudentDocumentsPlaceholderAction;
use App\Actions\PrepareStudentPaymentPlaceholderAction;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\User;
use App\Rules\TranslatedStudentTaskTitleRequiredRule;
use App\Rules\ValidStudentTaskPriorityRule;
use App\Rules\ValidStudentTaskStatusRule;
use Carbon\Carbon;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\StudentDictionarySeeder;
use Database\Seeders\StudentTranslationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StudentOnboardingWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app()->setLocale('en');
        Carbon::setTestNow(Carbon::parse('2026-05-28 10:00:00'));
        $this->seed(LanguageSeeder::class);
        $this->seed(StudentDictionarySeeder::class);
        $this->seed(StudentTranslationSeeder::class);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_onboarding_tasks_are_created_with_translated_titles_and_are_idempotent(): void
    {
        $manager = User::factory()->create();
        $student = Student::factory()->active()->create(['manager_id' => $manager->id]);
        $enrollment = StudentEnrollment::factory()->waitingDocuments()->create([
            'student_profile_id' => $student->id,
        ]);

        $tasks = app(CreateStudentOnboardingTasksAction::class)->handle($student, $manager, $enrollment);

        $this->assertCount(6, $tasks);
        $this->assertDatabaseCount('student_tasks', 6);
        $this->assertDatabaseCount('student_activities', 6);
        $this->assertSame(tkey('students.tasks.defaults.verify_personal_data'), $tasks[0]->display_title);
        $this->assertSame(tkey('students.tasks.defaults.request_documents'), $tasks[1]->display_title);
        $this->assertSame('high', $tasks[1]->priority);
        $this->assertSame('high', $tasks[3]->priority);

        $expectedDueDates = [
            'verify_personal_data' => now()->addDay()->toDateString(),
            'request_documents' => now()->addDay()->toDateString(),
            'prepare_contract' => now()->addDays(2)->toDateString(),
            'check_payment' => now()->addDays(2)->toDateString(),
            'assign_group' => now()->addDays(3)->toDateString(),
            'create_portal_access' => now()->addDays(3)->toDateString(),
        ];

        foreach (array_values($expectedDueDates) as $index => $date) {
            $this->assertSame($date, $tasks[$index]->due_at->toDateString());
        }

        $again = app(CreateStudentOnboardingTasksAction::class)->handle($student->refresh(), $manager, $enrollment);

        $this->assertCount(6, $again);
        $this->assertDatabaseCount('student_tasks', 6);
        $this->assertSame(6, $student->activities()->where('type', 'task_created')->count());

        app(CreateStudentOnboardingTasksAction::class)->handle($student->refresh(), $manager, $enrollment, true);

        $this->assertDatabaseCount('student_tasks', 12);
        $this->assertSame(12, $student->activities()->where('type', 'task_created')->count());
    }

    public function test_student_task_can_be_completed_and_cancelled_with_activities(): void
    {
        $manager = User::factory()->create();
        $student = Student::factory()->active()->create(['manager_id' => $manager->id]);

        $task = app(CreateStudentTaskAction::class)->handle(
            $student,
            ['en' => 'Call student', 'ru' => 'Call student', 'lt' => 'Call student', 'pl' => 'Call student'],
            $manager,
            now()->addDay(),
            'urgent',
        );

        $completed = app(CompleteStudentTaskAction::class)->handle($task, $manager);

        $this->assertSame('done', $completed->status);
        $this->assertNotNull($completed->completed_at);
        $this->assertDatabaseHas('student_activities', [
            'student_id' => $student->id,
            'type' => 'task_completed',
        ]);

        $taskToCancel = app(CreateStudentTaskAction::class)->handle(
            $student->refresh(),
            ['en' => 'Prepare documents', 'ru' => 'Prepare documents', 'lt' => 'Prepare documents', 'pl' => 'Prepare documents'],
            $manager,
            now()->addDays(2),
        );

        $cancelled = app(CancelStudentTaskAction::class)->handle($taskToCancel, $manager);

        $this->assertSame('cancelled', $cancelled->status);
        $this->assertNotNull($cancelled->cancelled_at);
        $this->assertDatabaseHas('student_activities', [
            'student_id' => $student->id,
            'type' => 'task_cancelled',
        ]);
    }

    public function test_document_payment_and_portal_placeholders_create_summary_data_and_activities(): void
    {
        $manager = User::factory()->create();
        $student = Student::factory()->active()->create();
        $enrollment = StudentEnrollment::factory()->waitingDocuments()->create([
            'student_profile_id' => $student->id,
            'price' => 1290.00,
            'currency' => 'EUR',
            'payment_status' => 'pending',
        ]);

        $student = app(PrepareStudentDocumentsPlaceholderAction::class)->handle($student, $manager);

        $this->assertSame([
            'identity_document' => 'missing',
            'medical_certificate' => 'missing',
            'photo' => 'missing',
            'contract' => 'not_created',
        ], $student->documents_summary);

        $student = app(PrepareStudentPaymentPlaceholderAction::class)->handle($student->refresh(), $manager, $enrollment);

        $this->assertSame('pending', $student->payment_summary['status']);
        $this->assertSame('pending', $student->payment_summary['payment_status']);
        $this->assertSame(1290.00, (float) $student->payment_summary['expected_price']);
        $this->assertSame('EUR', $student->payment_summary['currency']);

        $student = app(CreatePortalAccessPlaceholderAction::class)->handle($student->refresh(), $manager);

        $this->assertNotNull($student->portal_access_created_at);

        foreach (['document_placeholder_created', 'payment_placeholder_created', 'portal_access_created'] as $type) {
            $this->assertDatabaseHas('student_activities', [
                'student_id' => $student->id,
                'type' => $type,
            ]);
        }
    }

    public function test_student_task_validation_errors_are_translated(): void
    {
        $validator = Validator::make(
            [
                'task' => [
                    'title_translations' => ['en' => ''],
                    'priority' => 'later',
                    'status' => 'waiting',
                ],
            ],
            [
                'task.title_translations' => [new TranslatedStudentTaskTitleRequiredRule],
                'task.priority' => [new ValidStudentTaskPriorityRule],
                'task.status' => [new ValidStudentTaskStatusRule],
            ],
        );

        $this->assertTrue($validator->fails());
        $this->assertSame(tkey('students.validation.default_task_title_required'), $validator->errors()->first('task.title_translations'));
        $this->assertSame(tkey('students.validation.invalid_task_priority'), $validator->errors()->first('task.priority'));
        $this->assertSame(tkey('students.validation.invalid_task_status'), $validator->errors()->first('task.status'));
    }
}
