<?php

namespace Tests\Feature;

use App\Enums\EnrollmentStatus as EnrollmentStatusEnum;
use App\Enums\StudentStatus as StudentStatusEnum;
use App\Models\EnrollmentStatus as EnrollmentStatusModel;
use App\Models\Lead;
use App\Models\Student;
use App\Models\StudentActivity;
use App\Models\StudentEnrollment;
use App\Models\StudentStatus as StudentStatusModel;
use App\Models\StudentTask;
use Database\Seeders\StudentDictionarySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class StudentDatabaseFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_required_student_tables_extend_existing_storage(): void
    {
        foreach ([
            'student_statuses',
            'enrollment_statuses',
            'student_profiles',
            'enrollments',
            'student_activities',
            'student_tasks',
            'marketing_leads',
        ] as $table) {
            $this->assertTrue(Schema::hasTable($table), $table);
        }

        $this->assertFalse(Schema::hasTable('students'));
        $this->assertFalse(Schema::hasTable('student_enrollments'));

        foreach ([
            'uuid',
            'student_number',
            'user_id',
            'full_name',
            'middle_name',
            'personal_code',
            'normalized_phone',
            'preferred_messenger',
            'city',
            'locale',
            'status_id',
            'manager_id',
            'administrator_id',
            'source_lead_id',
            'source_id',
            'consent_accepted',
            'consent_accepted_at',
            'portal_access_created_at',
            'documents_summary',
            'payment_summary',
            'created_by_id',
            'updated_by_id',
            'deleted_at',
        ] as $column) {
            $this->assertTrue(Schema::hasColumn('student_profiles', $column), $column);
        }

        foreach ([
            'uuid',
            'enrollment_number',
            'lead_id',
            'course_category_id',
            'branch_id',
            'status_id',
            'manager_id',
            'administrator_id',
            'teacher_id',
            'start_date',
            'planned_end_date',
            'actual_end_date',
            'preferred_time',
            'training_language',
            'format',
            'gearbox_type',
            'price',
            'discount',
            'currency',
            'payment_status',
            'theory_progress',
            'practice_progress',
            'total_theory_hours',
            'completed_theory_hours',
            'total_practice_hours',
            'completed_practice_hours',
            'notes',
            'internal_notes',
            'created_by_id',
            'updated_by_id',
            'deleted_at',
        ] as $column) {
            $this->assertTrue(Schema::hasColumn('enrollments', $column), $column);
        }

        foreach (['converted_student_profile_id', 'converted_enrollment_id', 'converted_at'] as $column) {
            $this->assertTrue(Schema::hasColumn('marketing_leads', $column), $column);
        }
    }

    public function test_dictionary_seeders_create_default_student_and_enrollment_statuses(): void
    {
        $this->seed(StudentDictionarySeeder::class);
        $this->seed(StudentDictionarySeeder::class);

        $this->assertSame(1, StudentStatusModel::query()->where('is_default', true)->count());
        $this->assertDatabaseHas('student_statuses', ['code' => 'active', 'is_default' => true]);

        foreach (['active', 'inactive', 'blocked', 'archived'] as $status) {
            $this->assertDatabaseHas('student_statuses', ['code' => $status]);
        }

        $this->assertSame(1, EnrollmentStatusModel::query()->where('is_default', true)->count());
        $this->assertDatabaseHas('enrollment_statuses', ['code' => 'waiting_documents', 'is_default' => true]);

        foreach ([
            'waiting_documents',
            'waiting_payment',
            'waiting_start',
            'active',
            'paused',
            'completed',
            'cancelled',
            'archived',
        ] as $status) {
            $this->assertDatabaseHas('enrollment_statuses', ['code' => $status]);
        }
    }

    public function test_student_relationships_helpers_search_and_lead_conversion_work(): void
    {
        app()->setLocale('en');
        $this->seed(StudentDictionarySeeder::class);

        $studentStatus = StudentStatusModel::query()->where('code', 'active')->firstOrFail();
        $enrollmentStatus = EnrollmentStatusModel::query()->where('code', 'active')->firstOrFail();
        $lead = Lead::factory()->create([
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
            'phone' => '+370 600 00000',
        ]);

        $student = Student::factory()->active()->create([
            'status_id' => $studentStatus->id,
            'status' => StudentStatusEnum::Active,
            'source_lead_id' => $lead->id,
            'full_name' => 'Ada CRM Student',
            'first_name' => 'Ada',
            'middle_name' => 'CRM',
            'last_name' => 'Student',
            'phone' => '+370 600 00000',
            'email' => 'student-foundation@example.test',
        ]);

        $enrollment = StudentEnrollment::factory()->active()->create([
            'student_profile_id' => $student->id,
            'lead_id' => $lead->id,
            'status_id' => $enrollmentStatus->id,
            'status' => EnrollmentStatusEnum::Active,
            'enrollment_number' => 'ENR-FOUNDATION-1',
        ]);

        $lead->forceFill([
            'converted_student_profile_id' => $student->id,
            'converted_enrollment_id' => $enrollment->id,
            'converted_at' => now(),
        ])->save();

        StudentActivity::factory()->create([
            'student_id' => $student->id,
            'enrollment_id' => $enrollment->id,
            'lead_id' => $lead->id,
            'type' => 'created_from_lead',
        ]);

        StudentTask::factory()->create([
            'student_id' => $student->id,
            'enrollment_id' => $enrollment->id,
            'status' => 'open',
            'due_at' => now()->subMinute(),
        ]);

        $student = $student->fresh([
            'sourceLead',
            'enrollments',
            'currentEnrollment',
            'activities',
            'tasks',
        ]);

        $this->assertTrue($student->status()->first()->is($studentStatus));
        $this->assertSame('Active', $studentStatus->display_name);
        $this->assertTrue($student->sourceLead->is($lead));
        $this->assertCount(1, $student->enrollments);
        $this->assertTrue($student->current_enrollment->is($enrollment));
        $this->assertCount(1, $student->activities);
        $this->assertCount(1, $student->tasks);
        $this->assertSame('Ada CRM Student', $student->display_name);
        $this->assertFalse($student->is_archived);
        $this->assertFalse($student->is_blocked);

        $lead = $lead->fresh(['convertedStudent', 'convertedEnrollment']);

        $this->assertTrue($lead->convertedStudent->is($student));
        $this->assertTrue($lead->convertedEnrollment->is($enrollment));
        $this->assertTrue(Student::query()->search('Ada CRM')->whereKey($student->id)->exists());
        $this->assertTrue(Student::query()->search('600 00000')->whereKey($student->id)->exists());
        $this->assertTrue(StudentTask::query()->overdue()->where('student_id', $student->id)->exists());
    }

    public function test_enrollment_relationships_helpers_and_scopes_work(): void
    {
        $this->seed(StudentDictionarySeeder::class);

        $waitingDocuments = StudentEnrollment::factory()->waitingDocuments()->create([
            'enrollment_number' => 'ENR-WAITING-DOCS',
        ]);
        $waitingPayment = StudentEnrollment::factory()->waitingPayment()->create();
        $waitingStart = StudentEnrollment::factory()->waitingStart()->create();
        $active = StudentEnrollment::factory()->active()->create();
        $completed = StudentEnrollment::factory()->completed()->create();
        $cancelled = StudentEnrollment::factory()->cancelled()->create();

        $this->assertTrue($active->student()->exists());
        $this->assertTrue($active->status()->exists());
        $this->assertTrue($active->course()->exists());
        $this->assertTrue($active->is_active);
        $this->assertTrue($completed->is_completed);
        $this->assertTrue($cancelled->is_cancelled);
        $this->assertSame('students.enrollments.payment_statuses.waiting', $waitingDocuments->payment_status_label);

        $this->assertTrue(StudentEnrollment::query()->waitingDocuments()->whereKey($waitingDocuments->id)->exists());
        $this->assertTrue(StudentEnrollment::query()->waitingPayment()->whereKey($waitingPayment->id)->exists());
        $this->assertTrue(StudentEnrollment::query()->waitingStart()->whereKey($waitingStart->id)->exists());
        $this->assertTrue(StudentEnrollment::query()->active()->whereKey($active->id)->exists());
        $this->assertTrue(StudentEnrollment::query()->completed()->whereKey($completed->id)->exists());
        $this->assertTrue(StudentEnrollment::query()->cancelled()->whereKey($cancelled->id)->exists());
        $this->assertTrue(StudentEnrollment::query()->search('ENR-WAITING-DOCS')->whereKey($waitingDocuments->id)->exists());
    }
}
