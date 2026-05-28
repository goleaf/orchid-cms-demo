<?php

namespace Tests\Feature;

use App\Models\CommunicationAttachment;
use App\Models\CommunicationMessage;
use App\Models\CommunicationThread;
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
use App\Models\TranslationString;
use Database\Seeders\NotificationChannelSeeder;
use Database\Seeders\NotificationSeeder;
use Database\Seeders\NotificationTemplateSeeder;
use Database\Seeders\NotificationTemplateVariableSeeder;
use Database\Seeders\NotificationTranslationSeeder;
use Database\Seeders\ReminderRuleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationFactoriesSeedersTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_factories_create_valid_records_with_required_states(): void
    {
        $internal = NotificationChannel::factory()->internal()->create();
        $email = NotificationChannel::factory()->email()->create();
        $sms = NotificationChannel::factory()->smsPlaceholder()->create();
        $whatsapp = NotificationChannel::factory()->whatsappPlaceholder()->create();
        $telegram = NotificationChannel::factory()->telegramPlaceholder()->create();
        $push = NotificationChannel::factory()->pushPlaceholder()->create();
        $inactive = NotificationChannel::factory()->inactive()->create(['code' => 'inactive_channel']);
        $translated = NotificationChannel::factory()->translated()->create(['code' => 'translated_channel']);

        $this->assertTrue($internal->is_internal);
        $this->assertTrue($email->is_email);
        $this->assertTrue($sms->is_sms_placeholder);
        $this->assertTrue($whatsapp->is_whatsapp_placeholder);
        $this->assertTrue($telegram->is_telegram_placeholder);
        $this->assertTrue($push->is_push_placeholder);
        $this->assertFalse($inactive->is_active);
        $this->assertSame('Translated channel', $translated->displayName('en'));

        foreach ([
            'appointmentReminder' => 'appointment_reminder',
            'paymentReminder' => 'payment_due',
            'documentRejected' => 'document_rejected',
            'lessonReminder' => 'lesson_reminder',
            'examReminder' => 'exam_reminder',
            'leadFollowUp' => 'lead_follow_up',
            'studentWelcome' => 'student_welcome',
            'contractGenerated' => 'contract_generated',
        ] as $state => $code) {
            $template = NotificationTemplate::factory()->{$state}()->active()->system()->create([
                'channel_id' => $email->id,
            ]);

            $this->assertSame($code, $template->code);
            $this->assertTrue($template->is_active);
            $this->assertTrue($template->is_system);
        }

        $template = NotificationTemplate::factory()->translated()->create([
            'code' => 'translated_template',
            'channel_id' => $email->id,
        ]);
        $version = NotificationTemplateVersion::factory()->published()->translated()->create([
            'template_id' => $template->id,
        ]);
        $variable = NotificationTemplateVariable::factory()->create([
            'template_id' => $template->id,
        ]);

        $this->assertSame('Translated template', $template->displayName('en'));
        $this->assertSame(NotificationTemplateVersion::STATUS_PUBLISHED, $version->status);
        $this->assertTrue($variable->is_required);

        foreach ([
            'draft' => NotificationMessage::STATUS_DRAFT,
            'scheduled' => NotificationMessage::STATUS_SCHEDULED,
            'queued' => NotificationMessage::STATUS_QUEUED,
            'sent' => NotificationMessage::STATUS_SENT,
            'delivered' => NotificationMessage::STATUS_DELIVERED,
            'failed' => NotificationMessage::STATUS_FAILED,
            'cancelled' => NotificationMessage::STATUS_CANCELLED,
        ] as $state => $status) {
            $message = NotificationMessage::factory()->{$state}()->create(['channel_id' => $email->id]);

            $this->assertSame($status, $message->status);
        }

        $this->assertSame(NotificationMessage::PRIORITY_URGENT, NotificationMessage::factory()->urgent()->create(['channel_id' => $email->id])->priority);
        $this->assertSame(NotificationMessage::PRIORITY_HIGH, NotificationMessage::factory()->high()->create(['channel_id' => $email->id])->priority);
        $this->assertSame(NotificationMessage::PRIORITY_NORMAL, NotificationMessage::factory()->normal()->create(['channel_id' => $email->id])->priority);

        $fromTemplate = NotificationMessage::factory()->fromTemplate($version)->create();
        $this->assertSame($template->id, $fromTemplate->template_id);
        $this->assertSame($version->id, $fromTemplate->template_version_id);

        $recipient = NotificationRecipient::factory()->create(['message_id' => $fromTemplate->id]);
        $preference = NotificationPreference::factory()->create(['channel_id' => $email->id]);

        foreach ([
            'queued' => NotificationDelivery::STATUS_QUEUED,
            'sent' => NotificationDelivery::STATUS_SENT,
            'delivered' => NotificationDelivery::STATUS_DELIVERED,
            'failed' => NotificationDelivery::STATUS_FAILED,
            'retryable' => NotificationDelivery::STATUS_FAILED,
            'placeholder' => NotificationDelivery::STATUS_QUEUED,
        ] as $state => $status) {
            $delivery = NotificationDelivery::factory()->{$state}()->create([
                'message_id' => $fromTemplate->id,
                'recipient_id' => $recipient->id,
                'channel_id' => $email->id,
            ]);

            $this->assertSame($status, $delivery->status);
        }

        $this->assertTrue($preference->enabled);

        foreach ([
            'lessonTomorrow' => 'lesson_tomorrow',
            'lessonOneHourBefore' => 'lesson_one_hour_before',
            'paymentDue' => 'payment_due',
            'documentMissing' => 'document_missing',
            'examReminder' => 'exam_reminder',
            'leadFollowUp' => 'lead_follow_up',
        ] as $state => $code) {
            $rule = ReminderRule::factory()->{$state}()->active()->create(['template_id' => $template->id]);

            $this->assertSame($code, $rule->code);
            $this->assertTrue($rule->is_active);
        }

        $rule = ReminderRule::query()->where('code', 'lesson_tomorrow')->firstOrFail();
        $schedule = ReminderSchedule::factory()->create(['rule_id' => $rule->id]);
        $thread = CommunicationThread::factory()->create();
        $communicationMessage = CommunicationMessage::factory()->create([
            'thread_id' => $thread->id,
            'channel_id' => $email->id,
        ]);
        $attachment = CommunicationAttachment::factory()->create(['message_id' => $communicationMessage->id]);
        $activity = NotificationActivity::factory()->create([
            'message_id' => $fromTemplate->id,
            'recipient_id' => $recipient->id,
        ]);

        $this->assertSame(ReminderSchedule::STATUS_SCHEDULED, $schedule->status);
        $this->assertTrue($thread->messages()->whereKey($communicationMessage)->exists());
        $this->assertTrue($communicationMessage->attachments()->whereKey($attachment)->exists());
        $this->assertTrue($fromTemplate->activities()->whereKey($activity)->exists());
    }

    public function test_notification_seeders_are_idempotent(): void
    {
        $seeders = [
            NotificationChannelSeeder::class,
            NotificationTemplateSeeder::class,
            NotificationTemplateVariableSeeder::class,
            ReminderRuleSeeder::class,
            NotificationTranslationSeeder::class,
            NotificationSeeder::class,
        ];

        foreach ($seeders as $seeder) {
            $this->seed($seeder);
        }

        $counts = $this->notificationSeedCounts();

        foreach ($seeders as $seeder) {
            $this->seed($seeder);
        }

        $this->assertSame($counts, $this->notificationSeedCounts());
    }

    public function test_default_notification_channels_templates_reminders_and_translations_exist(): void
    {
        app()->setLocale('en');

        $this->seed(NotificationSeeder::class);

        foreach ([
            'internal',
            'email',
            'sms_placeholder',
            'whatsapp_placeholder',
            'telegram_placeholder',
            'push_placeholder',
        ] as $code) {
            $channel = NotificationChannel::query()->where('code', $code)->firstOrFail();

            $this->assertTrue($channel->is_active);
            $this->assertSame(['ru', 'en', 'lt', 'pl'], array_keys($channel->name_translations));
        }

        foreach ([
            'student_welcome',
            'lead_follow_up',
            'lesson_reminder',
            'driving_lesson_reminder',
            'payment_due',
            'document_missing',
            'document_rejected',
            'exam_reminder',
            'contract_generated',
        ] as $code) {
            $template = NotificationTemplate::query()->where('code', $code)->firstOrFail();

            $this->assertTrue($template->is_active);
            $this->assertTrue($template->is_system);
            $this->assertTrue($template->versions()->published()->exists());
            $this->assertTrue($template->variables()->exists());
            $this->assertSame(['ru', 'en', 'lt', 'pl'], array_keys($template->name_translations));
        }

        foreach ([
            'lesson_tomorrow',
            'lesson_one_hour_before',
            'payment_due',
            'document_missing',
            'exam_reminder',
            'lead_follow_up',
        ] as $code) {
            $rule = ReminderRule::query()->where('code', $code)->firstOrFail();

            $this->assertTrue($rule->is_active);
            $this->assertNotNull($rule->template_id);
        }

        $translation = TranslationString::query()
            ->where('key', 'notifications.templates.student_welcome')
            ->with('values')
            ->firstOrFail();

        $this->assertEqualsCanonicalizing(['ru', 'en', 'lt', 'pl'], $translation->values->pluck('language_code')->all());
        $this->assertSame('Student welcome', tkey('notifications.templates.student_welcome'));
    }

    /**
     * @return array<string, int>
     */
    private function notificationSeedCounts(): array
    {
        return [
            'notification_channels' => NotificationChannel::query()->count(),
            'notification_templates' => NotificationTemplate::query()->count(),
            'notification_template_versions' => NotificationTemplateVersion::query()->count(),
            'notification_template_variables' => NotificationTemplateVariable::query()->count(),
            'reminder_rules' => ReminderRule::query()->count(),
            'notification_messages' => NotificationMessage::query()->count(),
            'notification_recipients' => NotificationRecipient::query()->count(),
            'notification_deliveries' => NotificationDelivery::query()->count(),
            'notification_preferences' => NotificationPreference::query()->count(),
            'reminder_schedules' => ReminderSchedule::query()->count(),
            'communication_threads' => CommunicationThread::query()->count(),
            'communication_messages' => CommunicationMessage::query()->count(),
            'communication_attachments' => CommunicationAttachment::query()->count(),
            'notification_activities' => NotificationActivity::query()->count(),
            'notification_translations' => TranslationString::query()->where('key', 'like', 'notifications.%')->count(),
        ];
    }
}
