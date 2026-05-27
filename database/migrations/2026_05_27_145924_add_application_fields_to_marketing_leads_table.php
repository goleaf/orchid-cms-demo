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
            $table->foreignId('training_program_id')
                ->nullable()
                ->after('branch_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->foreignId('training_group_id')
                ->nullable()
                ->after('training_program_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->foreignId('instructor_id')
                ->nullable()
                ->after('training_group_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->string('preferred_format')->nullable()->after('license_category')->index();
            $table->string('preferred_language')->nullable()->after('preferred_format')->index();
            $table->string('preferred_time')->nullable()->after('preferred_language');
            $table->timestamp('privacy_accepted_at')->nullable()->after('preferred_time');
            $table->string('utm_source')->nullable()->after('message');
            $table->string('utm_medium')->nullable()->after('utm_source');
            $table->string('utm_campaign')->nullable()->after('utm_medium');
            $table->string('utm_term')->nullable()->after('utm_campaign');
            $table->string('utm_content')->nullable()->after('utm_term');
            $table->string('referrer_url')->nullable()->after('utm_content');

            $table->index(['training_program_id', 'status']);
            $table->index(['training_group_id', 'status']);
            $table->index(['instructor_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('marketing_leads', function (Blueprint $table) {
            $table->dropForeign(['training_program_id']);
            $table->dropForeign(['training_group_id']);
            $table->dropForeign(['instructor_id']);
            $table->dropIndex(['training_program_id', 'status']);
            $table->dropIndex(['training_group_id', 'status']);
            $table->dropIndex(['instructor_id', 'status']);
            $table->dropColumn([
                'training_program_id',
                'training_group_id',
                'instructor_id',
                'preferred_format',
                'preferred_language',
                'preferred_time',
                'privacy_accepted_at',
                'utm_source',
                'utm_medium',
                'utm_campaign',
                'utm_term',
                'utm_content',
                'referrer_url',
            ]);
        });
    }
};
