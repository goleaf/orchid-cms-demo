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
        Schema::table('marketing_leads', function (Blueprint $table) {
            $table->foreignId('responsible_manager_id')
                ->nullable()
                ->after('marketing_campaign_id')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->string('messenger')->nullable()->after('phone');
            $table->string('city')->nullable()->after('messenger')->index();
            $table->unsignedInteger('budget_cents')->nullable()->after('preferred_time');
            $table->text('rejection_reason')->nullable()->after('message');
            $table->json('crm_snapshot')->nullable()->after('rejection_reason');

            $table->index(['responsible_manager_id', 'status']);
            $table->index(['city', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('marketing_leads', function (Blueprint $table) {
            $table->dropForeign(['responsible_manager_id']);
            $table->dropIndex(['responsible_manager_id', 'status']);
            $table->dropIndex(['city', 'status']);
            $table->dropColumn([
                'responsible_manager_id',
                'messenger',
                'city',
                'budget_cents',
                'rejection_reason',
                'crm_snapshot',
            ]);
        });
    }
};
