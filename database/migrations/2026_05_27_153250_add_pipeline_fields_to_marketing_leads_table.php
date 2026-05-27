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
            $table->boolean('is_hot')->default(false)->after('budget_cents')->index();
            $table->timestamp('next_follow_up_at')->nullable()->after('is_hot')->index();
            $table->timestamp('last_status_changed_at')->nullable()->after('next_follow_up_at')->index();

            $table->index(['status', 'next_follow_up_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('marketing_leads', function (Blueprint $table) {
            $table->dropIndex(['status', 'next_follow_up_at']);
            $table->dropColumn([
                'is_hot',
                'next_follow_up_at',
                'last_status_changed_at',
            ]);
        });
    }
};
