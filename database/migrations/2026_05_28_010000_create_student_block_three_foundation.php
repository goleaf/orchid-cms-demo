<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->createStudentStatusesTable();
        $this->createEnrollmentStatusesTable();
        $this->extendStudentProfilesTable();
        $this->extendEnrollmentsTable();
        $this->createStudentActivitiesTable();
        $this->createStudentTasksTable();
        $this->extendMarketingLeadsConversionFields();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_tasks');
        Schema::dropIfExists('student_activities');

        // Lead conversion columns may already belong to earlier CRM migrations, so rollback
        // leaves them intact instead of guessing ownership.

        if (Schema::hasTable('enrollments')) {
            Schema::table('enrollments', function (Blueprint $table): void {
                foreach ([
                    'lead_id',
                    'course_category_id',
                    'branch_id',
                    'status_id',
                    'manager_id',
                    'administrator_id',
                    'teacher_id',
                    'created_by_id',
                    'updated_by_id',
                ] as $column) {
                    if (Schema::hasColumn('enrollments', $column)) {
                        $table->dropForeign([$column]);
                    }
                }

                $this->dropColumnsIfExist($table, 'enrollments', [
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
                ]);
            });
        }

        if (Schema::hasTable('student_profiles')) {
            Schema::table('student_profiles', function (Blueprint $table): void {
                foreach ([
                    'user_id',
                    'status_id',
                    'manager_id',
                    'administrator_id',
                    'source_lead_id',
                    'source_id',
                    'created_by_id',
                    'updated_by_id',
                ] as $column) {
                    if (Schema::hasColumn('student_profiles', $column)) {
                        $table->dropForeign([$column]);
                    }
                }

                $this->dropColumnsIfExist($table, 'student_profiles', [
                    'uuid',
                    'student_number',
                    'user_id',
                    'full_name',
                    'middle_name',
                    'personal_code',
                    'gender',
                    'normalized_phone',
                    'preferred_messenger',
                    'telegram_username',
                    'whatsapp_phone',
                    'emergency_contact_name',
                    'emergency_contact_phone',
                    'city',
                    'locale',
                    'status_id',
                    'manager_id',
                    'administrator_id',
                    'source_lead_id',
                    'source_id',
                    'source_label',
                    'consent_accepted',
                    'consent_accepted_at',
                    'consent_text_version',
                    'comment',
                    'internal_comment',
                    'portal_access_created_at',
                    'documents_summary',
                    'payment_summary',
                    'created_by_id',
                    'updated_by_id',
                    'deleted_at',
                ]);
            });
        }

        Schema::dropIfExists('enrollment_statuses');
        Schema::dropIfExists('student_statuses');
    }

    private function createStudentStatusesTable(): void
    {
        if (Schema::hasTable('student_statuses')) {
            return;
        }

        Schema::create('student_statuses', function (Blueprint $table): void {
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
            $table->boolean('is_final')->default(false)->index();
            $table->boolean('is_blocked')->default(false)->index();
            $table->boolean('is_archived')->default(false)->index();
            $table->timestamps();
        });
    }

    private function createEnrollmentStatusesTable(): void
    {
        if (Schema::hasTable('enrollment_statuses')) {
            return;
        }

        Schema::create('enrollment_statuses', function (Blueprint $table): void {
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
            $table->boolean('is_final')->default(false)->index();
            $table->boolean('is_success')->default(false)->index();
            $table->boolean('is_cancelled')->default(false)->index();
            $table->boolean('is_waiting_documents')->default(false)->index();
            $table->boolean('is_waiting_payment')->default(false)->index();
            $table->boolean('is_in_progress')->default(false)->index();
            $table->timestamps();
        });
    }

    private function extendStudentProfilesTable(): void
    {
        if (! Schema::hasTable('student_profiles')) {
            return;
        }

        Schema::table('student_profiles', function (Blueprint $table): void {
            if (! Schema::hasColumn('student_profiles', 'uuid')) {
                $table->uuid('uuid')->nullable()->unique()->after('id');
            }

            if (! Schema::hasColumn('student_profiles', 'student_number')) {
                $table->string('student_number')->nullable()->unique()->after('uuid');
            }

            if (! Schema::hasColumn('student_profiles', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('student_number')->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            }

            if (! Schema::hasColumn('student_profiles', 'full_name')) {
                $table->string('full_name')->nullable()->after('user_id');
            }

            if (! Schema::hasColumn('student_profiles', 'middle_name')) {
                $table->string('middle_name')->nullable()->after('last_name');
            }

            if (! Schema::hasColumn('student_profiles', 'personal_code')) {
                $table->string('personal_code')->nullable()->after('national_id')->index();
            }

            if (! Schema::hasColumn('student_profiles', 'gender')) {
                $table->string('gender')->nullable()->after('personal_code');
            }

            if (! Schema::hasColumn('student_profiles', 'normalized_phone')) {
                $table->string('normalized_phone')->nullable()->after('phone')->index();
            }

            if (! Schema::hasColumn('student_profiles', 'preferred_messenger')) {
                $table->string('preferred_messenger')->nullable()->after('normalized_phone');
            }

            if (! Schema::hasColumn('student_profiles', 'telegram_username')) {
                $table->string('telegram_username')->nullable()->after('preferred_messenger');
            }

            if (! Schema::hasColumn('student_profiles', 'whatsapp_phone')) {
                $table->string('whatsapp_phone')->nullable()->after('telegram_username');
            }

            if (! Schema::hasColumn('student_profiles', 'emergency_contact_name')) {
                $table->string('emergency_contact_name')->nullable()->after('whatsapp_phone');
            }

            if (! Schema::hasColumn('student_profiles', 'emergency_contact_phone')) {
                $table->string('emergency_contact_phone')->nullable()->after('emergency_contact_name');
            }

            if (! Schema::hasColumn('student_profiles', 'city')) {
                $table->string('city')->nullable()->after('address');
            }

            if (! Schema::hasColumn('student_profiles', 'locale')) {
                $table->string('locale')->nullable()->after('city')->index();
            }

            if (! Schema::hasColumn('student_profiles', 'status_id')) {
                $table->foreignId('status_id')->nullable()->after('status')->constrained('student_statuses')->cascadeOnUpdate()->nullOnDelete();
            }

            if (! Schema::hasColumn('student_profiles', 'manager_id')) {
                $table->foreignId('manager_id')->nullable()->after('status_id')->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            }

            if (! Schema::hasColumn('student_profiles', 'administrator_id')) {
                $table->foreignId('administrator_id')->nullable()->after('manager_id')->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            }

            if (! Schema::hasColumn('student_profiles', 'source_lead_id')) {
                $table->foreignId('source_lead_id')->nullable()->after('administrator_id')->constrained('marketing_leads')->cascadeOnUpdate()->nullOnDelete();
            }

            if (! Schema::hasColumn('student_profiles', 'source_id')) {
                $table->foreignId('source_id')->nullable()->after('source_lead_id')->constrained('lead_sources')->cascadeOnUpdate()->nullOnDelete();
            }

            if (! Schema::hasColumn('student_profiles', 'source_label')) {
                $table->string('source_label')->nullable()->after('source_id');
            }

            if (! Schema::hasColumn('student_profiles', 'consent_accepted')) {
                $table->boolean('consent_accepted')->default(false)->after('source_label')->index();
            }

            if (! Schema::hasColumn('student_profiles', 'consent_accepted_at')) {
                $table->timestamp('consent_accepted_at')->nullable()->after('consent_accepted');
            }

            if (! Schema::hasColumn('student_profiles', 'consent_text_version')) {
                $table->string('consent_text_version')->nullable()->after('consent_accepted_at');
            }

            if (! Schema::hasColumn('student_profiles', 'comment')) {
                $table->text('comment')->nullable()->after('notes');
            }

            if (! Schema::hasColumn('student_profiles', 'internal_comment')) {
                $table->text('internal_comment')->nullable()->after('comment');
            }

            if (! Schema::hasColumn('student_profiles', 'portal_access_created_at')) {
                $table->timestamp('portal_access_created_at')->nullable()->after('internal_comment')->index();
            }

            if (! Schema::hasColumn('student_profiles', 'documents_summary')) {
                $table->json('documents_summary')->nullable()->after('portal_access_created_at');
            }

            if (! Schema::hasColumn('student_profiles', 'payment_summary')) {
                $table->json('payment_summary')->nullable()->after('documents_summary');
            }

            if (! Schema::hasColumn('student_profiles', 'created_by_id')) {
                $table->foreignId('created_by_id')->nullable()->after('payment_summary')->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            }

            if (! Schema::hasColumn('student_profiles', 'updated_by_id')) {
                $table->foreignId('updated_by_id')->nullable()->after('created_by_id')->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            }

            if (! Schema::hasColumn('student_profiles', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    private function extendEnrollmentsTable(): void
    {
        if (! Schema::hasTable('enrollments')) {
            return;
        }

        Schema::table('enrollments', function (Blueprint $table): void {
            if (! Schema::hasColumn('enrollments', 'uuid')) {
                $table->uuid('uuid')->nullable()->unique()->after('id');
            }

            if (! Schema::hasColumn('enrollments', 'enrollment_number')) {
                $table->string('enrollment_number')->nullable()->unique()->after('uuid');
            }

            if (! Schema::hasColumn('enrollments', 'lead_id')) {
                $table->foreignId('lead_id')->nullable()->after('student_profile_id')->constrained('marketing_leads')->cascadeOnUpdate()->nullOnDelete();
            }

            if (! Schema::hasColumn('enrollments', 'course_category_id')) {
                $table->foreignId('course_category_id')->nullable()->after('training_program_id')->constrained('course_categories')->cascadeOnUpdate()->nullOnDelete();
            }

            if (! Schema::hasColumn('enrollments', 'branch_id')) {
                $table->foreignId('branch_id')->nullable()->after('course_category_id')->constrained('branches')->cascadeOnUpdate()->nullOnDelete();
            }

            if (! Schema::hasColumn('enrollments', 'status_id')) {
                $table->foreignId('status_id')->nullable()->after('status')->constrained('enrollment_statuses')->cascadeOnUpdate()->nullOnDelete();
            }

            if (! Schema::hasColumn('enrollments', 'manager_id')) {
                $table->foreignId('manager_id')->nullable()->after('status_id')->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            }

            if (! Schema::hasColumn('enrollments', 'administrator_id')) {
                $table->foreignId('administrator_id')->nullable()->after('manager_id')->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            }

            if (! Schema::hasColumn('enrollments', 'teacher_id')) {
                $table->foreignId('teacher_id')->nullable()->after('instructor_id')->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            }

            if (! Schema::hasColumn('enrollments', 'start_date')) {
                $table->date('start_date')->nullable()->after('started_at')->index();
            }

            if (! Schema::hasColumn('enrollments', 'planned_end_date')) {
                $table->date('planned_end_date')->nullable()->after('start_date')->index();
            }

            if (! Schema::hasColumn('enrollments', 'actual_end_date')) {
                $table->date('actual_end_date')->nullable()->after('planned_end_date')->index();
            }

            if (! Schema::hasColumn('enrollments', 'preferred_time')) {
                $table->string('preferred_time')->nullable()->after('actual_end_date');
            }

            if (! Schema::hasColumn('enrollments', 'training_language')) {
                $table->string('training_language')->nullable()->after('preferred_time');
            }

            if (! Schema::hasColumn('enrollments', 'format')) {
                $table->string('format')->nullable()->after('training_language');
            }

            if (! Schema::hasColumn('enrollments', 'gearbox_type')) {
                $table->string('gearbox_type')->nullable()->after('format');
            }

            if (! Schema::hasColumn('enrollments', 'price')) {
                $table->decimal('price', 10, 2)->nullable()->after('paid_cents');
            }

            if (! Schema::hasColumn('enrollments', 'discount')) {
                $table->decimal('discount', 10, 2)->nullable()->after('price');
            }

            if (! Schema::hasColumn('enrollments', 'currency')) {
                $table->string('currency', 3)->default('EUR')->after('discount');
            }

            if (! Schema::hasColumn('enrollments', 'payment_status')) {
                $table->string('payment_status')->nullable()->after('currency')->index();
            }

            if (! Schema::hasColumn('enrollments', 'theory_progress')) {
                $table->decimal('theory_progress', 5, 2)->nullable()->after('payment_status');
            }

            if (! Schema::hasColumn('enrollments', 'practice_progress')) {
                $table->decimal('practice_progress', 5, 2)->nullable()->after('theory_progress');
            }

            if (! Schema::hasColumn('enrollments', 'total_theory_hours')) {
                $table->decimal('total_theory_hours', 8, 2)->nullable()->after('practice_progress');
            }

            if (! Schema::hasColumn('enrollments', 'completed_theory_hours')) {
                $table->decimal('completed_theory_hours', 8, 2)->nullable()->after('total_theory_hours');
            }

            if (! Schema::hasColumn('enrollments', 'total_practice_hours')) {
                $table->decimal('total_practice_hours', 8, 2)->nullable()->after('completed_theory_hours');
            }

            if (! Schema::hasColumn('enrollments', 'completed_practice_hours')) {
                $table->decimal('completed_practice_hours', 8, 2)->nullable()->after('total_practice_hours');
            }

            if (! Schema::hasColumn('enrollments', 'notes')) {
                $table->text('notes')->nullable()->after('completed_practice_hours');
            }

            if (! Schema::hasColumn('enrollments', 'internal_notes')) {
                $table->text('internal_notes')->nullable()->after('notes');
            }

            if (! Schema::hasColumn('enrollments', 'created_by_id')) {
                $table->foreignId('created_by_id')->nullable()->after('internal_notes')->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            }

            if (! Schema::hasColumn('enrollments', 'updated_by_id')) {
                $table->foreignId('updated_by_id')->nullable()->after('created_by_id')->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            }

            if (! Schema::hasColumn('enrollments', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    private function createStudentActivitiesTable(): void
    {
        if (Schema::hasTable('student_activities')) {
            return;
        }

        Schema::create('student_activities', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('student_id')->constrained('student_profiles')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('enrollment_id')->nullable()->constrained('enrollments')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained('marketing_leads')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->string('type')->index();
            $table->string('title')->nullable();
            $table->text('body')->nullable();
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'type']);
            $table->index(['student_id', 'created_at']);
            $table->index(['enrollment_id', 'type']);
        });
    }

    private function createStudentTasksTable(): void
    {
        if (Schema::hasTable('student_tasks')) {
            return;
        }

        Schema::create('student_tasks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('student_id')->constrained('student_profiles')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('enrollment_id')->nullable()->constrained('enrollments')->cascadeOnUpdate()->nullOnDelete();
            $table->json('title_translations')->nullable();
            $table->json('description_translations')->nullable();
            $table->foreignId('assigned_to_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('created_by_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->string('priority')->default('normal')->index();
            $table->string('status')->default('open')->index();
            $table->timestamp('due_at')->nullable()->index();
            $table->timestamp('completed_at')->nullable()->index();
            $table->timestamp('cancelled_at')->nullable()->index();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['student_id', 'status']);
            $table->index(['assigned_to_id', 'status']);
        });
    }

    private function extendMarketingLeadsConversionFields(): void
    {
        if (! Schema::hasTable('marketing_leads')) {
            return;
        }

        Schema::table('marketing_leads', function (Blueprint $table): void {
            if (! Schema::hasColumn('marketing_leads', 'converted_enrollment_id')) {
                $table->foreignId('converted_enrollment_id')->nullable()->after('converted_student_profile_id')->constrained('enrollments')->cascadeOnUpdate()->nullOnDelete();
            }
        });
    }

    /**
     * @param  array<int, string>  $columns
     */
    private function dropColumnsIfExist(Blueprint $table, string $tableName, array $columns): void
    {
        $existing = array_values(array_filter(
            $columns,
            fn (string $column): bool => Schema::hasColumn($tableName, $column),
        ));

        if ($existing !== []) {
            $table->dropColumn($existing);
        }
    }
};
