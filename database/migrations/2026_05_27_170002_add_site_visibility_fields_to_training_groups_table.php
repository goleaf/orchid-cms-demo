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
        Schema::table('training_groups', function (Blueprint $table) {
            $table->json('name_translations')->nullable()->after('name');
            $table->unsignedSmallInteger('places_taken')->default(0)->after('capacity');
            $table->boolean('is_visible_on_site')->default(true)->after('classroom')->index();

            $table->index(['is_visible_on_site', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('training_groups', function (Blueprint $table) {
            $table->dropIndex(['is_visible_on_site', 'status']);
            $table->dropColumn([
                'name_translations',
                'places_taken',
                'is_visible_on_site',
            ]);
        });
    }
};
