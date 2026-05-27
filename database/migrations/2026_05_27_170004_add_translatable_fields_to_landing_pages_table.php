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
        Schema::table('landing_pages', function (Blueprint $table) {
            $table->json('title_translations')->nullable()->after('title');
            $table->json('eyebrow_translations')->nullable()->after('eyebrow');
            $table->json('hero_title_translations')->nullable()->after('hero_title');
            $table->json('hero_summary_translations')->nullable()->after('hero_summary');
            $table->json('about_heading_translations')->nullable()->after('about_heading');
            $table->json('about_body_translations')->nullable()->after('about_body');
            $table->json('offer_one_title_translations')->nullable()->after('offer_one_title');
            $table->json('offer_one_body_translations')->nullable()->after('offer_one_body');
            $table->json('offer_two_title_translations')->nullable()->after('offer_two_title');
            $table->json('offer_two_body_translations')->nullable()->after('offer_two_body');
            $table->json('offer_three_title_translations')->nullable()->after('offer_three_title');
            $table->json('offer_three_body_translations')->nullable()->after('offer_three_body');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('landing_pages', function (Blueprint $table) {
            $table->dropColumn([
                'title_translations',
                'eyebrow_translations',
                'hero_title_translations',
                'hero_summary_translations',
                'about_heading_translations',
                'about_body_translations',
                'offer_one_title_translations',
                'offer_one_body_translations',
                'offer_two_title_translations',
                'offer_two_body_translations',
                'offer_three_title_translations',
                'offer_three_body_translations',
            ]);
        });
    }
};
