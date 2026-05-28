<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->createExamAdmissionsTable();
        $this->createExamAdmissionChecklistItemsTable();
        $this->createExamSessionsTable();
        $this->createExamAttemptsTable();
        $this->createExamActivitiesTable();
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_activities');
        Schema::dropIfExists('exam_attempts');
        Schema::dropIfExists('exam_sessions');
        Schema::dropIfExists('exam_admission_checklist_items');
        Schema::dropIfExists('exam_admissions');
    }

    private function createExamAdmissionsTable(): void
    {
        if (Schema::hasTable('exam_admissions')) {
            return;
        }

        Schema::create('exam_admissions', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->nullable()->unique();
            $table->foreignId('enrollment_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('student_profile_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('training_group_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('training_program_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('instructor_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->string('admission_type')->index();
            $table->string('status')->default('checking')->index();
            $table->decimal('required_theory_hours', 8, 2)->nullable();
            $table->decimal('completed_theory_hours', 8, 2)->nullable();
            $table->decimal('required_practice_hours', 8, 2)->nullable();
            $table->decimal('completed_practice_hours', 8, 2)->nullable();
            $table->string('documents_status')->default('pending')->index();
            $table->string('payment_status')->default('pending')->index();
            $table->string('checklist_status')->default('pending')->index();
            $table->timestamp('admitted_at')->nullable()->index();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->text('notes')->nullable();
            $table->text('internal_notes')->nullable();
            $table->json('meta')->nullable();
            $table->foreignId('created_by_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('updated_by_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->timestamps();

            $table->unique(['enrollment_id', 'admission_type'], 'exam_admissions_enrollment_type_unique');
            $table->index(['student_profile_id', 'status'], 'exam_admissions_student_status_idx');
            $table->index(['training_group_id', 'status'], 'exam_admissions_group_status_idx');
            $table->index(['admission_type', 'status'], 'exam_admissions_type_status_idx');
        });
    }

    private function createExamAdmissionChecklistItemsTable(): void
    {
        if (Schema::hasTable('exam_admission_checklist_items')) {
            return;
        }

        Schema::create('exam_admission_checklist_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('exam_admission_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('code')->index();
            $table->json('title_translations')->nullable();
            $table->string('status')->default('pending')->index();
            $table->string('source_type')->nullable()->index();
            $table->unsignedBigInteger('source_id')->nullable()->index();
            $table->foreignId('student_document_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('payment_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('driving_lesson_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->timestamp('checked_at')->nullable()->index();
            $table->foreignId('checked_by_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->text('notes')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique(['exam_admission_id', 'code'], 'exam_admission_items_admission_code_unique');
            $table->index(['exam_admission_id', 'status'], 'exam_admission_items_admission_status_idx');
        });
    }

    private function createExamSessionsTable(): void
    {
        if (Schema::hasTable('exam_sessions')) {
            return;
        }

        Schema::create('exam_sessions', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->nullable()->unique();
            $table->foreignId('branch_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('training_program_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('training_group_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('instructor_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('vehicle_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->string('exam_type')->index();
            $table->string('provider')->default('internal')->index();
            $table->string('status')->default('planned')->index();
            $table->timestamp('starts_at')->index();
            $table->timestamp('ends_at')->nullable();
            $table->string('location')->nullable();
            $table->unsignedSmallInteger('capacity')->default(1);
            $table->unsignedSmallInteger('seats_taken')->default(0);
            $table->string('external_reference')->nullable()->index();
            $table->json('official_placeholder_payload')->nullable();
            $table->text('notes')->nullable();
            $table->text('internal_notes')->nullable();
            $table->foreignId('created_by_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('updated_by_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->timestamps();

            $table->index(['exam_type', 'status', 'starts_at'], 'exam_sessions_type_status_start_idx');
            $table->index(['training_group_id', 'starts_at'], 'exam_sessions_group_start_idx');
            $table->index(['instructor_id', 'starts_at'], 'exam_sessions_instructor_start_idx');
        });
    }

    private function createExamAttemptsTable(): void
    {
        if (Schema::hasTable('exam_attempts')) {
            return;
        }

        Schema::create('exam_attempts', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->nullable()->unique();
            $table->foreignId('exam_admission_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('exam_session_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('enrollment_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('student_profile_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('training_group_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('training_program_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('instructor_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('driving_lesson_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('student_document_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('payment_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('retake_of_attempt_id')->nullable()->constrained('exam_attempts')->cascadeOnUpdate()->nullOnDelete();
            $table->string('exam_type')->index();
            $table->string('provider')->default('internal')->index();
            $table->string('status')->default('scheduled')->index();
            $table->unsignedSmallInteger('attempt_number')->default(1);
            $table->decimal('score', 8, 2)->nullable();
            $table->decimal('max_score', 8, 2)->nullable();
            $table->boolean('passed')->default(false)->index();
            $table->json('result_payload')->nullable();
            $table->timestamp('started_at')->nullable()->index();
            $table->timestamp('finished_at')->nullable()->index();
            $table->timestamp('next_eligible_at')->nullable()->index();
            $table->string('official_reference')->nullable()->index();
            $table->json('official_payload')->nullable();
            $table->text('notes')->nullable();
            $table->text('internal_notes')->nullable();
            $table->foreignId('created_by_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('updated_by_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->timestamps();

            $table->index(['enrollment_id', 'exam_type', 'attempt_number'], 'exam_attempts_enrollment_type_attempt_idx');
            $table->index(['exam_session_id', 'status'], 'exam_attempts_session_status_idx');
            $table->index(['student_profile_id', 'status'], 'exam_attempts_student_status_idx');
            $table->index(['retake_of_attempt_id'], 'exam_attempts_retake_parent_idx');
        });
    }

    private function createExamActivitiesTable(): void
    {
        if (Schema::hasTable('exam_activities')) {
            return;
        }

        Schema::create('exam_activities', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('exam_admission_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('exam_session_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('exam_attempt_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('enrollment_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('student_profile_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('training_group_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->string('type')->index();
            $table->string('title')->nullable();
            $table->text('body')->nullable();
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['student_profile_id', 'created_at'], 'exam_activities_student_created_idx');
            $table->index(['enrollment_id', 'type'], 'exam_activities_enrollment_type_idx');
            $table->index(['exam_session_id', 'type'], 'exam_activities_session_type_idx');
        });
    }
};
