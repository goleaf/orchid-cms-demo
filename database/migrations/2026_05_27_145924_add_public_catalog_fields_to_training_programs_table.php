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
            $table->unsignedTinyInteger('duration_weeks')->default(12)->after('practice_hours');
            $table->string('format')->default('mixed')->index()->after('duration_weeks');
            $table->json('available_languages')->nullable()->after('format');
            $table->json('required_documents')->nullable()->after('description');
            $table->text('admission_requirements')->nullable()->after('required_documents');
            $table->string('seo_title')->nullable()->after('is_active');
            $table->text('meta_description')->nullable()->after('seo_title');
            $table->string('canonical_url')->nullable()->after('meta_description');
            $table->string('open_graph_image')->nullable()->after('canonical_url');
            $table->json('structured_data')->nullable()->after('open_graph_image');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('training_programs', function (Blueprint $table) {
            $table->dropColumn([
                'duration_weeks',
                'format',
                'available_languages',
                'required_documents',
                'admission_requirements',
                'seo_title',
                'meta_description',
                'canonical_url',
                'open_graph_image',
                'structured_data',
            ]);
        });
    }
};
