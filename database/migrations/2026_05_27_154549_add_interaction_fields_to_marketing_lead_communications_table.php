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
            $table->foreignId('marketing_message_template_id')
                ->nullable()
                ->after('user_id');
            $table->foreign('marketing_message_template_id', 'mlc_template_fk')
                ->references('id')
                ->on('marketing_message_templates')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->timestamp('client_replied_at')->nullable()->after('communicated_at')->index();
            $table->timestamp('callback_required_at')->nullable()->after('client_replied_at')->index();
            $table->string('call_recording_url', 500)->nullable()->after('callback_required_at');
            $table->string('call_recording_reference')->nullable()->after('call_recording_url')->index();

            $table->index(['marketing_lead_id', 'callback_required_at'], 'mlc_lead_callback_idx');
            $table->index(['channel', 'direction', 'communicated_at'], 'mlc_channel_direction_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('marketing_lead_communications', function (Blueprint $table) {
            $table->dropIndex('mlc_lead_callback_idx');
            $table->dropIndex('mlc_channel_direction_idx');
            $table->dropForeign('mlc_template_fk');
            $table->dropColumn([
                'client_replied_at',
                'callback_required_at',
                'call_recording_url',
                'call_recording_reference',
            ]);
        });
    }
};
