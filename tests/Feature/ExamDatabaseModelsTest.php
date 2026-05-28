<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\ExamActivity;
use App\Models\ExamAdmissionRule;
use App\Models\ExamAttempt;
use App\Models\ExamAttemptStatus as ExamAttemptStatusModel;
use App\Models\ExamChecklistItem;
use App\Models\ExamParticipant;
use App\Models\ExamResult;
use App\Models\ExamResultStatus as ExamResultStatusModel;
use App\Models\ExamRetake;
use App\Models\ExamSession;
use App\Models\ExamStatus as ExamStatusModel;
use App\Models\ExamType as ExamTypeModel;
use App\Models\Instructor;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\TrainingGroup;
use App\Models\User;
use App\Models\Vehicle;
use Database\Seeders\ExamDictionarySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ExamDatabaseModelsTest extends TestCase
{
    use RefreshDatabase;

    public function test_requested_exam_database_tables_and_columns_exist(): void
    {
        foreach ([
            'exam_types',
            'exam_statuses',
            'exam_attempt_statuses',
            'exam_result_statuses',
            'exam_admission_rules',
            'exam_sessions',
            'exam_participants',
            'exam_attempts',
            'exam_results',
            'exam_retakes',
            'exam_checklist_items',
            'exam_activities',
        ] as $table) {
            $this->assertTrue(Schema::hasTable($table), $table);
        }

        $columns = [
            'exam_types' => ['code', 'name_translations', 'description_translations', 'is_internal', 'is_official', 'is_theory', 'is_practical', 'is_active'],
            'exam_statuses' => ['code'],
            'exam_attempt_statuses' => ['code'],
            'exam_result_statuses' => ['code'],
            'exam_admission_rules' => ['exam_type_id', 'course_id', 'course_category_id', 'required_theory_hours', 'required_practice_hours', 'require_documents', 'require_no_debt', 'require_internal_exam_passed', 'is_active'],
            'exam_sessions' => ['exam_number', 'type_id', 'status_id', 'branch_id', 'group_id', 'scheduled_at', 'location', 'examiner_id', 'vehicle_id', 'classroom_id', 'capacity', 'notes'],
            'exam_participants' => ['exam_session_id', 'student_id', 'enrollment_id', 'status', 'admitted', 'block_reason', 'registered_at'],
            'exam_attempts' => ['attempt_number', 'exam_session_id', 'student_id', 'enrollment_id', 'status_id', 'attempt_no', 'started_at', 'finished_at', 'score', 'passed', 'no_show'],
            'exam_results' => ['attempt_id', 'result_status_id', 'score', 'max_score', 'passed', 'examiner_comment', 'mistakes_summary', 'decided_by_id', 'decided_at'],
            'exam_retakes' => ['student_id', 'enrollment_id', 'previous_attempt_id', 'new_attempt_id', 'reason', 'planned_at', 'status'],
            'exam_checklist_items' => ['exam_session_id', 'attempt_id', 'student_id', 'enrollment_id', 'key', 'title_translations', 'status', 'required'],
            'exam_activities' => ['exam_session_id', 'attempt_id', 'student_id', 'user_id', 'type', 'old_value', 'new_value', 'meta'],
        ];

        foreach ($columns as $table => $tableColumns) {
            foreach ($tableColumns as $column) {
                $this->assertTrue(Schema::hasColumn($table, $column), "{$table}.{$column}");
            }
        }

        foreach (['tenants', 'subscriptions', 'resellers', 'government_exam_syncs', 'exam_question_banks'] as $table) {
            $this->assertFalse(Schema::hasTable($table), $table);
        }
    }

    public function test_exam_models_create_relations_translations_and_scopes(): void
    {
        $branch = Branch::factory()->create();
        $category = CourseCategory::factory()->create();
        $course = Course::factory()->create(['course_category_id' => $category->id]);
        $instructor = Instructor::factory()->create(['branch_id' => $branch->id]);
        $vehicle = Vehicle::factory()->create(['branch_id' => $branch->id, 'instructor_id' => $instructor->id]);
        $group = TrainingGroup::factory()->create([
            'branch_id' => $branch->id,
            'training_program_id' => $course->id,
            'course_id' => $course->id,
            'instructor_id' => $instructor->id,
        ]);
        $student = Student::factory()->active()->create(['branch_id' => $branch->id]);
        $enrollment = StudentEnrollment::factory()->active()->create([
            'student_profile_id' => $student->id,
            'training_program_id' => $course->id,
            'course_category_id' => $category->id,
            'branch_id' => $branch->id,
            'training_group_id' => $group->id,
            'instructor_id' => $instructor->id,
        ]);
        $examiner = User::factory()->create();

        $type = ExamTypeModel::factory()->internalTheory()->create([
            'name_translations' => ['en' => 'Internal theory', 'lt' => 'Vidinis teorijos'],
        ]);
        $sessionStatus = ExamStatusModel::factory()->scheduled()->create();
        $attemptStatus = ExamAttemptStatusModel::factory()->planned()->create();
        $resultStatus = ExamResultStatusModel::factory()->passed()->create();

        $rule = ExamAdmissionRule::factory()
            ->forExamType($type)
            ->forCourse($course)
            ->forCourseCategory($category)
            ->create(['required_theory_hours' => 40, 'required_practice_hours' => 30]);

        $scheduledAt = now()->addDays(3)->setTime(9, 0);
        $session = ExamSession::factory()->create([
            'exam_number' => 'EXM-FOUNDATION-001',
            'type_id' => $type->id,
            'status_id' => $sessionStatus->id,
            'branch_id' => $branch->id,
            'group_id' => $group->id,
            'training_group_id' => $group->id,
            'training_program_id' => $course->id,
            'instructor_id' => $instructor->id,
            'examiner_id' => $examiner->id,
            'vehicle_id' => $vehicle->id,
            'classroom_id' => 1001,
            'scheduled_at' => $scheduledAt,
            'starts_at' => $scheduledAt,
            'location' => 'Main classroom',
            'capacity' => 12,
        ]);

        $participant = ExamParticipant::factory()->create([
            'exam_session_id' => $session->id,
            'student_id' => $student->id,
            'enrollment_id' => $enrollment->id,
            'admitted' => true,
        ]);

        $attempt = ExamAttempt::factory()->create([
            'exam_session_id' => $session->id,
            'enrollment_id' => $enrollment->id,
            'student_id' => $student->id,
            'student_profile_id' => $student->id,
            'status_id' => $attemptStatus->id,
            'attempt_no' => 1,
            'attempt_number' => 1,
            'score' => 88,
            'max_score' => 100,
            'passed' => true,
        ]);

        $result = ExamResult::factory()->create([
            'attempt_id' => $attempt->id,
            'result_status_id' => $resultStatus->id,
            'score' => 88,
            'max_score' => 100,
            'passed' => true,
            'decided_by_id' => $examiner->id,
        ]);

        $newAttempt = ExamAttempt::factory()->create([
            'exam_session_id' => $session->id,
            'enrollment_id' => $enrollment->id,
            'student_id' => $student->id,
            'student_profile_id' => $student->id,
            'attempt_no' => 2,
            'attempt_number' => 2,
        ]);
        $retake = ExamRetake::factory()->create([
            'student_id' => $student->id,
            'enrollment_id' => $enrollment->id,
            'previous_attempt_id' => $attempt->id,
            'new_attempt_id' => $newAttempt->id,
        ]);

        $checklistItem = ExamChecklistItem::factory()->forAttempt($attempt)->passed()->create([
            'key' => 'identity_document',
            'title_translations' => ['en' => 'Identity document', 'lt' => 'Asmens dokumentas'],
        ]);

        $activity = ExamActivity::factory()->forAttempt($attempt)->create([
            'attempt_id' => $attempt->id,
            'student_id' => $student->id,
            'user_id' => $examiner->id,
            'type' => 'result_recorded',
            'old_value' => 'pending',
            'new_value' => 'passed',
            'meta' => ['source' => 'manual'],
        ]);

        $this->assertSame('Internal theory', $type->displayName('en'));
        $this->assertTrue(ExamTypeModel::query()->active()->internal()->theory()->whereKey($type)->exists());
        $this->assertTrue(ExamAdmissionRule::query()->active()->forExamType($type)->forCourse($course)->whereKey($rule)->exists());
        $this->assertTrue($rule->course->is($course));
        $this->assertTrue($rule->courseCategory->is($category));

        $this->assertTrue($session->typeRecord->is($type));
        $this->assertTrue($session->statusRecord->is($sessionStatus));
        $this->assertTrue($session->groupAlias->is($group));
        $this->assertTrue($session->participants->first()->is($participant));
        $this->assertTrue($session->results->first()->is($result));

        $this->assertTrue($attempt->statusRecord->is($attemptStatus));
        $this->assertTrue($attempt->studentAlias->is($student));
        $this->assertTrue($attempt->result->is($result));
        $this->assertSame('Planned', $attempt->displayStatus('en'));

        $this->assertTrue($student->examParticipants()->whereKey($participant)->exists());
        $this->assertTrue($student->examResults()->whereKey($result)->exists());
        $this->assertTrue($enrollment->examRetakes()->whereKey($retake)->exists());
        $this->assertTrue($group->examSessionAliases()->whereKey($session)->exists());
        $this->assertTrue($course->examAdmissionRules()->whereKey($rule)->exists());

        $this->assertSame('Identity document', $checklistItem->displayTitle('en'));
        $this->assertTrue(ExamChecklistItem::query()->required()->passed()->whereKey($checklistItem)->exists());
        $this->assertTrue(ExamResult::query()->passed()->whereKey($result)->exists());
        $this->assertTrue(ExamRetake::query()->linked()->whereKey($retake)->exists());
        $this->assertTrue($activity->attemptAlias->is($attempt));
        $this->assertTrue($activity->studentAlias->is($student));
    }

    public function test_exam_dictionary_seeder_creates_foundation_records_through_factories(): void
    {
        $this->seed(ExamDictionarySeeder::class);

        foreach (['internal_theory', 'internal_practical', 'state_theory', 'state_practical'] as $code) {
            $this->assertDatabaseHas('exam_types', ['code' => $code]);
        }

        foreach (['draft', 'scheduled', 'open', 'in_progress', 'completed', 'cancelled', 'archived'] as $code) {
            $this->assertDatabaseHas('exam_statuses', ['code' => $code]);
        }

        foreach (['planned', 'allowed', 'blocked', 'in_progress', 'passed', 'failed', 'no_show', 'cancelled', 'archived'] as $code) {
            $this->assertDatabaseHas('exam_attempt_statuses', ['code' => $code]);
        }

        foreach (['pending', 'passed', 'failed', 'needs_retake', 'cancelled'] as $code) {
            $this->assertDatabaseHas('exam_result_statuses', ['code' => $code]);
        }

        $this->assertSame(4, ExamAdmissionRule::query()->count());
        $this->assertTrue(ExamAdmissionRule::query()->active()->where('require_internal_exam_passed', true)->exists());
    }
}
