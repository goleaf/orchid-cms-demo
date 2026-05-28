<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_channels', function (Blueprint $table): void {
            $table->id();
            $table->string('code')->unique();
            $table->json('name_translations')->nullable();
            $table->json('description_translations')->nullable();
            $table->string('driver')->default('placeholder');
            $table->string('provider')->nullable();
            $table->boolean('is_system')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('supports_internal')->default(false);
            $table->boolean('supports_external')->default(false);
            $table->boolean('supports_templates')->default(true);
            $table->boolean('supports_scheduling')->default(true);
            $table->boolean('supports_delivery_status')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->json('settings')->nullable();
            $table->foreignId('created_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });

        Schema::create('communication_templates', function (Blueprint $table): void {
            $table->id();
            $table->string('code')->nullable()->unique();
            $table->string('type')->default('general')->index();
            $table->foreignId('notification_channel_id')->nullable()->constrained('notification_channels')->nullOnDelete();
            $table->string('channel')->nullable()->index();
            $table->json('name_translations')->nullable();
            $table->json('subject_translations')->nullable();
            $table->json('body_translations')->nullable();
            $table->json('variable_keys')->nullable();
            $table->boolean('is_system')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->json('metadata')->nullable();
            $table->foreignId('created_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['channel', 'is_active', 'sort_order']);
            $table->index(['type', 'is_active', 'sort_order']);
        });

        Schema::create('user_notification_preferences', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('notification_channel_id')->constrained('notification_channels')->cascadeOnDelete();
            $table->string('event')->default('all');
            $table->boolean('is_enabled')->default(true);
            $table->time('quiet_hours_start')->nullable();
            $table->time('quiet_hours_end')->nullable();
            $table->unsignedSmallInteger('send_reminder_before_minutes')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'notification_channel_id', 'event'], 'user_channel_event_unique');
            $table->index(['notification_channel_id', 'event', 'is_enabled'], 'channel_event_enabled_index');
        });

        Schema::create('communication_reminders', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->nullableMorphs('remindable');
            $table->foreignId('marketing_lead_id')->nullable()->constrained('marketing_leads')->nullOnDelete();
            $table->foreignId('student_profile_id')->nullable()->constrained('student_profiles')->nullOnDelete();
            $table->foreignId('student_enrollment_id')->nullable()->constrained('enrollments')->nullOnDelete();
            $table->foreignId('assigned_to_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('notification_channel_id')->nullable()->constrained('notification_channels')->nullOnDelete();
            $table->foreignId('communication_template_id')->nullable()->constrained('communication_templates')->nullOnDelete();
            $table->string('status')->default('scheduled');
            $table->string('priority')->default('normal');
            $table->json('title_translations')->nullable();
            $table->json('body_translations')->nullable();
            $table->text('note')->nullable();
            $table->timestamp('due_at')->index();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('last_attempted_at')->nullable();
            $table->foreignId('created_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('completed_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['status', 'due_at']);
            $table->index(['assigned_to_user_id', 'status', 'due_at'], 'reminders_assignee_status_due_index');
            $table->index(['marketing_lead_id', 'due_at']);
            $table->index(['student_profile_id', 'due_at']);
        });

        Schema::create('student_communications', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('student_profile_id')->constrained('student_profiles')->cascadeOnDelete();
            $table->foreignId('student_enrollment_id')->nullable()->constrained('enrollments')->nullOnDelete();
            $table->foreignId('marketing_lead_id')->nullable()->constrained('marketing_leads')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('notification_channel_id')->nullable()->constrained('notification_channels')->nullOnDelete();
            $table->foreignId('communication_template_id')->nullable()->constrained('communication_templates')->nullOnDelete();
            $table->foreignId('communication_reminder_id')->nullable()->constrained('communication_reminders')->nullOnDelete();
            $table->string('channel');
            $table->string('direction')->default('outbound');
            $table->string('subject')->nullable();
            $table->text('body')->nullable();
            $table->timestamp('communicated_at')->index();
            $table->timestamp('client_replied_at')->nullable();
            $table->timestamp('callback_required_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['student_profile_id', 'communicated_at']);
            $table->index(['marketing_lead_id', 'communicated_at']);
            $table->index(['channel', 'communicated_at']);
        });

        Schema::create('notification_delivery_logs', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->nullableMorphs('notifiable');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('student_profile_id')->nullable()->constrained('student_profiles')->nullOnDelete();
            $table->foreignId('marketing_lead_id')->nullable()->constrained('marketing_leads')->nullOnDelete();
            $table->foreignId('student_communication_id')->nullable()->constrained('student_communications')->nullOnDelete();
            $table->foreignId('notification_channel_id')->nullable()->constrained('notification_channels')->nullOnDelete();
            $table->foreignId('communication_template_id')->nullable()->constrained('communication_templates')->nullOnDelete();
            $table->foreignId('communication_reminder_id')->nullable()->constrained('communication_reminders')->nullOnDelete();
            $table->uuid('database_notification_id')->nullable()->index();
            $table->string('direction')->default('outbound');
            $table->string('status')->default('queued');
            $table->string('recipient_name')->nullable();
            $table->string('recipient_email')->nullable();
            $table->string('recipient_phone')->nullable();
            $table->string('recipient_external_id')->nullable();
            $table->string('subject')->nullable();
            $table->text('body')->nullable();
            $table->string('provider')->nullable();
            $table->string('provider_message_id')->nullable();
            $table->string('provider_status')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('queued_at')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->foreignId('created_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['notification_channel_id', 'status'], 'delivery_channel_status_index');
            $table->index(['marketing_lead_id', 'created_at']);
            $table->index(['student_profile_id', 'created_at']);
            $table->index(['recipient_email', 'created_at']);
            $table->index(['recipient_phone', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_delivery_logs');
        Schema::dropIfExists('student_communications');
        Schema::dropIfExists('communication_reminders');
        Schema::dropIfExists('user_notification_preferences');
        Schema::dropIfExists('communication_templates');
        Schema::dropIfExists('notification_channels');
    }
};
