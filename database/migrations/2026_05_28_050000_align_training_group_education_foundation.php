<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->extendTrainingGroupStatuses();
        $this->createLearningPrograms();
        $this->createLearningProgramModules();
        $this->extendTrainingGroups();
        $this->extendTrainingGroupMemberships();
        $this->extendLearningTopics();
        $this->extendTrainingGroupSchedulePatterns();
        $this->extendTrainingGroupActivities();
    }

    public function down(): void
    {
        $this->dropTrainingGroupActivityAliases();
        $this->dropTrainingGroupSchedulePatternAliases();
        $this->dropLearningTopicAliases();
        $this->dropTrainingGroupMembershipAliases();
        $this->dropTrainingGroupAliases();
        Schema::dropIfExists('learning_program_modules');
        Schema::dropIfExists('learning_programs');
        $this->dropTrainingGroupStatusAliases();
    }

    private function extendTrainingGroupStatuses(): void
    {
        if (! Schema::hasTable('training_group_statuses')) {
            return;
        }

        Schema::table('training_group_statuses', function (Blueprint $table): void {
            if (! Schema::hasColumn('training_group_statuses', 'is_open_for_enrollment')) {
                $table->boolean('is_open_for_enrollment')->default(false)->after('is_public')->index();
            }

            if (! Schema::hasColumn('training_group_statuses', 'is_archived')) {
                $table->boolean('is_archived')->default(false)->after('is_cancelled')->index();
            }
        });
    }

    private function createLearningPrograms(): void
    {
        if (Schema::hasTable('learning_programs')) {
            return;
        }

        Schema::create('learning_programs', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->nullable()->unique();
            $table->foreignId('course_id')->nullable()->constrained('training_programs')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('course_category_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->string('code')->nullable()->unique();
            $table->json('name_translations')->nullable();
            $table->json('description_translations')->nullable();
            $table->boolean('is_default')->default(false)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->foreignId('created_by_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('updated_by_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['course_id', 'is_active'], 'learning_programs_course_active_idx');
            $table->index(['course_category_id', 'is_active'], 'learning_programs_category_active_idx');
        });
    }

    private function createLearningProgramModules(): void
    {
        if (Schema::hasTable('learning_program_modules')) {
            return;
        }

        Schema::create('learning_program_modules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('learning_program_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('code')->nullable()->index();
            $table->string('type')->default('theory')->index();
            $table->json('name_translations')->nullable();
            $table->json('description_translations')->nullable();
            $table->decimal('required_hours', 8, 2)->nullable();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->boolean('is_required')->default(true)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->index(['learning_program_id', 'type', 'sort_order'], 'learning_program_modules_program_type_sort_idx');
        });
    }

    private function extendTrainingGroups(): void
    {
        if (! Schema::hasTable('training_groups')) {
            return;
        }

        Schema::table('training_groups', function (Blueprint $table): void {
            if (! Schema::hasColumn('training_groups', 'course_id')) {
                $table->foreignId('course_id')->nullable()->after('training_program_id')->constrained('training_programs')->cascadeOnUpdate()->nullOnDelete();
            }

            if (! Schema::hasColumn('training_groups', 'learning_program_id')) {
                $table->foreignId('learning_program_id')->nullable()->after('status_id')->constrained('learning_programs')->cascadeOnUpdate()->nullOnDelete();
            }

            if (! Schema::hasColumn('training_groups', 'manager_id')) {
                $table->foreignId('manager_id')->nullable()->after('learning_program_id')->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            }

            if (! Schema::hasColumn('training_groups', 'administrator_id')) {
                $table->foreignId('administrator_id')->nullable()->after('manager_id')->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            }

            if (! Schema::hasColumn('training_groups', 'teacher_id')) {
                $table->foreignId('teacher_id')->nullable()->after('administrator_id')->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            }

            if (! Schema::hasColumn('training_groups', 'public_description_translations')) {
                $table->json('public_description_translations')->nullable()->after('description_translations');
            }

            if (! Schema::hasColumn('training_groups', 'start_date')) {
                $table->date('start_date')->nullable()->after('enrollment_closes_on')->index();
            }

            if (! Schema::hasColumn('training_groups', 'planned_end_date')) {
                $table->date('planned_end_date')->nullable()->after('start_date');
            }

            if (! Schema::hasColumn('training_groups', 'actual_end_date')) {
                $table->date('actual_end_date')->nullable()->after('planned_end_date');
            }

            if (! Schema::hasColumn('training_groups', 'capacity_total')) {
                $table->unsignedInteger('capacity_total')->nullable()->after('capacity');
            }

            if (! Schema::hasColumn('training_groups', 'capacity_reserved')) {
                $table->unsignedInteger('capacity_reserved')->default(0)->after('capacity_total');
            }

            if (! Schema::hasColumn('training_groups', 'capacity_taken')) {
                $table->unsignedInteger('capacity_taken')->default(0)->after('capacity_reserved');
            }

            if (! Schema::hasColumn('training_groups', 'capacity_waitlist')) {
                $table->unsignedInteger('capacity_waitlist')->default(0)->after('capacity_taken');
            }

            if (! Schema::hasColumn('training_groups', 'is_accepting_applications')) {
                $table->boolean('is_accepting_applications')->default(false)->after('is_featured')->index();
            }

            if (! Schema::hasColumn('training_groups', 'timezone')) {
                $table->string('timezone')->nullable()->after('is_accepting_applications');
            }

            if (! Schema::hasColumn('training_groups', 'default_lesson_duration_minutes')) {
                $table->unsignedInteger('default_lesson_duration_minutes')->nullable()->after('timezone');
            }

            if (! Schema::hasColumn('training_groups', 'notes')) {
                $table->text('notes')->nullable()->after('schedule_notes');
            }

            if (! Schema::hasColumn('training_groups', 'internal_notes')) {
                $table->text('internal_notes')->nullable()->after('notes');
            }
        });
    }

    private function extendTrainingGroupMemberships(): void
    {
        if (! Schema::hasTable('training_group_memberships')) {
            return;
        }

        Schema::table('training_group_memberships', function (Blueprint $table): void {
            if (! Schema::hasColumn('training_group_memberships', 'student_id')) {
                $table->foreignId('student_id')->nullable()->after('training_group_id')->constrained('student_profiles')->cascadeOnUpdate()->cascadeOnDelete();
            }

            if (! Schema::hasColumn('training_group_memberships', 'student_enrollment_id')) {
                $table->foreignId('student_enrollment_id')->nullable()->after('student_profile_id')->constrained('enrollments')->cascadeOnUpdate()->nullOnDelete();
            }

            if (! Schema::hasColumn('training_group_memberships', 'transfer_from_group_id')) {
                $table->foreignId('transfer_from_group_id')->nullable()->after('left_at')->constrained('training_groups')->cascadeOnUpdate()->nullOnDelete();
            }

            if (! Schema::hasColumn('training_group_memberships', 'transfer_to_group_id')) {
                $table->foreignId('transfer_to_group_id')->nullable()->after('transfer_from_group_id')->constrained('training_groups')->cascadeOnUpdate()->nullOnDelete();
            }

            if (! Schema::hasColumn('training_group_memberships', 'transfer_reason')) {
                $table->text('transfer_reason')->nullable()->after('transfer_to_group_id');
            }
        });
    }

    private function extendLearningTopics(): void
    {
        if (! Schema::hasTable('learning_topics')) {
            return;
        }

        Schema::table('learning_topics', function (Blueprint $table): void {
            if (! Schema::hasColumn('learning_topics', 'learning_program_module_id')) {
                $table->foreignId('learning_program_module_id')->nullable()->after('course_module_id')->constrained('learning_program_modules')->cascadeOnUpdate()->nullOnDelete();
            }

            if (! Schema::hasColumn('learning_topics', 'name_translations')) {
                $table->json('name_translations')->nullable()->after('title_translations');
            }

            if (! Schema::hasColumn('learning_topics', 'estimated_hours')) {
                $table->decimal('estimated_hours', 8, 2)->nullable()->after('duration_minutes');
            }
        });
    }

    private function extendTrainingGroupSchedulePatterns(): void
    {
        if (! Schema::hasTable('training_group_schedule_patterns')) {
            return;
        }

        Schema::table('training_group_schedule_patterns', function (Blueprint $table): void {
            if (! Schema::hasColumn('training_group_schedule_patterns', 'type')) {
                $table->string('type')->default('theory')->after('title_translations')->index();
            }

            if (! Schema::hasColumn('training_group_schedule_patterns', 'start_time')) {
                $table->time('start_time')->nullable()->after('day_of_week');
            }

            if (! Schema::hasColumn('training_group_schedule_patterns', 'end_time')) {
                $table->time('end_time')->nullable()->after('start_time');
            }

            if (! Schema::hasColumn('training_group_schedule_patterns', 'classroom_id')) {
                $table->unsignedBigInteger('classroom_id')->nullable()->after('classroom');
            }

            if (! Schema::hasColumn('training_group_schedule_patterns', 'location_translations')) {
                $table->json('location_translations')->nullable()->after('classroom_id');
            }

            if (! Schema::hasColumn('training_group_schedule_patterns', 'notes_translations')) {
                $table->json('notes_translations')->nullable()->after('location_translations');
            }
        });
    }

    private function extendTrainingGroupActivities(): void
    {
        if (! Schema::hasTable('training_group_activities')) {
            return;
        }

        Schema::table('training_group_activities', function (Blueprint $table): void {
            if (! Schema::hasColumn('training_group_activities', 'student_id')) {
                $table->foreignId('student_id')->nullable()->after('training_group_id')->constrained('student_profiles')->cascadeOnUpdate()->nullOnDelete();
            }

            if (! Schema::hasColumn('training_group_activities', 'student_enrollment_id')) {
                $table->foreignId('student_enrollment_id')->nullable()->after('student_id')->constrained('enrollments')->cascadeOnUpdate()->nullOnDelete();
            }
        });
    }

    private function dropTrainingGroupActivityAliases(): void
    {
        if (! Schema::hasTable('training_group_activities')) {
            return;
        }

        Schema::table('training_group_activities', function (Blueprint $table): void {
            foreach (['student_id', 'student_enrollment_id'] as $column) {
                if (Schema::hasColumn('training_group_activities', $column)) {
                    $table->dropForeign([$column]);
                    $table->dropColumn($column);
                }
            }
        });
    }

    private function dropTrainingGroupSchedulePatternAliases(): void
    {
        if (! Schema::hasTable('training_group_schedule_patterns')) {
            return;
        }

        Schema::table('training_group_schedule_patterns', function (Blueprint $table): void {
            foreach (['type', 'start_time', 'end_time', 'classroom_id', 'location_translations', 'notes_translations'] as $column) {
                if (Schema::hasColumn('training_group_schedule_patterns', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    private function dropLearningTopicAliases(): void
    {
        if (! Schema::hasTable('learning_topics')) {
            return;
        }

        Schema::table('learning_topics', function (Blueprint $table): void {
            if (Schema::hasColumn('learning_topics', 'learning_program_module_id')) {
                $table->dropForeign(['learning_program_module_id']);
                $table->dropColumn('learning_program_module_id');
            }

            foreach (['name_translations', 'estimated_hours'] as $column) {
                if (Schema::hasColumn('learning_topics', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    private function dropTrainingGroupMembershipAliases(): void
    {
        if (! Schema::hasTable('training_group_memberships')) {
            return;
        }

        Schema::table('training_group_memberships', function (Blueprint $table): void {
            foreach (['student_id', 'student_enrollment_id', 'transfer_from_group_id', 'transfer_to_group_id'] as $column) {
                if (Schema::hasColumn('training_group_memberships', $column)) {
                    $table->dropForeign([$column]);
                    $table->dropColumn($column);
                }
            }

            if (Schema::hasColumn('training_group_memberships', 'transfer_reason')) {
                $table->dropColumn('transfer_reason');
            }
        });
    }

    private function dropTrainingGroupAliases(): void
    {
        if (! Schema::hasTable('training_groups')) {
            return;
        }

        Schema::table('training_groups', function (Blueprint $table): void {
            foreach (['course_id', 'learning_program_id', 'manager_id', 'administrator_id', 'teacher_id'] as $column) {
                if (Schema::hasColumn('training_groups', $column)) {
                    $table->dropForeign([$column]);
                    $table->dropColumn($column);
                }
            }

            foreach ([
                'public_description_translations',
                'start_date',
                'planned_end_date',
                'actual_end_date',
                'capacity_total',
                'capacity_reserved',
                'capacity_taken',
                'capacity_waitlist',
                'is_accepting_applications',
                'timezone',
                'default_lesson_duration_minutes',
                'notes',
                'internal_notes',
            ] as $column) {
                if (Schema::hasColumn('training_groups', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    private function dropTrainingGroupStatusAliases(): void
    {
        if (! Schema::hasTable('training_group_statuses')) {
            return;
        }

        Schema::table('training_group_statuses', function (Blueprint $table): void {
            foreach (['is_open_for_enrollment', 'is_archived'] as $column) {
                if (Schema::hasColumn('training_group_statuses', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
