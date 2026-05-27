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
        Schema::table('branches', function (Blueprint $table) {
            $table->json('name_translations')->nullable()->after('name');
            $table->json('city_translations')->nullable()->after('city');
            $table->json('address_translations')->nullable()->after('address');
            $table->text('description')->nullable()->after('email');
            $table->json('description_translations')->nullable()->after('description');
            $table->string('working_hours')->nullable()->after('description_translations');
            $table->json('working_hours_translations')->nullable()->after('working_hours');
            $table->decimal('latitude', 10, 7)->nullable()->after('working_hours_translations');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->string('seo_title')->nullable()->after('longitude');
            $table->json('seo_title_translations')->nullable()->after('seo_title');
            $table->text('seo_description')->nullable()->after('seo_title_translations');
            $table->json('seo_description_translations')->nullable()->after('seo_description');
            $table->string('canonical_url')->nullable()->after('seo_description_translations');
            $table->string('open_graph_image')->nullable()->after('canonical_url');
            $table->string('og_title')->nullable()->after('open_graph_image');
            $table->json('og_title_translations')->nullable()->after('og_title');
            $table->text('og_description')->nullable()->after('og_title_translations');
            $table->json('og_description_translations')->nullable()->after('og_description');
            $table->unsignedInteger('sort_order')->default(0)->after('is_active')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn([
                'name_translations',
                'city_translations',
                'address_translations',
                'description',
                'description_translations',
                'working_hours',
                'working_hours_translations',
                'latitude',
                'longitude',
                'seo_title',
                'seo_title_translations',
                'seo_description',
                'seo_description_translations',
                'canonical_url',
                'open_graph_image',
                'og_title',
                'og_title_translations',
                'og_description',
                'og_description_translations',
                'sort_order',
            ]);
        });
    }
};
