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
        Schema::table('marketing_leads', function (Blueprint $table) {
            if (! Schema::hasColumn('marketing_leads', 'lead_number')) {
                $table->string('lead_number')->nullable()->after('uuid')->unique();
            }

            if (! Schema::hasColumn('marketing_leads', 'middle_name')) {
                $table->string('middle_name')->nullable()->after('last_name');
            }

            if (! Schema::hasColumn('marketing_leads', 'desired_start_date')) {
                $table->date('desired_start_date')->nullable()->after('preferred_time');
            }

            if (! Schema::hasColumn('marketing_leads', 'preferred_gearbox')) {
                $table->string('preferred_gearbox')->nullable()->after('desired_start_date');
            }

            if (! Schema::hasColumn('marketing_leads', 'converted_enrollment_id')) {
                $table->foreignId('converted_enrollment_id')
                    ->nullable()
                    ->after('converted_student_profile_id')
                    ->constrained('enrollments')
                    ->cascadeOnUpdate()
                    ->nullOnDelete();
            }
        });

        Schema::table('marketing_lead_tasks', function (Blueprint $table) {
            if (! Schema::hasColumn('marketing_lead_tasks', 'title_translations')) {
                $table->json('title_translations')->nullable()->after('title');
            }

            if (! Schema::hasColumn('marketing_lead_tasks', 'description_translations')) {
                $table->json('description_translations')->nullable()->after('title_translations');
            }

            if (! Schema::hasColumn('marketing_lead_tasks', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('completed_at')->index();
            }
        });

        foreach (['lead_statuses', 'lead_sources', 'lead_lost_reasons', 'lead_tags'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (! Schema::hasColumn($tableName, 'description_translations')) {
                    $table->json('description_translations')->nullable()->after('name_translations');
                }
            });
        }

        Schema::table('lead_statuses', function (Blueprint $table) {
            if (! Schema::hasColumn('lead_statuses', 'is_public')) {
                $table->boolean('is_public')->default(false)->after('is_active')->index();
            }

            if (! Schema::hasColumn('lead_statuses', 'is_duplicate')) {
                $table->boolean('is_duplicate')->default(false)->after('is_lost')->index();
            }

            if (! Schema::hasColumn('lead_statuses', 'is_spam')) {
                $table->boolean('is_spam')->default(false)->after('is_duplicate')->index();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lead_statuses', function (Blueprint $table) {
            foreach (['is_spam', 'is_duplicate', 'is_public'] as $column) {
                if (Schema::hasColumn('lead_statuses', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        foreach (['lead_tags', 'lead_lost_reasons', 'lead_sources', 'lead_statuses'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (Schema::hasColumn($tableName, 'description_translations')) {
                    $table->dropColumn('description_translations');
                }
            });
        }

        Schema::table('marketing_lead_tasks', function (Blueprint $table) {
            foreach (['cancelled_at', 'description_translations', 'title_translations'] as $column) {
                if (Schema::hasColumn('marketing_lead_tasks', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('marketing_leads', function (Blueprint $table) {
            if (Schema::hasColumn('marketing_leads', 'converted_enrollment_id')) {
                $table->dropForeign(['converted_enrollment_id']);
                $table->dropColumn('converted_enrollment_id');
            }

            foreach (['preferred_gearbox', 'desired_start_date', 'middle_name', 'lead_number'] as $column) {
                if (Schema::hasColumn('marketing_leads', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
