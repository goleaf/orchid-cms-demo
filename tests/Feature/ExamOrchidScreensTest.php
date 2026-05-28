<?php

namespace Tests\Feature;

use App\Enums\ExamAdmissionStatus;
use App\Enums\ExamAttemptStatus as LegacyExamAttemptStatus;
use App\Enums\ExamSessionStatus as LegacyExamSessionStatus;
use App\Enums\ExamType as LegacyExamType;
use App\Models\Branch;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\ExamActivity;
use App\Models\ExamAdmission;
use App\Models\ExamAttempt;
use App\Models\ExamAttemptStatus;
use App\Models\ExamChecklistItem;
use App\Models\ExamParticipant;
use App\Models\ExamResult;
use App\Models\ExamResultStatus;
use App\Models\ExamRetake;
use App\Models\ExamSession;
use App\Models\ExamStatus;
use App\Models\ExamType;
use App\Models\Instructor;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\TrainingGroup;
use App\Models\User;
use Database\Seeders\ExamDictionarySeeder;
use Database\Seeders\ExamTranslationSeeder;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\SystemTranslationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExamOrchidScreensTest extends TestCase
{
    use RefreshDatabase;

    public function test_exam_orchid_screens_are_accessible_with_matching_permissions(): void
    {
        $this->seedExamUiFoundation();
        $records = $this->examRecords();
        $actor = $this->userWithPermissions([
            'platform.exams',
            'exams.sessions.view',
            'exams.sessions.create',
            'exams.sessions.update',
            'exams.sessions.cancel',
            'exams.admissions.check',
            'exams.admissions.approve',
            'exams.admissions.block',
            'exams.attempts.view',
            'exams.attempts.start',
            'exams.attempts.complete',
            'exams.attempts.cancel',
            'exams.results.view',
            'exams.results.record',
            'exams.results.update',
            'exams.retakes.view',
            'exams.retakes.create',
            'exams.dictionaries.manage',
            'exams.export',
        ]);

        $this->actingAs($actor)
            ->get(route('platform.exams.sessions'))
            ->assertOk()
            ->assertSee('EXM-UI-001')
            ->assertSee(tkey('exams.sessions.title'));

        $this->actingAs($actor)
            ->get(route('platform.exams.sessions.create'))
            ->assertOk()
            ->assertSee(tkey('exams.sessions.create_title'));

        $this->actingAs($actor)
            ->get(route('platform.exams.sessions.edit', $records['session']))
            ->assertOk()
            ->assertSee('EXM-UI-001')
            ->assertSee(tkey('exams.sections.participants'));

        $this->actingAs($actor)
            ->get(route('platform.exams.admissions'))
            ->assertOk()
            ->assertSee(tkey('exams.admissions.title'))
            ->assertSee('Exam Orchid Student');

        $this->actingAs($actor)
            ->get(route('platform.exams.attempts'))
            ->assertOk()
            ->assertSee(tkey('exams.attempts.title'))
            ->assertSee('Exam Orchid Student');

        $this->actingAs($actor)
            ->get(route('platform.exams.attempts.edit', $records['attempt']))
            ->assertOk()
            ->assertSee(tkey('exams.attempts.edit_title'));

        $this->actingAs($actor)
            ->get(route('platform.exams.results'))
            ->assertOk()
            ->assertSee(tkey('exams.results.title'))
            ->assertSee('Exam Orchid Student');

        $this->actingAs($actor)
            ->get(route('platform.exams.retakes'))
            ->assertOk()
            ->assertSee(tkey('exams.retakes.title'));

        foreach ([
            'platform.exams.types' => 'exams.dictionaries.types.title',
            'platform.exams.statuses' => 'exams.dictionaries.statuses.title',
            'platform.exams.attempt-statuses' => 'exams.dictionaries.attempt_statuses.title',
            'platform.exams.result-statuses' => 'exams.dictionaries.result_statuses.title',
        ] as $route => $titleKey) {
            $this->actingAs($actor)
                ->get(route($route))
                ->assertOk()
                ->assertSee(tkey($titleKey));
        }
    }

    public function test_exam_orchid_screens_require_specific_permissions(): void
    {
        $this->seedExamUiFoundation();
        $viewer = $this->userWithPermissions(['platform.exams', 'exams.sessions.view']);

        $this->actingAs($this->userWithPermissions())
            ->get(route('platform.exams.sessions'))
            ->assertForbidden();

        $this->actingAs($viewer)
            ->get(route('platform.exams.results'))
            ->assertForbidden();

        $this->actingAs($this->userWithPermissions(['platform.exams', 'exams.results.view']))
            ->get(route('platform.exams.results'))
            ->assertOk();

        $this->actingAs($viewer)
            ->get(route('platform.exams.types'))
            ->assertForbidden();

        $this->actingAs($this->userWithPermissions(['platform.exams', 'exams.dictionaries.manage']))
            ->get(route('platform.exams.types'))
            ->assertOk();
    }

    /**
     * @return array<string, object>
     */
    private function examRecords(): array
    {
        $branch = Branch::factory()->create();
        $category = CourseCategory::factory()->create();
        $course = Course::factory()->create(['course_category_id' => $category->id]);
        $instructor = Instructor::factory()->create(['branch_id' => $branch->id]);
        $group = TrainingGroup::factory()->create([
            'branch_id' => $branch->id,
            'training_program_id' => $course->id,
            'course_id' => $course->id,
            'course_category_id' => $category->id,
            'instructor_id' => $instructor->id,
        ]);
        $student = Student::factory()->active()->create([
            'branch_id' => $branch->id,
            'first_name' => 'Exam',
            'last_name' => 'Orchid Student',
            'full_name' => 'Exam Orchid Student',
        ]);
        $enrollment = StudentEnrollment::factory()->active()->create([
            'student_profile_id' => $student->id,
            'training_program_id' => $course->id,
            'course_category_id' => $category->id,
            'branch_id' => $branch->id,
            'training_group_id' => $group->id,
            'instructor_id' => $instructor->id,
        ]);
        $examiner = User::factory()->create(['name' => 'Examiner User']);

        $type = ExamType::query()->where('code', 'internal_theory')->firstOrFail();
        $sessionStatus = ExamStatus::query()->where('code', 'scheduled')->firstOrFail();
        $attemptStatus = ExamAttemptStatus::query()->where('code', 'passed')->firstOrFail();
        $resultStatus = ExamResultStatus::query()->where('code', 'passed')->firstOrFail();
        $scheduledAt = now()->addDays(2)->setTime(10, 0);

        $session = ExamSession::factory()->create([
            'exam_number' => 'EXM-UI-001',
            'type_id' => $type->id,
            'status_id' => $sessionStatus->id,
            'branch_id' => $branch->id,
            'group_id' => $group->id,
            'training_group_id' => $group->id,
            'training_program_id' => $course->id,
            'instructor_id' => $instructor->id,
            'examiner_id' => $examiner->id,
            'exam_type' => LegacyExamType::InternalTheory,
            'status' => LegacyExamSessionStatus::Planned,
            'scheduled_at' => $scheduledAt,
            'starts_at' => $scheduledAt,
            'capacity' => 12,
        ]);

        ExamParticipant::factory()->create([
            'exam_session_id' => $session->id,
            'student_id' => $student->id,
            'enrollment_id' => $enrollment->id,
            'status' => 'admitted',
            'admitted' => true,
        ]);

        $admission = ExamAdmission::factory()->ready()->forEnrollment($enrollment)->create([
            'admission_type' => LegacyExamType::InternalTheory,
            'status' => ExamAdmissionStatus::Ready,
        ]);

        $attempt = ExamAttempt::factory()->passed()->create([
            'exam_admission_id' => $admission->id,
            'exam_session_id' => $session->id,
            'enrollment_id' => $enrollment->id,
            'student_id' => $student->id,
            'student_profile_id' => $student->id,
            'training_group_id' => $group->id,
            'training_program_id' => $course->id,
            'instructor_id' => $instructor->id,
            'status_id' => $attemptStatus->id,
            'status' => LegacyExamAttemptStatus::Passed,
            'attempt_number' => 1,
            'attempt_no' => 1,
        ]);

        $result = ExamResult::factory()->passed()->create([
            'attempt_id' => $attempt->id,
            'result_status_id' => $resultStatus->id,
            'decided_by_id' => $examiner->id,
        ]);

        $failedAttempt = ExamAttempt::factory()->failed()->create([
            'exam_admission_id' => $admission->id,
            'exam_session_id' => $session->id,
            'enrollment_id' => $enrollment->id,
            'student_id' => $student->id,
            'student_profile_id' => $student->id,
            'training_group_id' => $group->id,
            'training_program_id' => $course->id,
            'instructor_id' => $instructor->id,
        ]);

        ExamRetake::factory()->create([
            'student_id' => $student->id,
            'enrollment_id' => $enrollment->id,
            'previous_attempt_id' => $failedAttempt->id,
            'new_attempt_id' => null,
            'status' => 'planned',
        ]);

        ExamChecklistItem::factory()->forAttempt($attempt)->passed()->create([
            'exam_session_id' => $session->id,
            'student_id' => $student->id,
            'enrollment_id' => $enrollment->id,
            'key' => 'identity_document',
        ]);

        ExamActivity::factory()->forAttempt($attempt)->create([
            'exam_session_id' => $session->id,
            'user_id' => $examiner->id,
            'type' => 'result_recorded',
        ]);

        return [
            'session' => $session,
            'admission' => $admission,
            'attempt' => $attempt,
            'result' => $result,
        ];
    }

    private function seedExamUiFoundation(): void
    {
        $this->seed([
            LanguageSeeder::class,
            SystemTranslationSeeder::class,
            ExamTranslationSeeder::class,
            ExamDictionarySeeder::class,
        ]);
    }

    /**
     * @param  array<int, string>  $permissions
     */
    private function userWithPermissions(array $permissions = []): User
    {
        return User::factory()->create([
            'permissions' => collect(['platform.index', 'platform.main'])
                ->merge($permissions)
                ->mapWithKeys(fn (string $permission): array => [$permission => true])
                ->all(),
        ]);
    }
}
