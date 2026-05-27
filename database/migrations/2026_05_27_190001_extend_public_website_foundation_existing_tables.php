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
        Schema::table('training_programs', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->unique()->after('id');
            $table->foreignId('course_category_id')->nullable()->after('uuid')->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->string('code')->nullable()->unique()->after('course_category_id');
            $table->json('name_translations')->nullable()->after('title_translations');
            $table->json('program_summary_translations')->nullable()->after('description_translations');
            $table->json('includes_translations')->nullable()->after('included_items_translations');
            $table->json('excludes_translations')->nullable()->after('extra_costs_translations');
            $table->json('requirements_translations')->nullable()->after('admission_requirements');
            $table->decimal('price', 10, 2)->nullable()->after('old_price_cents');
            $table->decimal('old_price', 10, 2)->nullable()->after('price');
            $table->string('currency', 3)->default('EUR')->after('old_price');
            $table->json('duration_translations')->nullable()->after('duration_weeks');
            $table->string('icon')->nullable()->after('image_path');
            $table->string('og_image')->nullable()->after('open_graph_image');
            $table->boolean('is_visible_on_site')->default(true)->after('is_active')->index();
            $table->boolean('is_featured')->default(false)->after('is_visible_on_site')->index();
            $table->foreignId('created_by_id')->nullable()->after('sort_order')->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('updated_by_id')->nullable()->after('created_by_id')->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->softDeletes();

            $table->index(['is_active', 'is_visible_on_site', 'sort_order'], 'training_programs_public_visibility_idx');
            $table->index(['course_category_id', 'is_active'], 'training_programs_category_active_idx');
        });

        Schema::table('branches', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->unique()->after('id');
            $table->string('code')->nullable()->unique()->after('uuid');
            $table->text('map_url')->nullable()->after('longitude');
            $table->string('image')->nullable()->after('open_graph_image');
            $table->boolean('is_visible_on_site')->default(true)->after('is_active')->index();
            $table->foreignId('created_by_id')->nullable()->after('sort_order')->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('updated_by_id')->nullable()->after('created_by_id')->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->softDeletes();

            $table->index(['is_active', 'is_visible_on_site', 'sort_order'], 'branches_public_visibility_idx');
        });

        Schema::table('training_groups', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->unique()->after('id');
            $table->string('group_number')->nullable()->unique()->after('uuid');
            $table->foreignId('course_category_id')->nullable()->after('training_program_id')->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->json('description_translations')->nullable()->after('name_translations');
            $table->json('schedule_summary_translations')->nullable()->after('description_translations');
            $table->time('end_time')->nullable()->after('meeting_time');
            $table->boolean('is_featured')->default(false)->after('is_visible_on_site')->index();
            $table->unsignedInteger('sort_order')->default(0)->after('is_featured')->index();
            $table->foreignId('created_by_id')->nullable()->after('sort_order')->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('updated_by_id')->nullable()->after('created_by_id')->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->softDeletes();

            $table->index(['is_visible_on_site', 'status', 'starts_on'], 'training_groups_public_open_idx');
            $table->index(['branch_id', 'is_visible_on_site', 'status'], 'training_groups_branch_public_idx');
            $table->index(['training_program_id', 'is_visible_on_site', 'status'], 'training_groups_course_public_idx');
        });

        Schema::table('marketing_leads', function (Blueprint $table) {
            $table->string('full_name')->nullable()->after('uuid');
            $table->foreignId('course_category_id')->nullable()->after('training_program_id')->constrained()->cascadeOnUpdate()->nullOnDelete();

            $table->index(['course_category_id', 'status'], 'marketing_leads_course_category_status_idx');
        });

        Schema::table('student_reviews', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->unique()->after('id');
            $table->foreignId('branch_id')->nullable()->after('training_group_id')->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->json('name_translations')->nullable()->after('author_name');
            $table->json('text_translations')->nullable()->after('body');
            $table->string('image')->nullable()->after('text_translations');
            $table->boolean('is_active')->default(true)->after('status')->index();
            $table->boolean('is_featured')->default(false)->after('is_active')->index();
            $table->unsignedInteger('sort_order')->default(0)->after('published_at')->index();
            $table->foreignId('created_by_id')->nullable()->after('sort_order')->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('updated_by_id')->nullable()->after('created_by_id')->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->softDeletes();

            $table->index(['branch_id', 'status'], 'student_reviews_branch_status_idx');
            $table->index(['is_active', 'is_featured', 'published_at'], 'student_reviews_public_featured_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_reviews', function (Blueprint $table) {
            $table->dropIndex('student_reviews_branch_status_idx');
            $table->dropIndex('student_reviews_public_featured_idx');
            $table->dropForeign(['branch_id']);
            $table->dropForeign(['created_by_id']);
            $table->dropForeign(['updated_by_id']);
            $table->dropUnique(['uuid']);
            $table->dropSoftDeletes();
            $table->dropColumn([
                'uuid',
                'branch_id',
                'name_translations',
                'text_translations',
                'image',
                'is_active',
                'is_featured',
                'sort_order',
                'created_by_id',
                'updated_by_id',
            ]);
        });

        Schema::table('marketing_leads', function (Blueprint $table) {
            $table->dropIndex('marketing_leads_course_category_status_idx');
            $table->dropForeign(['course_category_id']);
            $table->dropColumn([
                'full_name',
                'course_category_id',
            ]);
        });

        Schema::table('training_groups', function (Blueprint $table) {
            $table->dropIndex('training_groups_public_open_idx');
            $table->dropIndex('training_groups_branch_public_idx');
            $table->dropIndex('training_groups_course_public_idx');
            $table->dropForeign(['course_category_id']);
            $table->dropForeign(['created_by_id']);
            $table->dropForeign(['updated_by_id']);
            $table->dropUnique(['uuid']);
            $table->dropUnique(['group_number']);
            $table->dropSoftDeletes();
            $table->dropColumn([
                'uuid',
                'group_number',
                'course_category_id',
                'description_translations',
                'schedule_summary_translations',
                'end_time',
                'is_featured',
                'sort_order',
                'created_by_id',
                'updated_by_id',
            ]);
        });

        Schema::table('branches', function (Blueprint $table) {
            $table->dropIndex('branches_public_visibility_idx');
            $table->dropForeign(['created_by_id']);
            $table->dropForeign(['updated_by_id']);
            $table->dropUnique(['uuid']);
            $table->dropUnique(['code']);
            $table->dropSoftDeletes();
            $table->dropColumn([
                'uuid',
                'code',
                'map_url',
                'image',
                'is_visible_on_site',
                'created_by_id',
                'updated_by_id',
            ]);
        });

        Schema::table('training_programs', function (Blueprint $table) {
            $table->dropIndex('training_programs_public_visibility_idx');
            $table->dropIndex('training_programs_category_active_idx');
            $table->dropForeign(['course_category_id']);
            $table->dropForeign(['created_by_id']);
            $table->dropForeign(['updated_by_id']);
            $table->dropUnique(['uuid']);
            $table->dropUnique(['code']);
            $table->dropSoftDeletes();
            $table->dropColumn([
                'uuid',
                'course_category_id',
                'code',
                'name_translations',
                'program_summary_translations',
                'includes_translations',
                'excludes_translations',
                'requirements_translations',
                'price',
                'old_price',
                'currency',
                'duration_translations',
                'icon',
                'og_image',
                'is_visible_on_site',
                'is_featured',
                'created_by_id',
                'updated_by_id',
            ]);
        });
    }
};
