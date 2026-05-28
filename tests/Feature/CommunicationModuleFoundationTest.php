<?php

namespace Tests\Feature;

use App\Actions\CreateInternalNotificationAction;
use App\Actions\CreateOrUpdateCommunicationReminderAction;
use App\Actions\LogNotificationDeliveryAction;
use App\Actions\LogStudentCommunicationAction;
use App\Actions\RenderCommunicationTemplateAction;
use App\Models\CommunicationReminder;
use App\Models\CommunicationTemplate;
use App\Models\Lead;
use App\Models\NotificationChannel;
use App\Models\NotificationDeliveryLog;
use App\Models\Student;
use App\Models\User;
use App\Rules\ActiveNotificationChannelRule;
use App\Services\Communication\PlaceholderChannelAdapter;
use App\Support\Access\SuperadminPermissions;
use Database\Seeders\CommunicationSeeder;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\StudentDictionarySeeder;
use Database\Seeders\SystemTranslationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class CommunicationModuleFoundationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app()->setLocale('en');
        $this->seed(LanguageSeeder::class);
        $this->seed(SystemTranslationSeeder::class);
        $this->seed(StudentDictionarySeeder::class);
        $this->seed(CommunicationSeeder::class);
    }

    public function test_communication_database_foundation_reuses_existing_crm_and_student_storage(): void
    {
        foreach ([
            'notifications',
            'notification_channels',
            'communication_templates',
            'communication_reminders',
            'notification_delivery_logs',
            'user_notification_preferences',
            'student_communications',
            'marketing_leads',
            'marketing_lead_communications',
            'student_profiles',
        ] as $table) {
            $this->assertTrue(Schema::hasTable($table), $table);
        }

        foreach (['code', 'driver', 'supports_templates', 'supports_scheduling', 'supports_delivery_status'] as $column) {
            $this->assertTrue(Schema::hasColumn('notification_channels', $column), $column);
        }

        foreach (['notification_channel_id', 'communication_template_id', 'marketing_lead_id', 'student_profile_id', 'status', 'due_at'] as $column) {
            $this->assertTrue(Schema::hasColumn('communication_reminders', $column), $column);
        }

        foreach (['recipient_email', 'recipient_phone', 'provider_status', 'student_communication_id', 'database_notification_id'] as $column) {
            $this->assertTrue(Schema::hasColumn('notification_delivery_logs', $column), $column);
        }

        $this->assertFalse(Schema::hasTable('tenants'));
        $this->assertFalse(Schema::hasTable('subscriptions'));
    }

    public function test_communication_seeders_are_idempotent_and_create_placeholder_channels(): void
    {
        $channelCount = NotificationChannel::query()->count();
        $templateCount = CommunicationTemplate::query()->count();

        $this->seed(CommunicationSeeder::class);

        $this->assertSame($channelCount, NotificationChannel::query()->count());
        $this->assertSame($templateCount, CommunicationTemplate::query()->count());
        $this->assertDatabaseHas('notification_channels', [
            'code' => NotificationChannel::CODE_SMS,
            'driver' => 'placeholder',
            'is_system' => true,
        ]);
        $this->assertDatabaseHas('communication_templates', [
            'code' => 'student-document-reminder',
            'type' => CommunicationTemplate::TYPE_STUDENT,
        ]);
    }

    public function test_actions_render_templates_log_history_and_keep_external_channels_as_placeholders(): void
    {
        $admin = $this->admin();
        $email = NotificationChannel::query()->where('code', NotificationChannel::CODE_EMAIL)->firstOrFail();
        $sms = NotificationChannel::query()->where('code', NotificationChannel::CODE_SMS)->firstOrFail();
        $template = CommunicationTemplate::query()->where('code', 'student-document-reminder')->firstOrFail();
        $lead = Lead::factory()->create(['first_name' => 'Ieva', 'last_name' => 'Norkute']);
        $student = Student::factory()->active()->create([
            'source_lead_id' => $lead->id,
            'first_name' => 'Ieva',
            'last_name' => 'Norkute',
            'full_name' => 'Ieva Norkute',
        ]);

        $rendered = app(RenderCommunicationTemplateAction::class)->handle($template, [
            'student_name' => 'Ieva',
        ], 'en');

        $this->assertSame('Hello Ieva. Please prepare the missing documents.', $rendered['body']);

        $communication = app(LogStudentCommunicationAction::class)->handle(
            $student,
            $admin,
            $email,
            'outbound',
            $rendered['subject'],
            $rendered['body'],
            ['source' => 'feature-test'],
            $template,
        );

        $this->assertDatabaseHas('student_communications', [
            'id' => $communication->id,
            'student_profile_id' => $student->id,
            'marketing_lead_id' => $lead->id,
            'notification_channel_id' => $email->id,
        ]);
        $this->assertDatabaseHas('student_activities', [
            'student_id' => $student->id,
            'type' => 'communication_logged',
        ]);

        $reminder = app(CreateOrUpdateCommunicationReminderAction::class)->handle(null, [
            'assigned_to_user_id' => $admin->id,
            'notification_channel_id' => $sms->id,
            'communication_template_id' => $template->id,
            'status' => CommunicationReminder::STATUS_SCHEDULED,
            'priority' => CommunicationReminder::PRIORITY_HIGH,
            'title_translations' => ['en' => 'Send document reminder'],
            'due_at' => now()->addHour(),
        ], $student);

        $this->assertTrue($student->communicationReminders()->whereKey($reminder->id)->exists());

        $log = app(LogNotificationDeliveryAction::class)->handle([
            'student_profile_id' => $student->id,
            'marketing_lead_id' => $lead->id,
            'student_communication_id' => $communication->id,
            'notification_channel_id' => $sms->id,
            'communication_template_id' => $template->id,
            'communication_reminder_id' => $reminder->id,
            'status' => NotificationDeliveryLog::STATUS_QUEUED,
            'recipient_phone' => $student->phone,
            'body' => 'Placeholder SMS',
            'queued_at' => now(),
            'created_by_id' => $admin->id,
        ], $student);

        $placeholder = app(PlaceholderChannelAdapter::class)->markAsPlaceholder($log);

        $this->assertSame(NotificationDeliveryLog::STATUS_SKIPPED, $placeholder->status);
        $this->assertSame('placeholder', $placeholder->provider_status);

        app(CreateInternalNotificationAction::class)->handle($admin, 'Internal title', 'Internal body', $admin);

        $this->assertDatabaseHas('notifications', [
            'notifiable_type' => $admin->getMorphClass(),
            'notifiable_id' => $admin->id,
            'type' => \App\Notifications\InternalCommunicationNotification::class,
        ]);
        $this->assertDatabaseHas('notification_delivery_logs', [
            'user_id' => $admin->id,
            'status' => NotificationDeliveryLog::STATUS_SENT,
            'provider' => 'database',
        ]);
    }

    public function test_validation_rules_and_form_requests_return_translated_errors(): void
    {
        $inactive = NotificationChannel::factory()->create([
            'code' => 'inactive_test',
            'is_active' => false,
        ]);

        $validator = Validator::make(
            ['channel' => $inactive->id],
            ['channel' => [new ActiveNotificationChannelRule]],
        );

        $this->assertTrue($validator->fails());
        $this->assertSame(tkey('communication.validation.channel_unavailable'), $validator->errors()->first('channel'));

        $this->actingAs($this->admin())
            ->from(route('platform.communications.channels'))
            ->post(route('platform.communications.channels', ['method' => 'save']), [
                'channel' => [
                    'code' => '',
                    'name_translations' => ['en' => ''],
                    'driver' => '',
                ],
            ])
            ->assertRedirect(route('platform.communications.channels'))
            ->assertSessionHasErrors([
                'channel.code' => tkey('communication.validation.channel_code_required'),
                'channel.name_translations' => tkey('communication.validation.default_translation_required'),
                'channel.driver' => tkey('communication.validation.channel_driver_required'),
            ]);
    }

    public function test_superadmin_can_open_communication_admin_screens(): void
    {
        $admin = $this->admin();

        collect([
            'platform.communications.channels' => tkey('communication.channels.title'),
            'platform.communications.templates' => tkey('communication.templates.title'),
            'platform.communications.templates.create' => tkey('communication.templates.create_title'),
            'platform.communications.reminders' => tkey('communication.reminders.title'),
            'platform.communications.delivery-logs' => tkey('communication.delivery_logs.title'),
        ])->each(function (string $label, string $routeName) use ($admin): void {
            $this->actingAs($admin)
                ->get(route($routeName))
                ->assertOk()
                ->assertSee($label);
        });

        foreach ([
            'communications.channels.view',
            'communications.channels.manage',
            'communications.templates.view',
            'communications.templates.manage',
            'communications.reminders.view',
            'communications.reminders.manage',
            'communications.delivery_logs.view',
            'communications.preferences.manage',
            'communications.student_history.manage',
            'communications.lead_history.view',
        ] as $permission) {
            $this->assertContains($permission, SuperadminPermissions::all());
        }
    }

    private function admin(): User
    {
        $admin = User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin-communications@example.test',
        ]);

        $admin->forceFill([
            'permissions' => SuperadminPermissions::enabled(),
        ])->save();

        return $admin;
    }
}
