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
            $table->json('title_translations')->nullable()->after('title');
            $table->text('short_description')->nullable()->after('description');
            $table->json('short_description_translations')->nullable()->after('short_description');
            $table->json('description_translations')->nullable()->after('short_description_translations');
            $table->unsignedInteger('old_price_cents')->nullable()->after('price_cents');
            $table->text('included_items')->nullable()->after('admission_requirements');
            $table->json('included_items_translations')->nullable()->after('included_items');
            $table->text('extra_costs')->nullable()->after('included_items_translations');
            $table->json('extra_costs_translations')->nullable()->after('extra_costs');
            $table->text('theory_program')->nullable()->after('extra_costs_translations');
            $table->json('theory_program_translations')->nullable()->after('theory_program');
            $table->text('practice_program')->nullable()->after('theory_program_translations');
            $table->json('practice_program_translations')->nullable()->after('practice_program');
            $table->json('seo_title_translations')->nullable()->after('seo_title');
            $table->json('seo_description_translations')->nullable()->after('meta_description');
            $table->string('image_path')->nullable()->after('open_graph_image');
            $table->string('og_title')->nullable()->after('image_path');
            $table->json('og_title_translations')->nullable()->after('og_title');
            $table->text('og_description')->nullable()->after('og_title_translations');
            $table->json('og_description_translations')->nullable()->after('og_description');
            $table->unsignedInteger('sort_order')->default(0)->after('structured_data')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('training_programs', function (Blueprint $table) {
            $table->dropColumn([
                'title_translations',
                'short_description',
                'short_description_translations',
                'description_translations',
                'old_price_cents',
                'included_items',
                'included_items_translations',
                'extra_costs',
                'extra_costs_translations',
                'theory_program',
                'theory_program_translations',
                'practice_program',
                'practice_program_translations',
                'seo_title_translations',
                'seo_description_translations',
                'image_path',
                'og_title',
                'og_title_translations',
                'og_description',
                'og_description_translations',
                'sort_order',
            ]);
        });
    }
};
