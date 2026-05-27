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
            $table->string('normalized_phone')->nullable()->after('phone')->index();
            $table->foreignId('assigned_by_user_id')->nullable()->after('responsible_manager_id')->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->timestamp('assigned_at')->nullable()->after('assigned_by_user_id')->index();
            $table->foreignId('duplicate_of_id')->nullable()->after('lost_reason_code')->constrained('marketing_leads')->cascadeOnUpdate()->nullOnDelete();
            $table->string('priority')->default('normal')->after('is_hot')->index();
            $table->unsignedTinyInteger('lead_score')->default(0)->after('priority')->index();
            $table->text('internal_comment')->nullable()->after('message');
            $table->timestamp('last_contacted_at')->nullable()->after('contacted_at')->index();
            $table->timestamp('closed_at')->nullable()->after('converted_at')->index();
            $table->boolean('consent_accepted')->default(false)->after('privacy_accepted_at')->index();
            $table->timestamp('consent_accepted_at')->nullable()->after('consent_accepted');
            $table->string('consent_text_version')->nullable()->after('consent_accepted_at');
            $table->foreignId('created_by_user_id')->nullable()->after('converted_student_profile_id')->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('updated_by_user_id')->nullable()->after('created_by_user_id')->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->softDeletes();

            $table->index(['normalized_phone', 'status'], 'marketing_leads_phone_status_idx');
            $table->index(['duplicate_of_id', 'status'], 'marketing_leads_duplicate_status_idx');
            $table->index(['responsible_manager_id', 'next_follow_up_at'], 'marketing_leads_manager_follow_up_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('marketing_leads', function (Blueprint $table) {
            $table->dropIndex('marketing_leads_phone_status_idx');
            $table->dropIndex('marketing_leads_duplicate_status_idx');
            $table->dropIndex('marketing_leads_manager_follow_up_idx');
            $table->dropForeign(['assigned_by_user_id']);
            $table->dropForeign(['duplicate_of_id']);
            $table->dropForeign(['created_by_user_id']);
            $table->dropForeign(['updated_by_user_id']);
            $table->dropSoftDeletes();
            $table->dropColumn([
                'normalized_phone',
                'assigned_by_user_id',
                'assigned_at',
                'duplicate_of_id',
                'priority',
                'lead_score',
                'internal_comment',
                'last_contacted_at',
                'closed_at',
                'consent_accepted',
                'consent_accepted_at',
                'consent_text_version',
                'created_by_user_id',
                'updated_by_user_id',
            ]);
        });
    }
};
