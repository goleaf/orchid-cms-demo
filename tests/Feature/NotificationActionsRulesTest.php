<?php

namespace Tests\Feature;

use App\Actions\CreateMessageFromTemplateAction;
use App\Actions\MarkNotificationDeliveredAction;
use App\Actions\MarkNotificationFailedAction;
use App\Actions\ProcessDueRemindersAction;
use App\Actions\RenderNotificationTemplateAction;
use App\Actions\RetryNotificationDeliveryAction;
use App\Actions\ScheduleNotificationMessageAction;
use App\Actions\SendEmailNotificationAction;
use App\Actions\SendInternalNotificationAction;
use App\Actions\SendSmsPlaceholderNotificationAction;
use App\Actions\UpdateNotificationPreferenceAction;
use App\Models\CommunicationMessage;
use App\Models\NotificationChannel;
use App\Models\NotificationDelivery;
use App\Models\NotificationMessage;
use App\Models\NotificationRecipient;
use App\Models\NotificationTemplate;
use App\Models\NotificationTemplateVersion;
use App\Models\ReminderRule;
use App\Models\ReminderSchedule;
use App\Models\Student;
use App\Models\User;
use App\Notifications\InternalCommunicationNotification;
use App\Notifications\NotificationMessageMailNotification;
use App\Rules\ActiveNotificationChannelRule;
use App\Rules\NotificationPreferenceAllowedRule;
use App\Rules\NotificationRecipientRequiredRule;
use App\Rules\PublishedNotificationTemplateRule;
use App\Rules\SafeNotificationTemplateContentRule;
use App\Rules\ValidCommunicationDirectionRule;
use App\Rules\ValidNotificationPriorityRule;
use App\Rules\ValidReminderTriggerRule;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\SystemTranslationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class NotificationActionsRulesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app()->setLocale('en');
        $this->seed(LanguageSeeder::class);
        $this->seed(SystemTranslationSeeder::class);
    }

    public function test_templates_render_and_create_messages_with_target_recipients(): void
    {
        $student = Student::factory()->active()->create([
            'full_name' => 'Ieva Norkute',
            'first_name' => 'Ieva',
            'last_name' => 'Norkute',
            'email' => 'ieva.student@example.test',
            'phone' => '+37060010001',
            'locale' => 'en',
        ]);
        $channel = $this->channel('email_render_test', ['is_email' => true, 'driver' => 'mail']);
        $template = $this->publishedTemplate($channel, [
            'subject_translations' => ['en' => 'Lesson for {{ student_name }}'],
            'body_translations' => ['en' => 'Hello :student_name, lesson starts at {{ lesson_time }}.'],
        ]);

        $rendered = app(RenderNotificationTemplateAction::class)->handle($template, [
            'student_name' => 'Ieva Norkute',
            'lesson_time' => '10:30',
        ], 'en');

        $this->assertSame('Lesson for Ieva Norkute', $rendered['subject']);
        $this->assertSame('Hello Ieva Norkute, lesson starts at 10:30.', $rendered['body']);

        $message = app(CreateMessageFromTemplateAction::class)->handle($template, [
            'target_type' => 'student',
            'target_id' => $student->id,
            'variables' => ['lesson_time' => '10:30'],
            'locale' => 'en',
        ]);

        $this->assertSame(NotificationMessage::STATUS_DRAFT, $message->status);
        $this->assertSame('Lesson for Ieva Norkute', $message->subject);
        $this->assertSame('Hello Ieva Norkute, lesson starts at 10:30.', $message->body);
        $this->assertTrue($message->recipients->first()->student->is($student));
        $this->assertSame('ieva.student@example.test', $message->recipients->first()->email);
    }

    public function test_scheduling_and_due_reminders_create_queued_messages(): void
    {
        $student = Student::factory()->active()->create([
            'full_name' => 'Darius Petrauskas',
            'first_name' => 'Darius',
            'last_name' => 'Petrauskas',
            'email' => 'darius.student@example.test',
            'phone' => '+37060010002',
            'locale' => 'en',
        ]);
        $channel = $this->channel('email_due_reminder_test', ['is_email' => true, 'driver' => 'mail']);
        $template = $this->publishedTemplate($channel, [
            'body_translations' => ['en' => 'Reminder for {{ student_name }}.'],
        ]);
        $rule = ReminderRule::factory()->create([
            'code' => 'lesson_due_notification_actions',
            'trigger_type' => ReminderRule::TRIGGER_BEFORE_LESSON,
            'target_type' => Student::class,
            'template_id' => $template->id,
            'offset_minutes' => -60,
        ]);
        $schedule = ReminderSchedule::factory()->create([
            'rule_id' => $rule->id,
            'target_type' => Student::class,
            'target_id' => $student->id,
            'scheduled_at' => now()->subMinute(),
        ]);

        $result = app(ProcessDueRemindersAction::class)->handle();

        $schedule->refresh();

        $this->assertSame(['processed' => 1, 'failed' => 0], $result);
        $this->assertSame(ReminderSchedule::STATUS_SENT, $schedule->status);
        $this->assertNotNull($schedule->message_id);
        $this->assertSame(NotificationMessage::STATUS_QUEUED, $schedule->message->status);
        $this->assertSame('Reminder for Darius Petrauskas.', $schedule->message->body);
    }

    public function test_internal_email_and_placeholder_senders_create_delivery_records(): void
    {
        Notification::fake();

        $admin = User::factory()->create(['email' => 'admin@example.test']);
        $internalChannel = $this->channel('internal_action_test', [
            'driver' => 'database',
            'is_internal' => true,
            'supports_internal' => true,
            'supports_external' => false,
        ]);
        $internalMessage = NotificationMessage::factory()->create([
            'channel_id' => $internalChannel->id,
            'subject' => 'Internal title',
            'body' => 'Internal body',
        ]);
        NotificationRecipient::factory()->create([
            'message_id' => $internalMessage->id,
            'user_id' => $admin->id,
            'email' => $admin->email,
        ]);

        app(ScheduleNotificationMessageAction::class)->handle($internalMessage, now()->addMinutes(10), $admin);
        $internalMessage = app(SendInternalNotificationAction::class)->handle($internalMessage->refresh(), $admin);

        Notification::assertSentTo($admin, InternalCommunicationNotification::class);
        $this->assertSame(NotificationMessage::STATUS_SENT, $internalMessage->status);
        $this->assertDatabaseHas('notification_deliveries', [
            'message_id' => $internalMessage->id,
            'status' => NotificationDelivery::STATUS_SENT,
            'provider' => 'database',
        ]);

        $emailChannel = $this->channel('email_action_test', ['is_email' => true, 'driver' => 'mail']);
        $emailMessage = NotificationMessage::factory()->create([
            'channel_id' => $emailChannel->id,
            'subject' => 'Email title',
            'body' => 'Email body',
        ]);
        NotificationRecipient::factory()->create([
            'message_id' => $emailMessage->id,
            'email' => 'student.email@example.test',
        ]);

        $emailMessage = app(SendEmailNotificationAction::class)->handle($emailMessage);

        Notification::assertSentOnDemand(NotificationMessageMailNotification::class);
        $this->assertSame(NotificationMessage::STATUS_SENT, $emailMessage->status);
        $this->assertDatabaseHas('notification_deliveries', [
            'message_id' => $emailMessage->id,
            'status' => NotificationDelivery::STATUS_SENT,
        ]);

        $smsChannel = $this->channel('sms_placeholder_action_test', [
            'driver' => 'placeholder',
            'is_sms_placeholder' => true,
        ]);
        $smsMessage = NotificationMessage::factory()->create([
            'channel_id' => $smsChannel->id,
            'body' => 'SMS placeholder body',
        ]);
        NotificationRecipient::factory()->create([
            'message_id' => $smsMessage->id,
            'phone' => '+37060010003',
        ]);

        $smsMessage = app(SendSmsPlaceholderNotificationAction::class)->handle($smsMessage);

        $this->assertSame(NotificationMessage::STATUS_QUEUED, $smsMessage->status);
        $this->assertSame(NotificationRecipient::STATUS_QUEUED, $smsMessage->recipients->first()->status);
        $this->assertSame(NotificationDelivery::STATUS_QUEUED, $smsMessage->deliveries->first()->status);
        $this->assertSame('sms_placeholder', $smsMessage->deliveries->first()->provider);
        $this->assertTrue($smsMessage->deliveries->first()->metadata['placeholder']);
    }

    public function test_delivery_markers_retry_and_preferences_update_foundation_records(): void
    {
        $student = Student::factory()->active()->create([
            'email' => 'preference.student@example.test',
            'phone' => '+37060010004',
            'locale' => 'lt',
        ]);
        $channel = $this->channel('retry_preference_test', ['is_email' => true, 'driver' => 'mail']);
        $message = NotificationMessage::factory()->create([
            'channel_id' => $channel->id,
            'status' => NotificationMessage::STATUS_SENT,
        ]);
        $recipient = NotificationRecipient::factory()->create([
            'message_id' => $message->id,
            'student_id' => $student->id,
            'email' => $student->email,
        ]);
        $delivery = NotificationDelivery::factory()->create([
            'message_id' => $message->id,
            'recipient_id' => $recipient->id,
            'channel_id' => $channel->id,
            'status' => NotificationDelivery::STATUS_SENT,
            'provider' => 'mail',
        ]);

        $delivered = app(MarkNotificationDeliveredAction::class)->handle($delivery);

        $this->assertSame(NotificationDelivery::STATUS_DELIVERED, $delivered->status);
        $this->assertSame(NotificationMessage::STATUS_DELIVERED, $message->refresh()->status);

        $failedMessage = NotificationMessage::factory()->create(['channel_id' => $channel->id]);
        $failedRecipient = NotificationRecipient::factory()->create(['message_id' => $failedMessage->id]);
        $failedDelivery = NotificationDelivery::factory()->create([
            'message_id' => $failedMessage->id,
            'recipient_id' => $failedRecipient->id,
            'channel_id' => $channel->id,
            'status' => NotificationDelivery::STATUS_SENT,
            'provider' => 'mail',
        ]);

        $failedDelivery = app(MarkNotificationFailedAction::class)->handle($failedDelivery, 'Temporary failure');
        $retry = app(RetryNotificationDeliveryAction::class)->handle($failedDelivery);

        $this->assertSame(NotificationDelivery::STATUS_FAILED, $failedDelivery->status);
        $this->assertSame(NotificationDelivery::STATUS_QUEUED, $retry->status);
        $this->assertSame(2, $retry->attempt_no);
        $this->assertSame($failedDelivery->id, $retry->metadata['retried_from_delivery_id']);
        $this->assertSame(NotificationMessage::STATUS_QUEUED, $failedMessage->refresh()->status);

        $preference = app(UpdateNotificationPreferenceAction::class)->handle([
            'student_id' => $student->id,
            'channel_id' => $channel->id,
            'enabled' => false,
            'locale' => 'lt',
        ]);

        $this->assertFalse($preference->enabled);
        $this->assertSame('lt', $preference->locale);
        $this->assertTrue($preference->student->is($student));
    }

    public function test_notification_rules_return_notification_translation_keys(): void
    {
        $inactiveChannel = $this->channel('inactive_notification_rule_test', ['is_active' => false]);
        $draftTemplate = NotificationTemplate::factory()->create([
            'code' => 'draft_notification_rule_template',
        ]);

        $validator = Validator::make(
            [
                'channel_id' => $inactiveChannel->id,
                'template_id' => $draftTemplate->id,
                'body' => '<script>alert(1)</script>',
                'recipient' => [],
                'priority' => 'immediate',
                'direction' => 'sideways',
                'trigger_type' => 'after_subscription_renewal',
                'preference' => ['channel_id' => $inactiveChannel->id],
            ],
            [
                'channel_id' => [new ActiveNotificationChannelRule(messageKey: 'notifications.validation.channel_unavailable')],
                'template_id' => [new PublishedNotificationTemplateRule],
                'body' => [new SafeNotificationTemplateContentRule],
                'recipient' => [new NotificationRecipientRequiredRule],
                'priority' => [new ValidNotificationPriorityRule],
                'direction' => [new ValidCommunicationDirectionRule],
                'trigger_type' => [new ValidReminderTriggerRule],
                'preference' => [new NotificationPreferenceAllowedRule],
            ],
        );

        $this->assertTrue($validator->fails());
        $this->assertSame(tkey('notifications.validation.channel_unavailable'), $validator->errors()->first('channel_id'));
        $this->assertSame(tkey('notifications.validation.template_not_published'), $validator->errors()->first('template_id'));
        $this->assertSame(tkey('notifications.validation.unsafe_template_content'), $validator->errors()->first('body'));
        $this->assertSame(tkey('notifications.validation.recipient_required'), $validator->errors()->first('recipient'));
        $this->assertSame(tkey('notifications.validation.invalid_priority'), $validator->errors()->first('priority'));
        $this->assertSame(tkey('notifications.validation.invalid_communication_direction'), $validator->errors()->first('direction'));
        $this->assertSame(tkey('notifications.validation.invalid_reminder_trigger'), $validator->errors()->first('trigger_type'));
        $this->assertSame(tkey('notifications.validation.preference_target_required'), $validator->errors()->first('preference'));

        $this->assertContains(NotificationMessage::PRIORITY_LOW, NotificationMessage::priorityValues());
        $this->assertContains(NotificationMessage::STATUS_ARCHIVED, NotificationMessage::statusValues());
        $this->assertContains(CommunicationMessage::DIRECTION_INTERNAL, CommunicationMessage::directionValues());
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function channel(string $code, array $overrides = []): NotificationChannel
    {
        return NotificationChannel::factory()->create([
            'code' => $code,
            'name_translations' => ['en' => str($code)->replace('_', ' ')->title()->toString()],
            'driver' => $overrides['driver'] ?? $code,
            'is_active' => $overrides['is_active'] ?? true,
            'is_internal' => $overrides['is_internal'] ?? false,
            'is_email' => $overrides['is_email'] ?? false,
            'is_sms_placeholder' => $overrides['is_sms_placeholder'] ?? false,
            'is_whatsapp_placeholder' => $overrides['is_whatsapp_placeholder'] ?? false,
            'is_telegram_placeholder' => $overrides['is_telegram_placeholder'] ?? false,
            'is_push_placeholder' => $overrides['is_push_placeholder'] ?? false,
            'supports_internal' => $overrides['supports_internal'] ?? false,
            'supports_external' => $overrides['supports_external'] ?? true,
            'supports_templates' => $overrides['supports_templates'] ?? true,
            'supports_scheduling' => $overrides['supports_scheduling'] ?? true,
            'supports_delivery_status' => $overrides['supports_delivery_status'] ?? true,
        ]);
    }

    /**
     * @param  array<string, mixed>  $versionOverrides
     */
    private function publishedTemplate(NotificationChannel $channel, array $versionOverrides = []): NotificationTemplate
    {
        $template = NotificationTemplate::factory()->forChannel($channel)->create([
            'code' => 'notification_actions_'.$channel->code,
            'template_group' => 'lesson',
        ]);

        NotificationTemplateVersion::factory()->published()->create([
            'template_id' => $template->id,
            'subject_translations' => ['en' => 'Notification for {{ target_name }}'],
            'body_translations' => ['en' => 'Hello {{ target_name }}.'],
            ...$versionOverrides,
        ]);

        return $template->refresh();
    }
}
