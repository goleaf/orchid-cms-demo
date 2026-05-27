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
        Schema::create('site_pages', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('type')->nullable()->index();
            $table->string('slug')->unique();
            $table->json('title_translations')->nullable();
            $table->json('subtitle_translations')->nullable();
            $table->json('content_translations')->nullable();
            $table->json('excerpt_translations')->nullable();
            $table->json('seo_title_translations')->nullable();
            $table->json('seo_description_translations')->nullable();
            $table->json('og_title_translations')->nullable();
            $table->json('og_description_translations')->nullable();
            $table->string('og_image')->nullable();
            $table->string('template')->nullable()->index();
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('is_indexable')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->foreignId('created_by_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('updated_by_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['type', 'is_active', 'published_at']);
        });

        Schema::create('course_categories', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('code')->nullable()->unique();
            $table->string('slug')->unique();
            $table->json('name_translations')->nullable();
            $table->json('description_translations')->nullable();
            $table->json('short_description_translations')->nullable();
            $table->json('seo_title_translations')->nullable();
            $table->json('seo_description_translations')->nullable();
            $table->string('image')->nullable();
            $table->string('icon')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('is_visible_on_site')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->foreignId('created_by_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('updated_by_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['is_active', 'is_visible_on_site', 'sort_order'], 'course_categories_visibility_idx');
        });

        Schema::create('pricing_packages', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('course_id')->nullable()->constrained('training_programs')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('course_category_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->string('code')->nullable()->unique();
            $table->string('slug')->nullable()->unique();
            $table->json('name_translations')->nullable();
            $table->json('description_translations')->nullable();
            $table->json('features_translations')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->decimal('old_price', 10, 2)->nullable();
            $table->string('currency', 3)->default('EUR');
            $table->decimal('theory_hours', 8, 2)->nullable();
            $table->decimal('practice_hours', 8, 2)->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('is_visible_on_site')->default(true)->index();
            $table->boolean('is_featured')->default(false)->index();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->foreignId('created_by_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('updated_by_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['course_id', 'is_active', 'is_visible_on_site'], 'pricing_packages_course_visibility_idx');
            $table->index(['course_category_id', 'is_active', 'is_visible_on_site'], 'pricing_packages_category_visibility_idx');
        });

        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->nullableMorphs('faqable');
            $table->json('question_translations')->nullable();
            $table->json('answer_translations')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->foreignId('created_by_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('updated_by_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });

        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('group')->nullable()->index();
            $table->json('value')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_public')->default(false)->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_settings');
        Schema::dropIfExists('faqs');
        Schema::dropIfExists('pricing_packages');
        Schema::dropIfExists('course_categories');
        Schema::dropIfExists('site_pages');
    }
};
