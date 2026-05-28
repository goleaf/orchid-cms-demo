<?php

namespace Tests\Feature;

use App\Actions\AddExamActivityAction;
use App\Actions\AddStudentToExamSessionAction;
use App\Actions\ApproveExamAdmissionAction;
use App\Actions\BlockExamAdmissionAction;
use App\Actions\BuildExamAdmissionChecklistAction;
use App\Actions\CancelExamAttemptAction;
use App\Actions\CancelExamSessionAction;
use App\Actions\ChangeExamSessionStatusAction;
use App\Actions\CheckExamAdmissionAction;
use App\Actions\CompleteExamAttemptAction;
use App\Actions\CreateExamAttemptAction;
use App\Actions\CreateExamRetakeAction;
use App\Actions\CreateExamSessionAction;
use App\Actions\GenerateExamAttemptNumberAction;
use App\Actions\GenerateExamNumberAction;
use App\Actions\MarkExamAttemptNoShowAction;
use App\Actions\MarkExamFailedAction;
use App\Actions\MarkExamPassedAction;
use App\Actions\RecordExamResultAction;
use App\Actions\RemoveStudentFromExamSessionAction;
use App\Actions\ScheduleExamRetakeAction;
use App\Actions\StartExamAttemptAction;
use App\Actions\UpdateExamSessionAction;
use App\Enums\DocumentStatus;
use App\Enums\ExamAdmissionStatus;
use App\Enums\ExamAttemptStatus as LegacyExamAttemptStatus;
use App\Enums\ExamSessionStatus as LegacyExamSessionStatus;
use App\Enums\ExamType as LegacyExamType;
use App\Enums\PaymentStatus;
use App\Http\Requests\Exams\AddStudentToExamSessionRequest;
use App\Http\Requests\Exams\ChangeExamSessionStatusRequest;
use App\Http\Requests\Exams\CheckExamAdmissionRequest;
use App\Http\Requests\Exams\CompleteExamAttemptRequest;
use App\Http\Requests\Exams\CreateExamAttemptRequest;
use App\Http\Requests\Exams\CreateExamRetakeRequest;
use App\Http\Requests\Exams\RecordExamResultRequest;
use App\Http\Requests\Exams\StoreExamSessionRequest;
use App\Http\Requests\Exams\UpdateExamSessionRequest;
use App\Models\Branch;
use App\Models\ExamActivity;
use App\Models\ExamAttempt;
use App\Models\ExamAttemptStatus as ExamAttemptStatusModel;
use App\Models\ExamParticipant;
use App\Models\ExamResult;
use App\Models\ExamResultStatus as ExamResultStatusModel;
use App\Models\ExamRetake;
use App\Models\ExamSession;
use App\Models\ExamStatus as ExamStatusModel;
use App\Models\ExamType as ExamTypeModel;
use App\Models\Instructor;
use App\Models\Payment;
use App\Models\Student;
use App\Models\StudentDocument;
use App\Models\StudentEnrollment;
use App\Models\TrainingGroup;
use App\Models\TrainingProgram;
use App\Models\User;
use App\Rules\ActiveExamStatusRule;
use App\Rules\ActiveExamTypeRule;
use App\Rules\EnrollmentActiveForExamRule;
use App\Rules\EnrollmentCanTakeExamRule;
use App\Rules\ExamAttemptCanCompleteRule;
use App\Rules\ExamAttemptCanStartRule;
use App\Rules\ExamResultScoreRule;
use App\Rules\ExamRetakeAllowedRule;
use App\Rules\ExamSessionCapacityRule;
use App\Rules\InternalExamPassedRule;
use App\Rules\RequiredDocumentsAcceptedRule;
use App\Rules\RequiredPaymentsCompletedRule;
use App\Rules\RequiredPracticeHoursRule;
use App\Rules\RequiredTheoryHoursRule;
use App\Rules\StudentActiveForExamRule;
use App\Rules\StudentCanJoinExamSessionRule;
use App\Rules\ValidExamSessionStatusTransitionRule;
use Database\Seeders\ExamDictionarySeeder;
use Database\Seeders\ExamTranslationSeeder;
use Database\Seeders\LanguageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ExamActionsValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app()->setLocale('en');
        $this->seed([LanguageSeeder::class, ExamTranslationSeeder::class, ExamDictionarySeeder::class]);
    }

    public function test_exam_actions_manage_sessions_admissions_attempts_results_retakes_and_activity(): void
    {
        $fixture = $this->examFixture();
        $user = $this->userWithPermissions([
            'exams.manage_admissions',
            'exams.manage_sessions',
            'exams.record_results',
            'exams.schedule_retakes',
        ]);
        $type = ExamTypeModel::query()->where('code', 'internal_practical')->firstOrFail();
        $scheduled = ExamStatusModel::query()->where('code', 'scheduled')->firstOrFail();
        $open = ExamStatusModel::query()->where('code', 'open')->firstOrFail();
        $completed = ExamStatusModel::query()->where('code', 'completed')->firstOrFail();
        $needsRetake = ExamResultStatusModel::query()->where('code', 'needs_retake')->firstOrFail();

        ExamSession::factory()->create(['exam_number' => 'EXM-'.now()->format('Ymd').'-0001']);
        $this->assertSame('EXM-'.now()->format('Ymd').'-0002', app(GenerateExamNumberAction::class)->handle(now()));

        $session = app(CreateExamSessionAction::class)->handle([
            'type_id' => $type->id,
            'status_id' => $scheduled->id,
            'branch_id' => $fixture['branch']->id,
            'group_id' => $fixture['group']->id,
            'training_program_id' => $fixture['program']->id,
            'instructor_id' => $fixture['instructor']->id,
            'scheduled_at' => now()->addDays(7),
            'location' => 'Practice yard',
            'capacity' => 2,
        ], $user);

        $this->assertSame($type->id, $session->type_id);
        $this->assertSame($scheduled->id, $session->status_id);

        $session = app(ChangeExamSessionStatusAction::class)->handle($session, $open, $user);
        $this->assertSame($open->id, $session->status_id);

        $session = app(UpdateExamSessionAction::class)->handle($session, [
            'status_id' => $completed->id,
            'capacity' => 3,
            'location' => 'Updated yard',
        ], $user);
        $this->assertSame('Updated yard', $session->location);
        $session = app(ChangeExamSessionStatusAction::class)->handle($session, $open, $user, true);

        $admissionResult = app(CheckExamAdmissionAction::class)->handle($fixture['enrollment'], $type, [], $user);
        $this->assertTrue($admissionResult['allowed']);
        $this->assertSame([], $admissionResult['blocking_errors']);
        $admission = $admissionResult['admission'];
        $this->assertSame(ExamAdmissionStatus::Ready, $admission->status);
        $this->assertGreaterThanOrEqual(9, app(BuildExamAdmissionChecklistAction::class)->handle($admission, $type)->count());

        $admission = app(BlockExamAdmissionAction::class)->handle($admission, tkey('exams.validation.enrollment_cannot_take_exam'), $user);
        $this->assertSame(ExamAdmissionStatus::Blocked, $admission->status);
        $admission = app(ApproveExamAdmissionAction::class)->handle($admission, $user);
        $this->assertSame(ExamAdmissionStatus::Ready, $admission->status);

        $participant = app(AddStudentToExamSessionAction::class)->handle($session, $fixture['student'], $fixture['enrollment'], $user);
        $this->assertTrue($participant->admitted);
        $this->assertSame(1, $session->refresh()->seats_taken);
        $this->assertSame(1, app(GenerateExamAttemptNumberAction::class)->handle($fixture['enrollment'], $type));

        $attempt = app(CreateExamAttemptAction::class)->handle($session, $fixture['student'], $fixture['enrollment'], [], $user);
        $this->assertSame(1, $attempt->attempt_no);

        $attempt = app(StartExamAttemptAction::class)->handle($attempt, $user);
        $this->assertSame('in_progress', $attempt->statusRecord->code);

        $attempt = app(CompleteExamAttemptAction::class)->handle($attempt, [
            'score' => 58,
            'max_score' => 100,
            'passed' => false,
            'examiner_comment' => 'Needs more practice',
        ], $user);
        $this->assertFalse($attempt->passed);
        $this->assertSame(LegacyExamAttemptStatus::Failed, $attempt->status);

        $result = app(RecordExamResultAction::class)->handle($attempt, [
            'result_status_id' => $needsRetake->id,
            'score' => 58,
            'max_score' => 100,
            'passed' => false,
        ], $user);
        $this->assertSame($needsRetake->id, $result->result_status_id);

        $attempt = app(MarkExamFailedAction::class)->handle($attempt, ['score' => 55, 'max_score' => 100], $user);
        $this->assertFalse($attempt->passed);

        $retakeAttempt = app(CreateExamRetakeAction::class)->handle($attempt, [
            'exam_session_id' => $session->id,
            'next_eligible_at' => now()->addWeek(),
        ], $user);
        $retake = app(ScheduleExamRetakeAction::class)->handle($attempt, $retakeAttempt, $user, now()->addWeek());
        $this->assertSame($retakeAttempt->id, $retake->new_attempt_id);

        $retakeAttempt = app(MarkExamPassedAction::class)->handle($retakeAttempt, ['score' => 95, 'max_score' => 100], $user);
        $this->assertTrue($retakeAttempt->passed);

        $noShowAttempt = app(CreateExamAttemptAction::class)->handle($session, $fixture['student'], $fixture['enrollment'], [], $user);
        $noShowAttempt = app(MarkExamAttemptNoShowAction::class)->handle($noShowAttempt, $user);
        $this->assertTrue($noShowAttempt->no_show);

        $cancelledAttempt = app(CreateExamAttemptAction::class)->handle($session, $fixture['student'], $fixture['enrollment'], [], $user);
        $cancelledAttempt = app(CancelExamAttemptAction::class)->handle($cancelledAttempt, $user, 'Manual cancellation');
        $this->assertSame(LegacyExamAttemptStatus::Cancelled, $cancelledAttempt->status);

        $activity = app(AddExamActivityAction::class)->handle([
            'exam_session_id' => $session->id,
            'attempt_id' => $attempt->id,
            'student_id' => $fixture['student']->id,
            'enrollment_id' => $fixture['enrollment']->id,
            'type' => 'manual_note',
        ], $user);
        $this->assertSame($attempt->id, $activity->attempt_id);

        $this->assertTrue(app(RemoveStudentFromExamSessionAction::class)->handle($session, $fixture['student'], $fixture['enrollment'], $user));
        $this->assertSame(0, $session->refresh()->seats_taken);

        $cancelSession = app(CreateExamSessionAction::class)->handle([
            'type_id' => $type->id,
            'status_id' => $scheduled->id,
            'scheduled_at' => now()->addDays(14),
            'capacity' => 1,
        ], $user);
        $cancelSession = app(CancelExamSessionAction::class)->handle($cancelSession, $user, 'Weather');
        $this->assertSame('cancelled', $cancelSession->statusRecord->code);
        $this->assertGreaterThanOrEqual(1, ExamResult::query()->count());
        $this->assertGreaterThanOrEqual(1, ExamRetake::query()->count());
        $this->assertGreaterThanOrEqual(10, ExamActivity::query()->count());
    }

    public function test_exam_rules_return_translated_messages(): void
    {
        $fixture = $this->examFixture(false);
        $internalTheory = ExamTypeModel::query()->where('code', 'internal_theory')->firstOrFail();
        $stateTheory = ExamTypeModel::query()->where('code', 'official_theory_placeholder')->firstOrFail();
        $scheduled = ExamStatusModel::query()->where('code', 'scheduled')->firstOrFail();
        $open = ExamStatusModel::query()->where('code', 'open')->firstOrFail();
        $passedStatus = ExamAttemptStatusModel::query()->where('code', 'passed')->firstOrFail();
        $plannedStatus = ExamAttemptStatusModel::query()->where('code', 'planned')->firstOrFail();
        $inProgressStatus = ExamAttemptStatusModel::query()->where('code', 'in_progress')->firstOrFail();

        $inactiveType = ExamTypeModel::factory()->internalTheory()->create(['code' => 'inactive_exam_type', 'is_active' => false]);
        $inactiveStatus = ExamStatusModel::factory()->scheduled()->create(['code' => 'inactive_exam_status', 'is_active' => false]);
        $session = ExamSession::factory()->create([
            'type_id' => $internalTheory->id,
            'status_id' => $scheduled->id,
            'status' => LegacyExamSessionStatus::Planned,
            'capacity' => 1,
            'seats_taken' => 1,
        ]);
        $participant = ExamParticipant::factory()->create([
            'exam_session_id' => $session->id,
            'student_id' => $fixture['student']->id,
            'enrollment_id' => $fixture['enrollment']->id,
        ]);

        $passedAttempt = ExamAttempt::factory()->passed()->create([
            'enrollment_id' => $fixture['enrollment']->id,
            'student_id' => $fixture['student']->id,
            'student_profile_id' => $fixture['student']->id,
            'exam_type' => LegacyExamType::InternalPractical,
            'status_id' => $passedStatus->id,
        ]);
        $plannedAttempt = ExamAttempt::factory()->create([
            'enrollment_id' => $fixture['enrollment']->id,
            'student_id' => $fixture['student']->id,
            'student_profile_id' => $fixture['student']->id,
            'status_id' => $plannedStatus->id,
        ]);
        $inProgressAttempt = ExamAttempt::factory()->create([
            'enrollment_id' => $fixture['enrollment']->id,
            'student_id' => $fixture['student']->id,
            'student_profile_id' => $fixture['student']->id,
            'status_id' => $inProgressStatus->id,
            'status' => LegacyExamAttemptStatus::InProgress,
            'started_at' => now(),
        ]);

        $cases = [
            ['type' => $inactiveType->id, 'rule' => new ActiveExamTypeRule, 'attribute' => 'type', 'key' => 'active_exam_type'],
            ['status' => $inactiveStatus->id, 'rule' => new ActiveExamStatusRule, 'attribute' => 'status', 'key' => 'active_exam_status'],
            ['status_id' => $open->id, 'rule' => new ValidExamSessionStatusTransitionRule($session->forceFill(['status_id' => ExamStatusModel::query()->where('code', 'completed')->value('id')])), 'attribute' => 'status_id', 'key' => 'invalid_session_status_transition'],
            ['session' => $session->id, 'rule' => new ExamSessionCapacityRule, 'attribute' => 'session', 'key' => 'session_capacity_unavailable'],
            ['student' => $fixture['student']->id, 'rule' => new StudentCanJoinExamSessionRule($session, $fixture['enrollment']), 'attribute' => 'student', 'key' => 'student_cannot_join_session'],
            ['enrollment' => $fixture['enrollment']->id, 'rule' => new EnrollmentCanTakeExamRule($internalTheory), 'attribute' => 'enrollment', 'key' => 'enrollment_cannot_take_exam'],
            ['enrollment' => $fixture['enrollment']->id, 'rule' => new RequiredDocumentsAcceptedRule, 'attribute' => 'enrollment', 'key' => 'documents_required'],
            ['enrollment' => $fixture['enrollment']->id, 'rule' => new RequiredPaymentsCompletedRule, 'attribute' => 'enrollment', 'key' => 'payment_required'],
            ['enrollment' => $fixture['enrollment']->id, 'rule' => new RequiredTheoryHoursRule(40), 'attribute' => 'enrollment', 'key' => 'theory_hours_required'],
            ['enrollment' => $fixture['enrollment']->id, 'rule' => new RequiredPracticeHoursRule(30), 'attribute' => 'enrollment', 'key' => 'practice_hours_required'],
            ['enrollment' => $fixture['enrollment']->id, 'rule' => new InternalExamPassedRule($stateTheory), 'attribute' => 'enrollment', 'key' => 'internal_exam_required'],
            ['enrollment' => StudentEnrollment::factory()->cancelled()->create()->id, 'rule' => new EnrollmentActiveForExamRule, 'attribute' => 'enrollment', 'key' => 'enrollment_inactive'],
            ['student' => Student::factory()->blocked()->create()->id, 'rule' => new StudentActiveForExamRule, 'attribute' => 'student', 'key' => 'student_inactive'],
            ['attempt' => $passedAttempt->id, 'rule' => new ExamAttemptCanStartRule, 'attribute' => 'attempt', 'key' => 'attempt_cannot_start'],
            ['attempt' => $plannedAttempt->id, 'rule' => new ExamAttemptCanCompleteRule, 'attribute' => 'attempt', 'key' => 'attempt_cannot_complete'],
            ['score' => 120, 'max_score' => 100, 'rule' => new ExamResultScoreRule, 'attribute' => 'score', 'key' => 'result_score_invalid'],
            ['attempt' => $passedAttempt->id, 'rule' => new ExamRetakeAllowedRule, 'attribute' => 'attempt', 'key' => 'retake_not_allowed'],
        ];

        foreach ($cases as $case) {
            $attribute = $case['attribute'];
            $validator = Validator::make($case, [$attribute => [$case['rule']]]);
            $this->assertTrue($validator->fails(), $case['key']);
            $this->assertSame(tkey('exams.validation.'.$case['key']), $validator->errors()->first($attribute));
        }

        $this->assertTrue(app(ExamSession::class)->newQuery()->whereKey($participant->exam_session_id)->exists());
        $this->assertTrue(Validator::make(['attempt' => $inProgressAttempt->id], ['attempt' => [new ExamAttemptCanCompleteRule]])->passes());
    }

    public function test_required_exam_form_requests_authorize_and_use_exam_validation_messages(): void
    {
        $requests = [
            StoreExamSessionRequest::class => 'exams.manage_sessions',
            UpdateExamSessionRequest::class => 'exams.manage_sessions',
            ChangeExamSessionStatusRequest::class => 'exams.manage_sessions',
            AddStudentToExamSessionRequest::class => 'exams.manage_sessions',
            CheckExamAdmissionRequest::class => 'exams.manage_admissions',
            CreateExamAttemptRequest::class => 'exams.record_results',
            CompleteExamAttemptRequest::class => 'exams.record_results',
            RecordExamResultRequest::class => 'exams.record_results',
            CreateExamRetakeRequest::class => 'exams.schedule_retakes',
        ];

        foreach ($requests as $requestClass => $permission) {
            $request = $requestClass::create('/', 'POST');
            $request->setUserResolver(fn (): User => $this->userWithPermissions([$permission]));

            $this->assertTrue($request->authorize(), $requestClass);
            $this->assertSame(tkey('exams.validation.required'), $request->messages()['required']);
            $this->assertSame(tkey('exams.validation.exists'), $request->messages()['exists']);
        }
    }

    /**
     * @return array{
     *     branch: Branch,
     *     instructor: Instructor,
     *     program: TrainingProgram,
     *     group: TrainingGroup,
     *     student: Student,
     *     enrollment: StudentEnrollment
     * }
     */
    private function examFixture(bool $ready = true): array
    {
        $branch = Branch::factory()->create();
        $instructor = Instructor::factory()->create(['branch_id' => $branch->id]);
        $program = TrainingProgram::factory()->create();
        $group = TrainingGroup::factory()->create([
            'branch_id' => $branch->id,
            'training_program_id' => $program->id,
            'course_id' => $program->id,
            'instructor_id' => $instructor->id,
        ]);
        $student = Student::factory()->active()->create(['branch_id' => $branch->id]);
        $enrollment = StudentEnrollment::factory()->active()->create([
            'student_profile_id' => $student->id,
            'training_program_id' => $program->id,
            'branch_id' => $branch->id,
            'training_group_id' => $group->id,
            'instructor_id' => $instructor->id,
            'payment_status' => $ready ? PaymentStatus::Paid->value : 'waiting',
            'total_theory_hours' => 40,
            'completed_theory_hours' => $ready ? 40 : 0,
            'total_practice_hours' => 30,
            'completed_practice_hours' => $ready ? 30 : 0,
        ]);

        if ($ready) {
            foreach (['id_card', 'medical_certificate', 'training_contract'] as $documentType) {
                StudentDocument::factory()->create([
                    'student_profile_id' => $student->id,
                    'enrollment_id' => $enrollment->id,
                    'document_type' => $documentType,
                    'status' => DocumentStatus::Verified,
                ]);
            }

            Payment::factory()->create([
                'student_profile_id' => $student->id,
                'enrollment_id' => $enrollment->id,
                'amount_cents' => $enrollment->contracted_price_cents,
                'status' => PaymentStatus::Paid,
            ]);
        }

        return compact('branch', 'instructor', 'program', 'group', 'student', 'enrollment');
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
