<?php

namespace Tests\Feature;

use App\Models\CommunicationAttachment;
use App\Models\CommunicationMessage;
use App\Models\CommunicationThread;
use App\Models\Lead;
use App\Models\NotificationActivity;
use App\Models\NotificationChannel;
use App\Models\NotificationDelivery;
use App\Models\NotificationMessage;
use App\Models\NotificationPreference;
use App\Models\NotificationRecipient;
use App\Models\NotificationTemplate;
use App\Models\NotificationTemplateVariable;
use App\Models\NotificationTemplateVersion;
use App\Models\ReminderRule;
use App\Models\ReminderSchedule;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class NotificationDatabaseModelsTest extends TestCase
{
    use RefreshDatabase;

    public function test_requested_notification_database_tables_and_columns_exist(): void
    {
        foreach ([
            'notification_channels',
            'notification_templates',
            'notification_template_versions',
            'notification_template_variables',
            'notification_messages',
            'notification_recipients',
            'notification_deliveries',
            'notification_preferences',
            'reminder_rules',
            'reminder_schedules',
            'communication_threads',
            'communication_messages',
            'communication_attachments',
            'notification_activities',
        ] as $table) {
            $this->assertTrue(Schema::hasTable($table), $table);
        }

        $columns = [
            'notification_channels' => ['code', 'name_translations', 'description_translations', 'is_active', 'is_internal', 'is_email', 'is_sms_placeholder', 'is_whatsapp_placeholder', 'is_telegram_placeholder', 'is_push_placeholder'],
            'notification_templates' => ['code', 'channel_id', 'name_translations', 'description_translations', 'template_group', 'is_active', 'is_system'],
            'notification_template_versions' => ['template_id', 'version', 'status', 'subject_translations', 'body_translations', 'variables_schema', 'published_at', 'published_by_id'],
            'notification_template_variables' => ['template_id', 'key', 'label_translations', 'description_translations', 'type', 'is_required', 'default_value', 'sort_order'],
            'notification_messages' => ['message_number', 'channel_id', 'template_id', 'template_version_id', 'subject', 'body', 'priority', 'status', 'scheduled_at', 'sent_at', 'failed_at', 'created_by_id'],
            'notification_recipients' => ['message_id', 'user_id', 'student_id', 'lead_id', 'email', 'phone', 'locale', 'status'],
            'notification_deliveries' => ['message_id', 'recipient_id', 'channel_id', 'status', 'provider', 'provider_message_id', 'attempt_no', 'sent_at', 'delivered_at', 'failed_at', 'error_message'],
            'notification_preferences' => ['user_id', 'student_id', 'lead_id', 'channel_id', 'enabled', 'locale'],
            'reminder_rules' => ['code', 'name_translations', 'trigger_type', 'target_type', 'template_id', 'offset_minutes', 'is_active'],
            'reminder_schedules' => ['rule_id', 'target_type', 'target_id', 'message_id', 'scheduled_at', 'status'],
            'communication_threads' => ['thread_number', 'subject', 'target_type', 'target_id', 'student_id', 'lead_id', 'status'],
            'communication_messages' => ['thread_id', 'direction', 'channel_id', 'body', 'user_id', 'student_id', 'lead_id', 'sent_at'],
            'communication_attachments' => ['message_id', 'disk', 'path', 'original_name', 'mime_type', 'size'],
            'notification_activities' => ['message_id', 'recipient_id', 'delivery_id', 'user_id', 'student_id', 'lead_id', 'activity_type', 'description', 'occurred_at'],
        ];

        foreach ($columns as $table => $tableColumns) {
            foreach ($tableColumns as $column) {
                $this->assertTrue(Schema::hasColumn($table, $column), "{$table}.{$column}");
            }
        }

        foreach (['tenants', 'subscriptions', 'resellers', 'platform_billing_plans'] as $table) {
            $this->assertFalse(Schema::hasTable($table), $table);
        }
    }

    public function test_notification_models_create_relations_translations_and_scopes(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->active()->create([
            'email' => 'student-notification@example.test',
            'phone' => '+37060000001',
            'locale' => 'en',
        ]);
        $lead = Lead::factory()->fromWebsite()->create([
            'email' => 'lead-notification@example.test',
            'phone' => '+37060000002',
            'locale' => 'ru',
        ]);

        $channel = NotificationChannel::factory()->create([
            'code' => NotificationChannel::CODE_EMAIL,
            'name_translations' => ['en' => 'Email', 'lt' => 'El. pastas'],
            'is_internal' => false,
            'is_email' => true,
            'is_sms_placeholder' => false,
            'is_whatsapp_placeholder' => false,
            'is_telegram_placeholder' => false,
            'is_push_placeholder' => false,
        ]);

        $template = NotificationTemplate::factory()->forChannel($channel)->create([
            'code' => 'lesson_reminder',
            'name_translations' => ['en' => 'Lesson reminder', 'lt' => 'Pamokos priminimas'],
            'template_group' => 'lesson',
        ]);
        $version = NotificationTemplateVersion::factory()->published()->create([
            'template_id' => $template->id,
            'version' => 1,
            'subject_translations' => ['en' => 'Driving lesson soon'],
            'body_translations' => ['en' => 'Your lesson starts at {{ lesson_time }}.'],
            'published_by_id' => $user->id,
        ]);
        $variable = NotificationTemplateVariable::factory()->create([
            'template_id' => $template->id,
            'key' => 'lesson_time',
            'label_translations' => ['en' => 'Lesson time'],
            'sort_order' => 1,
        ]);
        $message = NotificationMessage::factory()->scheduled()->create([
            'message_number' => 'MSG-BLOCK11-001',
            'channel_id' => $channel->id,
            'template_id' => $template->id,
            'template_version_id' => $version->id,
            'subject' => 'Driving lesson soon',
            'body' => 'Your lesson starts at 10:00.',
            'created_by_id' => $user->id,
        ]);
        $studentRecipient = NotificationRecipient::factory()->create([
            'message_id' => $message->id,
            'student_id' => $student->id,
            'email' => $student->email,
            'phone' => $student->phone,
            'locale' => 'en',
        ]);
        $leadRecipient = NotificationRecipient::factory()->create([
            'message_id' => $message->id,
            'lead_id' => $lead->id,
            'email' => $lead->email,
            'phone' => $lead->phone,
            'locale' => 'ru',
        ]);
        $delivery = NotificationDelivery::factory()->create([
            'message_id' => $message->id,
            'recipient_id' => $studentRecipient->id,
            'channel_id' => $channel->id,
            'status' => NotificationDelivery::STATUS_DELIVERED,
            'provider' => 'placeholder',
            'provider_message_id' => 'placeholder-001',
            'sent_at' => now(),
            'delivered_at' => now(),
        ]);
        $preference = NotificationPreference::factory()->create([
            'student_id' => $student->id,
            'channel_id' => $channel->id,
            'enabled' => true,
            'locale' => 'en',
        ]);
        $rule = ReminderRule::factory()->create([
            'code' => 'lesson_minus_60',
            'name_translations' => ['en' => 'Lesson minus sixty minutes'],
            'trigger_type' => 'before_lesson',
            'target_type' => Student::class,
            'template_id' => $template->id,
            'offset_minutes' => -60,
        ]);
        $schedule = ReminderSchedule::factory()->create([
            'rule_id' => $rule->id,
            'target_type' => Student::class,
            'target_id' => $student->id,
            'message_id' => $message->id,
            'scheduled_at' => now()->subMinute(),
        ]);
        $thread = CommunicationThread::factory()->create([
            'thread_number' => 'THR-BLOCK11-001',
            'subject' => 'Lesson coordination',
            'target_type' => Student::class,
            'target_id' => $student->id,
            'student_id' => $student->id,
            'lead_id' => $lead->id,
        ]);
        $communicationMessage = CommunicationMessage::factory()->create([
            'thread_id' => $thread->id,
            'direction' => CommunicationMessage::DIRECTION_OUTBOUND,
            'channel_id' => $channel->id,
            'body' => 'Please confirm tomorrow lesson.',
            'user_id' => $user->id,
            'student_id' => $student->id,
            'lead_id' => $lead->id,
        ]);
        $attachment = CommunicationAttachment::factory()->create([
            'message_id' => $communicationMessage->id,
            'path' => 'communications/lesson-plan.pdf',
        ]);
        $activity = NotificationActivity::factory()->create([
            'message_id' => $message->id,
            'recipient_id' => $studentRecipient->id,
            'delivery_id' => $delivery->id,
            'user_id' => $user->id,
            'student_id' => $student->id,
            'activity_type' => NotificationActivity::TYPE_DELIVERED,
            'occurred_at' => now(),
        ]);

        $this->assertSame('Lesson reminder', $template->displayName('en'));
        $this->assertSame('Driving lesson soon', $version->subject('en'));
        $this->assertSame('Lesson time', $variable->displayLabel('en'));
        $this->assertSame('Lesson minus sixty minutes', $rule->displayName('en'));

        $this->assertTrue($channel->notificationTemplates()->whereKey($template)->exists());
        $this->assertTrue($template->versions()->whereKey($version)->exists());
        $this->assertTrue($template->variables()->whereKey($variable)->exists());
        $this->assertTrue($template->messages()->whereKey($message)->exists());
        $this->assertTrue($message->recipients()->whereKey($studentRecipient)->exists());
        $this->assertTrue($message->deliveries()->whereKey($delivery)->exists());
        $this->assertTrue($studentRecipient->student->is($student));
        $this->assertTrue($leadRecipient->lead->is($lead));
        $this->assertTrue($delivery->recipient->is($studentRecipient));
        $this->assertTrue($preference->student->is($student));
        $this->assertTrue($rule->schedules()->whereKey($schedule)->exists());
        $this->assertTrue($thread->messages()->whereKey($communicationMessage)->exists());
        $this->assertTrue($communicationMessage->attachments()->whereKey($attachment)->exists());
        $this->assertTrue($activity->delivery->is($delivery));

        $this->assertTrue($user->createdNotificationMessages()->whereKey($message)->exists());
        $this->assertTrue($student->notificationRecipients()->whereKey($studentRecipient)->exists());
        $this->assertTrue($student->communicationThreads()->whereKey($thread)->exists());
        $this->assertTrue($lead->notificationRecipients()->whereKey($leadRecipient)->exists());
        $this->assertTrue($lead->communicationMessages()->whereKey($communicationMessage)->exists());

        $this->assertTrue(NotificationChannel::query()->email()->whereKey($channel)->exists());
        $this->assertTrue(NotificationTemplate::query()->active()->forGroup('lesson')->whereKey($template)->exists());
        $this->assertTrue(NotificationTemplateVersion::query()->published()->whereKey($version)->exists());
        $this->assertTrue(NotificationTemplateVariable::query()->required()->whereKey($variable)->exists());
        $this->assertTrue(NotificationMessage::query()->scheduled()->whereKey($message)->exists());
        $this->assertTrue(NotificationRecipient::query()->pending()->forStudent($student)->whereKey($studentRecipient)->exists());
        $this->assertTrue(NotificationRecipient::query()->forLead($lead)->whereKey($leadRecipient)->exists());
        $this->assertTrue(NotificationDelivery::query()->delivered()->whereKey($delivery)->exists());
        $this->assertTrue(NotificationPreference::query()->enabled()->whereKey($preference)->exists());
        $this->assertTrue(ReminderRule::query()->active()->forTarget(Student::class)->whereKey($rule)->exists());
        $this->assertTrue(ReminderSchedule::query()->due()->whereKey($schedule)->exists());
        $this->assertTrue(CommunicationThread::query()->open()->forStudent($student)->whereKey($thread)->exists());
        $this->assertTrue(CommunicationMessage::query()->outbound()->whereKey($communicationMessage)->exists());
        $this->assertTrue(NotificationActivity::query()->forType(NotificationActivity::TYPE_DELIVERED)->whereKey($activity)->exists());
    }
}
