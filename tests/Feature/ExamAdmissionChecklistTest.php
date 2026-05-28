<?php

namespace Tests\Feature;

use App\Actions\AddStudentToExamSessionAction;
use App\Actions\ApproveExamAdmissionAction;
use App\Actions\BlockExamAdmissionAction;
use App\Actions\CheckExamAdmissionAction;
use App\Actions\RecheckExamSessionAdmissionsAction;
use App\Enums\DocumentStatus;
use App\Enums\ExamAdmissionStatus;
use App\Enums\ExamParticipantStatus;
use App\Enums\ExamSessionStatus as LegacyExamSessionStatus;
use App\Enums\ExamType as LegacyExamType;
use App\Enums\PaymentStatus;
use App\Models\ExamAdmissionRule;
use App\Models\ExamAttempt;
use App\Models\ExamSession;
use App\Models\ExamStatus as ExamStatusModel;
use App\Models\ExamType as ExamTypeModel;
use App\Models\Payment;
use App\Models\Student;
use App\Models\StudentDocument;
use App\Models\StudentEnrollment;
use App\Models\User;
use Database\Seeders\ExamDictionarySeeder;
use Database\Seeders\ExamTranslationSeeder;
use Database\Seeders\LanguageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExamAdmissionChecklistTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app()->setLocale('en');
        $this->seed([LanguageSeeder::class, ExamTranslationSeeder::class, ExamDictionarySeeder::class]);
    }

    public function test_admission_passes_when_all_checks_pass(): void
    {
        $fixture = $this->admissionFixture();
        $type = $this->examType('internal_theory');

        $result = app(CheckExamAdmissionAction::class)->handle($fixture['enrollment'], $type, [], $fixture['user']);

        $this->assertTrue($result['allowed']);
        $this->assertSame([], $result['blocking_errors']);
        $this->assertSame(ExamAdmissionStatus::Ready, $result['admission']->status);
        $this->assertSame([
            'documents',
            'payments',
            'theory_hours',
            'practice_hours',
            'internal_theory',
            'internal_practical',
            'enrollment_status',
            'student_status',
            'manual_review',
        ], collect($result['checklist'])->pluck('key')->all());

        $this->assertDatabaseHas('exam_admission_checklist_items', [
            'exam_admission_id' => $result['admission']->id,
            'key' => 'documents',
            'required' => true,
            'passed' => true,
            'message_key' => 'exams.admissions.checks.documents_passed',
        ]);

        $session = $this->sessionForType($type);
        $participant = app(AddStudentToExamSessionAction::class)->handle($session, $fixture['student'], $fixture['enrollment'], $fixture['user']);

        $this->assertTrue($participant->admitted);
        $this->assertSame(ExamParticipantStatus::Admitted->value, $participant->status);
        $this->assertDatabaseHas('exam_checklist_items', [
            'exam_session_id' => $session->id,
            'student_id' => $fixture['student']->id,
            'key' => 'documents',
            'passed' => true,
        ]);
    }

    public function test_missing_documents_block_admission(): void
    {
        $fixture = $this->admissionFixture(withDocuments: false);

        $result = app(CheckExamAdmissionAction::class)->handle($fixture['enrollment'], $this->examType('internal_theory'), [], $fixture['user']);

        $this->assertFalse($result['allowed']);
        $this->assertContains('exams.validation.documents_required', $result['blocking_errors']);
        $this->assertSame(ExamAdmissionStatus::Blocked, $result['admission']->status);
        $this->assertChecklistFailed($result, 'documents', 'exams.validation.documents_required');

        $session = $this->sessionForType($this->examType('internal_theory'));
        $participant = app(AddStudentToExamSessionAction::class)->handle($session, $fixture['student'], $fixture['enrollment'], $fixture['user']);

        $this->assertFalse($participant->admitted);
        $this->assertSame('exams.validation.documents_required', $participant->block_reason);
    }

    public function test_overdue_debt_blocks_admission(): void
    {
        $fixture = $this->admissionFixture(withPayment: false);

        $result = app(CheckExamAdmissionAction::class)->handle($fixture['enrollment'], $this->examType('internal_theory'), [], $fixture['user']);

        $this->assertFalse($result['allowed']);
        $this->assertContains('exams.validation.payment_required', $result['blocking_errors']);
        $this->assertChecklistFailed($result, 'payments', 'exams.validation.payment_required');
    }

    public function test_insufficient_theory_hours_block_admission(): void
    {
        $fixture = $this->admissionFixture(['completed_theory_hours' => 12]);

        $result = app(CheckExamAdmissionAction::class)->handle($fixture['enrollment'], $this->examType('internal_theory'), [], $fixture['user']);

        $this->assertFalse($result['allowed']);
        $this->assertContains('exams.validation.theory_hours_required', $result['blocking_errors']);
        $this->assertChecklistFailed($result, 'theory_hours', 'exams.validation.theory_hours_required');
    }

    public function test_insufficient_practice_hours_block_practical_admission(): void
    {
        $fixture = $this->admissionFixture(['completed_practice_hours' => 10]);

        $result = app(CheckExamAdmissionAction::class)->handle($fixture['enrollment'], $this->examType('internal_practical'), [], $fixture['user']);

        $this->assertFalse($result['allowed']);
        $this->assertContains('exams.validation.practice_hours_required', $result['blocking_errors']);
        $this->assertChecklistFailed($result, 'practice_hours', 'exams.validation.practice_hours_required');
    }

    public function test_internal_exam_requirements_work_when_configured(): void
    {
        $fixture = $this->admissionFixture();
        $internalPractical = $this->examType('internal_practical');
        $officialPractical = $this->examType('official_practical_placeholder');
        $this->ruleFor($internalPractical)->forceFill(['require_internal_exam_passed' => true])->save();

        $practicalResult = app(CheckExamAdmissionAction::class)->handle($fixture['enrollment'], $internalPractical, [], $fixture['user']);

        $this->assertFalse($practicalResult['allowed']);
        $this->assertChecklistFailed($practicalResult, 'internal_theory', 'exams.validation.internal_exam_required');

        ExamAttempt::factory()->passed()->create([
            'enrollment_id' => $fixture['enrollment']->id,
            'student_id' => $fixture['student']->id,
            'student_profile_id' => $fixture['student']->id,
            'exam_type' => LegacyExamType::InternalTheory,
        ]);

        $practicalResult = app(CheckExamAdmissionAction::class)->handle($fixture['enrollment'], $internalPractical, [], $fixture['user']);
        $this->assertTrue($practicalResult['allowed']);

        $officialResult = app(CheckExamAdmissionAction::class)->handle($fixture['enrollment'], $officialPractical, [], $fixture['user']);
        $this->assertFalse($officialResult['allowed']);
        $this->assertChecklistFailed($officialResult, 'internal_practical', 'exams.validation.internal_exam_required');

        ExamAttempt::factory()->passed()->create([
            'enrollment_id' => $fixture['enrollment']->id,
            'student_id' => $fixture['student']->id,
            'student_profile_id' => $fixture['student']->id,
            'exam_type' => LegacyExamType::InternalPractical,
        ]);

        $officialResult = app(CheckExamAdmissionAction::class)->handle($fixture['enrollment'], $officialPractical, [], $fixture['user']);
        $this->assertTrue($officialResult['allowed']);
    }

    public function test_manual_approval_allows_admission_with_warnings(): void
    {
        $fixture = $this->admissionFixture(withDocuments: false);
        $type = $this->examType('internal_theory');
        $blocked = app(CheckExamAdmissionAction::class)->handle($fixture['enrollment'], $type, [], $fixture['user']);

        app(ApproveExamAdmissionAction::class)->handle($blocked['admission'], $fixture['user']);
        $result = app(CheckExamAdmissionAction::class)->handle($fixture['enrollment'], $type, [], $fixture['user']);

        $this->assertTrue($result['allowed']);
        $this->assertContains('exams.validation.documents_required', $result['warnings']);
        $this->assertChecklistPassed($result, 'manual_review', 'exams.admissions.checks.manual_approved');
    }

    public function test_manual_block_and_session_recheck_block_participant(): void
    {
        $fixture = $this->admissionFixture();
        $type = $this->examType('internal_theory');
        $initial = app(CheckExamAdmissionAction::class)->handle($fixture['enrollment'], $type, [], $fixture['user']);
        app(BlockExamAdmissionAction::class)->handle($initial['admission'], 'exams.validation.manual_blocked', $fixture['user']);

        $session = $this->sessionForType($type);
        $participant = app(AddStudentToExamSessionAction::class)->handle($session, $fixture['student'], $fixture['enrollment'], $fixture['user']);
        $results = app(RecheckExamSessionAdmissionsAction::class)->handle($session, $fixture['user']);

        $this->assertFalse($participant->refresh()->admitted);
        $this->assertSame('exams.validation.manual_blocked', $participant->block_reason);
        $this->assertFalse($results->first()['allowed']);
        $this->assertChecklistFailed(
            app(CheckExamAdmissionAction::class)->handle($fixture['enrollment'], $type, [], $fixture['user']),
            'manual_review',
            'exams.validation.manual_blocked',
        );
    }

    /**
     * @param  array<string, mixed>  $enrollmentOverrides
     * @return array{student: Student, enrollment: StudentEnrollment, user: User}
     */
    private function admissionFixture(array $enrollmentOverrides = [], bool $withDocuments = true, bool $withPayment = true): array
    {
        $user = User::factory()->create();
        $student = Student::factory()->active()->create();
        $enrollment = StudentEnrollment::factory()->active()->create([
            'student_profile_id' => $student->id,
            'payment_status' => $withPayment ? PaymentStatus::Paid->value : 'waiting',
            'contracted_price_cents' => 129000,
            'paid_cents' => $withPayment ? 129000 : 0,
            'total_theory_hours' => 40,
            'completed_theory_hours' => 40,
            'total_practice_hours' => 30,
            'completed_practice_hours' => 30,
            ...$enrollmentOverrides,
        ]);

        if ($withDocuments) {
            foreach (['id_card', 'medical_certificate', 'training_contract'] as $documentType) {
                StudentDocument::factory()->create([
                    'student_profile_id' => $student->id,
                    'enrollment_id' => $enrollment->id,
                    'document_type' => $documentType,
                    'status' => DocumentStatus::Verified,
                ]);
            }
        }

        if ($withPayment) {
            Payment::factory()->create([
                'student_profile_id' => $student->id,
                'enrollment_id' => $enrollment->id,
                'amount_cents' => 129000,
                'status' => PaymentStatus::Paid,
            ]);
        }

        return compact('student', 'enrollment', 'user');
    }

    private function examType(string $code): ExamTypeModel
    {
        return ExamTypeModel::query()->where('code', $code)->firstOrFail();
    }

    private function ruleFor(ExamTypeModel $type): ExamAdmissionRule
    {
        return ExamAdmissionRule::query()
            ->where('exam_type_id', $type->id)
            ->whereNull('course_id')
            ->whereNull('course_category_id')
            ->firstOrFail();
    }

    private function sessionForType(ExamTypeModel $type): ExamSession
    {
        $scheduled = ExamStatusModel::query()->where('code', 'scheduled')->firstOrFail();

        return ExamSession::factory()->create([
            'type_id' => $type->id,
            'status_id' => $scheduled->id,
            'exam_type' => $type->is_practical ? LegacyExamType::InternalPractical : LegacyExamType::InternalTheory,
            'status' => LegacyExamSessionStatus::Planned,
            'capacity' => 4,
            'seats_taken' => 0,
        ]);
    }

    /**
     * @param  array{checklist: array<int, array<string, mixed>>}  $result
     */
    private function assertChecklistFailed(array $result, string $key, string $messageKey): void
    {
        $item = collect($result['checklist'])->firstWhere('key', $key);

        $this->assertNotNull($item);
        $this->assertFalse($item['passed']);
        $this->assertSame('failed', $item['status']);
        $this->assertSame($messageKey, $item['message_key']);
    }

    /**
     * @param  array{checklist: array<int, array<string, mixed>>}  $result
     */
    private function assertChecklistPassed(array $result, string $key, string $messageKey): void
    {
        $item = collect($result['checklist'])->firstWhere('key', $key);

        $this->assertNotNull($item);
        $this->assertTrue($item['passed']);
        $this->assertSame('passed', $item['status']);
        $this->assertSame($messageKey, $item['message_key']);
    }
}
