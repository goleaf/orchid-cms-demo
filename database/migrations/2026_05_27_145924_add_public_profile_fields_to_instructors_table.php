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
        Schema::table('instructors', function (Blueprint $table) {
            $table->string('photo_path')->nullable()->after('phone');
            $table->unsignedTinyInteger('experience_years')->default(1)->after('license_number');
            $table->decimal('rating', 3, 2)->default(5)->after('experience_years');
            $table->unsignedSmallInteger('review_count')->default(0)->after('rating');
            $table->json('languages')->nullable()->after('categories');
            $table->string('availability_summary')->nullable()->after('languages');
            $table->text('teaching_style')->nullable()->after('availability_summary');
            $table->text('bio')->nullable()->after('teaching_style');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('instructors', function (Blueprint $table) {
            $table->dropColumn([
                'photo_path',
                'experience_years',
                'rating',
                'review_count',
                'languages',
                'availability_summary',
                'teaching_style',
                'bio',
            ]);
        });
    }
};
