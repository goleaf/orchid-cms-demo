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
        Schema::table('vehicles', function (Blueprint $table) {
            $table->string('photo_path')->nullable()->after('instructor_id');
            $table->string('availability_summary')->nullable()->after('status');
            $table->text('description')->nullable()->after('availability_summary');
            $table->json('features')->nullable()->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn([
                'photo_path',
                'availability_summary',
                'description',
                'features',
            ]);
        });
    }
};
