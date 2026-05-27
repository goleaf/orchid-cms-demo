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
        Schema::table('marketing_lead_communications', function (Blueprint $table) {
            $table->string('call_result')->nullable()->after('call_recording_reference')->index();
            $table->unsignedInteger('duration_seconds')->nullable()->after('call_result');
        });

        Schema::table('marketing_lead_tasks', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('marketing_lead_tasks', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('marketing_lead_communications', function (Blueprint $table) {
            $table->dropColumn([
                'call_result',
                'duration_seconds',
            ]);
        });
    }
};
