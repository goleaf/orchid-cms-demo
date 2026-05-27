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
        Schema::table('lead_statuses', function (Blueprint $table) {
            $table->string('color', 32)->default('#64748b')->after('name_translations');
            $table->boolean('is_default')->default(false)->after('is_active')->index();
            $table->boolean('is_final')->default(false)->after('is_default')->index();
            $table->boolean('is_success')->default(false)->after('is_final')->index();
            $table->boolean('is_lost')->default(false)->after('is_success')->index();
        });

        Schema::table('lead_sources', function (Blueprint $table) {
            $table->string('color', 32)->nullable()->after('name_translations');
        });

        Schema::table('lead_lost_reasons', function (Blueprint $table) {
            $table->string('color', 32)->nullable()->after('name_translations');
        });

        Schema::table('lead_tags', function (Blueprint $table) {
            $table->string('color', 32)->nullable()->after('name_translations');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lead_tags', function (Blueprint $table) {
            $table->dropColumn('color');
        });

        Schema::table('lead_lost_reasons', function (Blueprint $table) {
            $table->dropColumn('color');
        });

        Schema::table('lead_sources', function (Blueprint $table) {
            $table->dropColumn('color');
        });

        Schema::table('lead_statuses', function (Blueprint $table) {
            $table->dropColumn([
                'color',
                'is_default',
                'is_final',
                'is_success',
                'is_lost',
            ]);
        });
    }
};
