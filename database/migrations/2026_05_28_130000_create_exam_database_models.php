<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->createExamTypesTable();
        $this->createExamStatusesTable();
        $this->createExamAttemptStatusesTable();
        $this->createExamResultStatusesTable();
        $this->createExamAdmissionRulesTable();
        $this->extendExamSessionsTable();
        $this->createExamParticipantsTable();
        $this->extendExamAttemptsTable();
        $this->createExamResultsTable();
        $this->createExamRetakesTable();
        $this->createExamChecklistItemsTable();
        $this->extendExamActivitiesTable();
    }

    public function down(): void
    {
        $this->dropColumnsIfExist('exam_activities', ['attempt_id', 'student_id']);
        $this->dropColumnsIfExist('exam_attempts', ['status_id', 'student_id', 'attempt_no', 'no_show']);
        $this->dropColumnsIfExist('exam_sessions', [
            'exam_number',
            'type_id',
            'status_id',
            'group_id',
            'scheduled_at',
            'examiner_id',
            'classroom_id',
        ]);

        Schema::dropIfExists('exam_checklist_items');
        Schema::dropIfExists('exam_retakes');
        Schema::dropIfExists('exam_results');
        Schema::dropIfExists('exam_participants');
        Schema::dropIfExists('exam_admission_rules');
        Schema::dropIfExists('exam_result_statuses');
        Schema::dropIfExists('exam_attempt_statuses');
        Schema::dropIfExists('exam_statuses');
        Schema::dropIfExists('exam_types');
    }

    private function createExamTypesTable(): void
    {
        if (Schema::hasTable('exam_types')) {
            return;
        }

        Schema::create('exam_types', function (Blueprint $table): void {
            $table->id();
            $table->string('code')->unique();
            $table->string('name')->nullable();
            $table->json('name_translations')->nullable();
            $table->json('description_translations')->nullable();
            $table->boolean('is_internal')->default(true)->index();
            $table->boolean('is_official')->default(false)->index();
            $table->boolean('is_theory')->default(false)->index();
            $table->boolean('is_practical')->default(false)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedSmallInteger('sort_order')->default(0)->index();
            $table->timestamps();

            $table->index(['is_active', 'is_internal', 'is_theory'], 'exam_types_active_internal_theory_idx');
            $table->index(['is_active', 'is_official', 'is_practical'], 'exam_types_active_official_practical_idx');
        });
    }

    private function createExamStatusesTable(): void
    {
        if (Schema::hasTable('exam_statuses')) {
            return;
        }

        Schema::create('exam_statuses', function (Blueprint $table): void {
            $table->id();
            $table->string('code')->unique();
            $table->string('name')->nullable();
            $table->json('name_translations')->nullable();
            $table->json('description_translations')->nullable();
            $table->string('color')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0)->index();
            $table->boolean('is_system')->default(true)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
    }

    private function createExamAttemptStatusesTable(): void
    {
        if (Schema::hasTable('exam_attempt_statuses')) {
            return;
        }

        Schema::create('exam_attempt_statuses', function (Blueprint $table): void {
            $table->id();
            $table->string('code')->unique();
            $table->string('name')->nullable();
            $table->json('name_translations')->nullable();
            $table->json('description_translations')->nullable();
            $table->string('color')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0)->index();
            $table->boolean('is_system')->default(true)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
    }

    private function createExamResultStatusesTable(): void
    {
        if (Schema::hasTable('exam_result_statuses')) {
            return;
        }

        Schema::create('exam_result_statuses', function (Blueprint $table): void {
            $table->id();
            $table->string('code')->unique();
            $table->string('name')->nullable();
            $table->json('name_translations')->nullable();
            $table->json('description_translations')->nullable();
            $table->string('color')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0)->index();
            $table->boolean('is_system')->default(true)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
    }

    private function createExamAdmissionRulesTable(): void
    {
        if (Schema::hasTable('exam_admission_rules')) {
            return;
        }

        Schema::create('exam_admission_rules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('exam_type_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('course_id')->nullable()->constrained('training_programs')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('course_category_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->decimal('required_theory_hours', 8, 2)->nullable();
            $table->decimal('required_practice_hours', 8, 2)->nullable();
            $table->boolean('require_documents')->default(true)->index();
            $table->boolean('require_no_debt')->default(true)->index();
            $table->boolean('require_internal_exam_passed')->default(false)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->index(['exam_type_id', 'is_active'], 'exam_admission_rules_type_active_idx');
            $table->index(['course_id', 'course_category_id'], 'exam_admission_rules_course_category_idx');
        });
    }

    private function extendExamSessionsTable(): void
    {
        if (! Schema::hasTable('exam_sessions')) {
            return;
        }

        Schema::table('exam_sessions', function (Blueprint $table): void {
            if (! Schema::hasColumn('exam_sessions', 'exam_number')) {
                $table->string('exam_number')->nullable()->unique()->after('uuid');
            }

            if (! Schema::hasColumn('exam_sessions', 'type_id')) {
                $table->foreignId('type_id')->nullable()->after('exam_number')->constrained('exam_types')->cascadeOnUpdate()->nullOnDelete();
            }

            if (! Schema::hasColumn('exam_sessions', 'status_id')) {
                $table->foreignId('status_id')->nullable()->after('type_id')->constrained('exam_statuses')->cascadeOnUpdate()->nullOnDelete();
            }

            if (! Schema::hasColumn('exam_sessions', 'group_id')) {
                $table->foreignId('group_id')->nullable()->after('branch_id')->constrained('training_groups')->cascadeOnUpdate()->nullOnDelete();
            }

            if (! Schema::hasColumn('exam_sessions', 'scheduled_at')) {
                $table->timestamp('scheduled_at')->nullable()->after('status')->index();
            }

            if (! Schema::hasColumn('exam_sessions', 'examiner_id')) {
                $table->foreignId('examiner_id')->nullable()->after('scheduled_at')->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            }

            if (! Schema::hasColumn('exam_sessions', 'classroom_id')) {
                $table->unsignedBigInteger('classroom_id')->nullable()->after('vehicle_id')->index();
            }

            $table->index(['type_id', 'status_id', 'scheduled_at'], 'exam_sessions_type_status_scheduled_idx');
            $table->index(['group_id', 'scheduled_at'], 'exam_sessions_group_scheduled_idx');
        });
    }

    private function createExamParticipantsTable(): void
    {
        if (Schema::hasTable('exam_participants')) {
            return;
        }

        Schema::create('exam_participants', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('exam_session_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('student_profiles')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('enrollment_id')->constrained('enrollments')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('status')->default('registered')->index();
            $table->boolean('admitted')->default(false)->index();
            $table->text('block_reason')->nullable();
            $table->timestamp('registered_at')->nullable()->index();
            $table->timestamps();

            $table->unique(['exam_session_id', 'student_id', 'enrollment_id'], 'exam_participants_session_student_enrollment_unique');
            $table->index(['student_id', 'status'], 'exam_participants_student_status_idx');
            $table->index(['enrollment_id', 'status'], 'exam_participants_enrollment_status_idx');
        });
    }

    private function extendExamAttemptsTable(): void
    {
        if (! Schema::hasTable('exam_attempts')) {
            return;
        }

        Schema::table('exam_attempts', function (Blueprint $table): void {
            if (! Schema::hasColumn('exam_attempts', 'status_id')) {
                $table->foreignId('status_id')->nullable()->after('status')->constrained('exam_attempt_statuses')->cascadeOnUpdate()->nullOnDelete();
            }

            if (! Schema::hasColumn('exam_attempts', 'student_id')) {
                $table->foreignId('student_id')->nullable()->after('enrollment_id')->constrained('student_profiles')->cascadeOnUpdate()->cascadeOnDelete();
            }

            if (! Schema::hasColumn('exam_attempts', 'attempt_no')) {
                $table->unsignedSmallInteger('attempt_no')->nullable()->after('attempt_number')->index();
            }

            if (! Schema::hasColumn('exam_attempts', 'no_show')) {
                $table->boolean('no_show')->default(false)->after('passed')->index();
            }

            $table->index(['student_id', 'status_id'], 'exam_attempts_student_status_id_idx');
            $table->index(['exam_session_id', 'status_id'], 'exam_attempts_session_status_id_idx');
        });
    }

    private function createExamResultsTable(): void
    {
        if (Schema::hasTable('exam_results')) {
            return;
        }

        Schema::create('exam_results', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('attempt_id')->unique()->constrained('exam_attempts')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('result_status_id')->constrained('exam_result_statuses')->cascadeOnUpdate()->restrictOnDelete();
            $table->decimal('score', 8, 2)->nullable();
            $table->decimal('max_score', 8, 2)->nullable();
            $table->boolean('passed')->default(false)->index();
            $table->text('examiner_comment')->nullable();
            $table->text('mistakes_summary')->nullable();
            $table->foreignId('decided_by_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->timestamp('decided_at')->nullable()->index();
            $table->timestamps();

            $table->index(['result_status_id', 'decided_at'], 'exam_results_status_decided_idx');
        });
    }

    private function createExamRetakesTable(): void
    {
        if (Schema::hasTable('exam_retakes')) {
            return;
        }

        Schema::create('exam_retakes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('student_id')->constrained('student_profiles')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('enrollment_id')->constrained('enrollments')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('previous_attempt_id')->constrained('exam_attempts')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('new_attempt_id')->nullable()->constrained('exam_attempts')->cascadeOnUpdate()->nullOnDelete();
            $table->text('reason')->nullable();
            $table->timestamp('planned_at')->nullable()->index();
            $table->string('status')->default('planned')->index();
            $table->timestamps();

            $table->index(['student_id', 'status'], 'exam_retakes_student_status_idx');
            $table->index(['enrollment_id', 'planned_at'], 'exam_retakes_enrollment_planned_idx');
        });
    }

    private function createExamChecklistItemsTable(): void
    {
        if (Schema::hasTable('exam_checklist_items')) {
            return;
        }

        Schema::create('exam_checklist_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('exam_session_id')->nullable()->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('attempt_id')->nullable()->constrained('exam_attempts')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('student_profiles')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('enrollment_id')->constrained('enrollments')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('key')->index();
            $table->json('title_translations')->nullable();
            $table->string('status')->default('pending')->index();
            $table->boolean('required')->default(true)->index();
            $table->timestamps();

            $table->unique(['exam_session_id', 'attempt_id', 'student_id', 'key'], 'exam_checklist_items_context_student_key_unique');
            $table->index(['student_id', 'status'], 'exam_checklist_items_student_status_idx');
            $table->index(['enrollment_id', 'required', 'status'], 'exam_checklist_items_enrollment_required_status_idx');
        });
    }

    private function extendExamActivitiesTable(): void
    {
        if (! Schema::hasTable('exam_activities')) {
            return;
        }

        Schema::table('exam_activities', function (Blueprint $table): void {
            if (! Schema::hasColumn('exam_activities', 'attempt_id')) {
                $table->foreignId('attempt_id')->nullable()->after('exam_attempt_id')->constrained('exam_attempts')->cascadeOnUpdate()->nullOnDelete();
            }

            if (! Schema::hasColumn('exam_activities', 'student_id')) {
                $table->foreignId('student_id')->nullable()->after('student_profile_id')->constrained('student_profiles')->cascadeOnUpdate()->nullOnDelete();
            }

            $table->index(['attempt_id', 'type'], 'exam_activities_attempt_type_idx');
            $table->index(['student_id', 'created_at'], 'exam_activities_student_id_created_idx');
        });
    }

    /**
     * @param  array<int, string>  $columns
     */
    private function dropColumnsIfExist(string $tableName, array $columns): void
    {
        if (! Schema::hasTable($tableName)) {
            return;
        }

        $existing = array_values(array_filter(
            $columns,
            fn (string $column): bool => Schema::hasColumn($tableName, $column),
        ));

        if ($existing === []) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($existing): void {
            $table->dropColumn($existing);
        });
    }
};
