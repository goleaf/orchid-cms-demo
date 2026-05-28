<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addChannelFlags();

        Schema::create('notification_templates', function (Blueprint $table): void {
            $table->id();
            $table->string('code')->unique();
            $table->foreignId('channel_id')->nullable()->constrained('notification_channels')->nullOnDelete();
            $table->json('name_translations')->nullable();
            $table->json('description_translations')->nullable();
            $table->string('template_group')->default('general')->index();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_system')->default(false);
            $table->timestamps();

            $table->index(['channel_id', 'is_active']);
            $table->index(['template_group', 'is_active']);
        });

        Schema::create('notification_template_versions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('template_id')->constrained('notification_templates')->cascadeOnDelete();
            $table->unsignedInteger('version');
            $table->string('status')->default('draft')->index();
            $table->json('subject_translations')->nullable();
            $table->json('body_translations')->nullable();
            $table->json('variables_schema')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->foreignId('published_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['template_id', 'version']);
            $table->index(['template_id', 'status']);
        });

        Schema::create('notification_template_variables', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('template_id')->constrained('notification_templates')->cascadeOnDelete();
            $table->string('key');
            $table->json('label_translations')->nullable();
            $table->json('description_translations')->nullable();
            $table->string('type')->default('string');
            $table->boolean('is_required')->default(false);
            $table->string('default_value')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['template_id', 'key']);
            $table->index(['template_id', 'sort_order']);
        });

        Schema::create('notification_messages', function (Blueprint $table): void {
            $table->id();
            $table->string('message_number')->unique();
            $table->foreignId('channel_id')->constrained('notification_channels')->cascadeOnDelete();
            $table->foreignId('template_id')->nullable()->constrained('notification_templates')->nullOnDelete();
            $table->foreignId('template_version_id')->nullable()->constrained('notification_template_versions')->nullOnDelete();
            $table->string('subject')->nullable();
            $table->text('body');
            $table->string('priority')->default('normal')->index();
            $table->string('status')->default('draft')->index();
            $table->timestamp('scheduled_at')->nullable()->index();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->foreignId('created_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['channel_id', 'status']);
            $table->index(['template_id', 'status']);
        });

        Schema::create('notification_recipients', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('message_id')->constrained('notification_messages')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('student_id')->nullable()->constrained('student_profiles')->nullOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained('marketing_leads')->nullOnDelete();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('locale')->nullable();
            $table->string('status')->default('pending')->index();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['message_id', 'status']);
            $table->index(['student_id', 'created_at']);
            $table->index(['lead_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });

        Schema::create('notification_deliveries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('message_id')->constrained('notification_messages')->cascadeOnDelete();
            $table->foreignId('recipient_id')->constrained('notification_recipients')->cascadeOnDelete();
            $table->foreignId('channel_id')->constrained('notification_channels')->cascadeOnDelete();
            $table->string('status')->default('pending')->index();
            $table->string('provider')->nullable();
            $table->string('provider_message_id')->nullable();
            $table->unsignedInteger('attempt_no')->default(1);
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->text('error_message')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['message_id', 'status']);
            $table->index(['recipient_id', 'status']);
            $table->index(['channel_id', 'status']);
            $table->index(['provider', 'provider_message_id']);
        });

        Schema::create('notification_preferences', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('student_id')->nullable()->constrained('student_profiles')->nullOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained('marketing_leads')->nullOnDelete();
            $table->foreignId('channel_id')->constrained('notification_channels')->cascadeOnDelete();
            $table->boolean('enabled')->default(true);
            $table->string('locale')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->index(['channel_id', 'enabled']);
            $table->index(['user_id', 'channel_id']);
            $table->index(['student_id', 'channel_id']);
            $table->index(['lead_id', 'channel_id']);
        });

        Schema::create('reminder_rules', function (Blueprint $table): void {
            $table->id();
            $table->string('code')->unique();
            $table->json('name_translations')->nullable();
            $table->string('trigger_type')->index();
            $table->string('target_type')->index();
            $table->foreignId('template_id')->nullable()->constrained('notification_templates')->nullOnDelete();
            $table->integer('offset_minutes')->default(0);
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['target_type', 'trigger_type', 'is_active']);
        });

        Schema::create('reminder_schedules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('rule_id')->constrained('reminder_rules')->cascadeOnDelete();
            $table->string('target_type');
            $table->unsignedBigInteger('target_id');
            $table->foreignId('message_id')->nullable()->constrained('notification_messages')->nullOnDelete();
            $table->timestamp('scheduled_at')->index();
            $table->string('status')->default('scheduled')->index();
            $table->timestamp('processed_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['target_type', 'target_id']);
            $table->index(['rule_id', 'status', 'scheduled_at']);
        });

        Schema::create('communication_threads', function (Blueprint $table): void {
            $table->id();
            $table->string('thread_number')->unique();
            $table->string('subject')->nullable();
            $table->string('target_type');
            $table->unsignedBigInteger('target_id');
            $table->foreignId('student_id')->nullable()->constrained('student_profiles')->nullOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained('marketing_leads')->nullOnDelete();
            $table->string('status')->default('open')->index();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['target_type', 'target_id']);
            $table->index(['student_id', 'created_at']);
            $table->index(['lead_id', 'created_at']);
        });

        Schema::create('communication_messages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('thread_id')->constrained('communication_threads')->cascadeOnDelete();
            $table->string('direction')->default('outbound')->index();
            $table->foreignId('channel_id')->constrained('notification_channels')->cascadeOnDelete();
            $table->text('body');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('student_id')->nullable()->constrained('student_profiles')->nullOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained('marketing_leads')->nullOnDelete();
            $table->timestamp('sent_at')->nullable()->index();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['thread_id', 'sent_at']);
            $table->index(['channel_id', 'sent_at']);
        });

        Schema::create('communication_attachments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('message_id')->constrained('communication_messages')->cascadeOnDelete();
            $table->string('disk')->default('local');
            $table->string('path');
            $table->string('original_name')->nullable();
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['message_id', 'created_at']);
        });

        Schema::create('notification_activities', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('message_id')->nullable()->constrained('notification_messages')->nullOnDelete();
            $table->foreignId('recipient_id')->nullable()->constrained('notification_recipients')->nullOnDelete();
            $table->foreignId('delivery_id')->nullable()->constrained('notification_deliveries')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('student_id')->nullable()->constrained('student_profiles')->nullOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained('marketing_leads')->nullOnDelete();
            $table->string('activity_type')->index();
            $table->text('description')->nullable();
            $table->timestamp('occurred_at')->index();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['message_id', 'activity_type']);
            $table->index(['student_id', 'occurred_at']);
            $table->index(['lead_id', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_activities');
        Schema::dropIfExists('communication_attachments');
        Schema::dropIfExists('communication_messages');
        Schema::dropIfExists('communication_threads');
        Schema::dropIfExists('reminder_schedules');
        Schema::dropIfExists('reminder_rules');
        Schema::dropIfExists('notification_preferences');
        Schema::dropIfExists('notification_deliveries');
        Schema::dropIfExists('notification_recipients');
        Schema::dropIfExists('notification_messages');
        Schema::dropIfExists('notification_template_variables');
        Schema::dropIfExists('notification_template_versions');
        Schema::dropIfExists('notification_templates');

        $this->dropChannelFlags();
    }

    private function addChannelFlags(): void
    {
        $columns = [
            'is_internal' => false,
            'is_email' => false,
            'is_sms_placeholder' => false,
            'is_whatsapp_placeholder' => false,
            'is_telegram_placeholder' => false,
            'is_push_placeholder' => false,
        ];

        Schema::table('notification_channels', function (Blueprint $table) use ($columns): void {
            foreach ($columns as $column => $default) {
                if (! Schema::hasColumn('notification_channels', $column)) {
                    $table->boolean($column)->default($default);
                }
            }
        });
    }

    private function dropChannelFlags(): void
    {
        $columns = collect([
            'is_internal',
            'is_email',
            'is_sms_placeholder',
            'is_whatsapp_placeholder',
            'is_telegram_placeholder',
            'is_push_placeholder',
        ])
            ->filter(fn (string $column): bool => Schema::hasColumn('notification_channels', $column))
            ->all();

        if ($columns === []) {
            return;
        }

        Schema::table('notification_channels', function (Blueprint $table) use ($columns): void {
            $table->dropColumn($columns);
        });
    }
};
