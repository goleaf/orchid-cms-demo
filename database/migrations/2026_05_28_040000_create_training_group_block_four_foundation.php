<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->createTrainingGroupStatusesTable();
        $this->extendTrainingGroupsTable();
        $this->extendCourseModulesTable();
        $this->createLearningTopicsTable();
        $this->createTrainingGroupMembershipsTable();
        $this->createTrainingGroupSchedulePatternsTable();
        $this->createTrainingGroupActivitiesTable();
    }

    public function down(): void
    {
        Schema::dropIfExists('training_group_activities');
        Schema::dropIfExists('training_group_schedule_patterns');
        Schema::dropIfExists('training_group_memberships');
        Schema::dropIfExists('learning_topics');

        if (Schema::hasTable('course_modules')) {
            Schema::table('course_modules', function (Blueprint $table): void {
                $this->dropColumnsIfExist($table, 'course_modules', [
                    'uuid',
                    'code',
                    'title_translations',
                    'description_translations',
                    'created_by_id',
                    'updated_by_id',
                    'deleted_at',
                ]);
            });
        }

        if (Schema::hasTable('training_groups')) {
            Schema::table('training_groups', function (Blueprint $table): void {
                if (Schema::hasColumn('training_groups', 'status_id')) {
                    $table->dropForeign(['status_id']);
                }

                $this->dropColumnsIfExist($table, 'training_groups', [
                    'status_id',
                    'learning_notes',
                    'schedule_notes',
                    'enrollment_closes_on',
                ]);
            });
        }

        Schema::dropIfExists('training_group_statuses');
    }

    private function createTrainingGroupStatusesTable(): void
    {
        if (Schema::hasTable('training_group_statuses')) {
            return;
        }

        Schema::create('training_group_statuses', function (Blueprint $table): void {
            $table->id();
            $table->string('code')->unique();
            $table->string('name')->nullable();
            $table->json('name_translations')->nullable();
            $table->json('description_translations')->nullable();
            $table->string('color')->nullable();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->boolean('is_system')->default(false)->index();
            $table->boolean('is_default')->default(false)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('is_public')->default(false)->index();
            $table->boolean('accepts_enrollments')->default(false)->index();
            $table->boolean('is_in_progress')->default(false)->index();
            $table->boolean('is_final')->default(false)->index();
            $table->boolean('is_success')->default(false)->index();
            $table->boolean('is_cancelled')->default(false)->index();
            $table->timestamps();
        });
    }

    private function extendTrainingGroupsTable(): void
    {
        if (! Schema::hasTable('training_groups')) {
            return;
        }

        Schema::table('training_groups', function (Blueprint $table): void {
            if (! Schema::hasColumn('training_groups', 'status_id')) {
                $table->foreignId('status_id')
                    ->nullable()
                    ->after('status')
                    ->constrained('training_group_statuses')
                    ->cascadeOnUpdate()
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('training_groups', 'enrollment_closes_on')) {
                $table->date('enrollment_closes_on')->nullable()->after('ends_on')->index();
            }

            if (! Schema::hasColumn('training_groups', 'learning_notes')) {
                $table->text('learning_notes')->nullable()->after('classroom');
            }

            if (! Schema::hasColumn('training_groups', 'schedule_notes')) {
                $table->text('schedule_notes')->nullable()->after('learning_notes');
            }
        });
    }

    private function extendCourseModulesTable(): void
    {
        if (! Schema::hasTable('course_modules')) {
            return;
        }

        Schema::table('course_modules', function (Blueprint $table): void {
            if (! Schema::hasColumn('course_modules', 'uuid')) {
                $table->uuid('uuid')->nullable()->unique()->after('id');
            }

            if (! Schema::hasColumn('course_modules', 'code')) {
                $table->string('code')->nullable()->after('training_program_id')->index();
            }

            if (! Schema::hasColumn('course_modules', 'title_translations')) {
                $table->json('title_translations')->nullable()->after('title');
            }

            if (! Schema::hasColumn('course_modules', 'description_translations')) {
                $table->json('description_translations')->nullable()->after('title_translations');
            }

            if (! Schema::hasColumn('course_modules', 'created_by_id')) {
                $table->foreignId('created_by_id')->nullable()->after('is_required')->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            }

            if (! Schema::hasColumn('course_modules', 'updated_by_id')) {
                $table->foreignId('updated_by_id')->nullable()->after('created_by_id')->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            }

            if (! Schema::hasColumn('course_modules', 'deleted_at')) {
                $table->softDeletes();
            }

            $table->index(['training_program_id', 'module_type', 'sort_order'], 'course_modules_program_type_sort_idx');
        });
    }

    private function createLearningTopicsTable(): void
    {
        if (Schema::hasTable('learning_topics')) {
            return;
        }

        Schema::create('learning_topics', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->nullable()->unique();
            $table->foreignId('training_program_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('course_module_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->string('code')->nullable()->index();
            $table->json('title_translations')->nullable();
            $table->json('description_translations')->nullable();
            $table->string('topic_type')->default('theory')->index();
            $table->unsignedInteger('duration_minutes')->nullable();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->boolean('is_required')->default(true)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->foreignId('created_by_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('updated_by_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['training_program_id', 'code']);
            $table->index(['training_program_id', 'topic_type', 'sort_order'], 'learning_topics_program_type_sort_idx');
        });
    }

    private function createTrainingGroupMembershipsTable(): void
    {
        if (Schema::hasTable('training_group_memberships')) {
            return;
        }

        Schema::create('training_group_memberships', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->nullable()->unique();
            $table->foreignId('training_group_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('student_profile_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('enrollment_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->string('status')->default('active')->index();
            $table->timestamp('joined_at')->nullable()->index();
            $table->timestamp('left_at')->nullable();
            $table->string('left_reason')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('updated_by_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['training_group_id', 'enrollment_id'], 'training_group_memberships_group_enrollment_unique');
            $table->index(['student_profile_id', 'status'], 'training_group_memberships_student_status_idx');
            $table->index(['training_group_id', 'status'], 'training_group_memberships_group_status_idx');
        });
    }

    private function createTrainingGroupSchedulePatternsTable(): void
    {
        if (Schema::hasTable('training_group_schedule_patterns')) {
            return;
        }

        Schema::create('training_group_schedule_patterns', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->nullable()->unique();
            $table->foreignId('training_group_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->json('title_translations')->nullable();
            $table->unsignedTinyInteger('day_of_week')->index();
            $table->time('starts_at');
            $table->time('ends_at');
            $table->string('lesson_type')->default('theory')->index();
            $table->string('classroom')->nullable();
            $table->foreignId('instructor_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->foreignId('created_by_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('updated_by_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['training_group_id', 'is_active', 'sort_order'], 'training_group_schedule_active_sort_idx');
        });
    }

    private function createTrainingGroupActivitiesTable(): void
    {
        if (Schema::hasTable('training_group_activities')) {
            return;
        }

        Schema::create('training_group_activities', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('training_group_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('enrollment_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('membership_id')->nullable()->constrained('training_group_memberships')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('student_profile_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->string('type')->index();
            $table->string('title')->nullable();
            $table->text('body')->nullable();
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['training_group_id', 'type'], 'training_group_activities_group_type_idx');
        });
    }

    /**
     * @param  array<int, string>  $columns
     */
    private function dropColumnsIfExist(Blueprint $table, string $tableName, array $columns): void
    {
        foreach ($columns as $column) {
            if (Schema::hasColumn($tableName, $column)) {
                $table->dropColumn($column);
            }
        }
    }
};
