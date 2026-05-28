<?php

namespace Database\Seeders;

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
use App\Models\ReminderRule;
use App\Models\ReminderSchedule;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoNotificationSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'demo', 'testing'])) {
            return;
        }

        $this->call([
            NotificationChannelSeeder::class,
            NotificationTemplateSeeder::class,
            NotificationTemplateVariableSeeder::class,
            ReminderRuleSeeder::class,
        ]);

        $user = User::query()->updateOrCreate(
            ['email' => 'notifications.demo.manager@example.test'],
            [
                'name' => 'Notification Demo Manager',
                'password' => Hash::make('password'),
                'preferred_locale' => 'en',
            ],
        );

        $student = Student::query()->where('email', 'notifications.demo.student@example.test')->first()
            ?? Student::factory()->active()->create([
                'full_name' => 'Demo Notification Student',
                'first_name' => 'Demo',
                'last_name' => 'Student',
                'email' => 'notifications.demo.student@example.test',
                'phone' => '+37060020001',
                'locale' => 'en',
            ]);

        $lead = Lead::query()->where('email', 'notifications.demo.lead@example.test')->first()
            ?? Lead::factory()->fromWebsite()->create([
                'full_name' => 'Demo Notification Lead',
                'first_name' => 'Demo',
                'last_name' => 'Lead',
                'email' => 'notifications.demo.lead@example.test',
                'phone' => '+37060020002',
                'locale' => 'en',
            ]);

        $email = NotificationChannel::query()->where('code', 'email')->firstOrFail();
        $internal = NotificationChannel::query()->where('code', 'internal')->firstOrFail();
        $template = NotificationTemplate::query()->where('code', 'lesson_reminder')->firstOrFail();
        $version = $template->versions()->published()->firstOrFail();

        $message = NotificationMessage::query()->updateOrCreate(
            ['message_number' => 'MSG-DEMO-NOTIFICATION-001'],
            [
                'channel_id' => $email->id,
                'template_id' => $template->id,
                'template_version_id' => $version->id,
                'subject' => 'Lesson reminder',
                'body' => 'Hello Demo Notification Student. Your lesson is tomorrow at 10:00.',
                'priority' => NotificationMessage::PRIORITY_NORMAL,
                'status' => NotificationMessage::STATUS_DELIVERED,
                'sent_at' => now()->subHour(),
                'created_by_id' => $user->id,
                'metadata' => ['demo' => true],
            ],
        );

        $recipient = NotificationRecipient::query()->updateOrCreate(
            [
                'message_id' => $message->id,
                'student_id' => $student->id,
            ],
            [
                'user_id' => null,
                'lead_id' => null,
                'email' => $student->email,
                'phone' => $student->phone,
                'locale' => $student->locale,
                'status' => NotificationRecipient::STATUS_DELIVERED,
                'metadata' => ['demo' => true],
            ],
        );

        $delivery = NotificationDelivery::query()->updateOrCreate(
            [
                'message_id' => $message->id,
                'recipient_id' => $recipient->id,
                'channel_id' => $email->id,
                'attempt_no' => 1,
            ],
            [
                'status' => NotificationDelivery::STATUS_DELIVERED,
                'provider' => 'mail',
                'provider_message_id' => 'demo-mail-001',
                'sent_at' => now()->subHour(),
                'delivered_at' => now()->subMinutes(50),
                'failed_at' => null,
                'error_message' => null,
                'metadata' => ['demo' => true],
            ],
        );

        foreach ([
            ['user_id' => $user->id, 'student_id' => null, 'lead_id' => null, 'channel_id' => $internal->id, 'locale' => 'en'],
            ['user_id' => null, 'student_id' => $student->id, 'lead_id' => null, 'channel_id' => $email->id, 'locale' => $student->locale],
            ['user_id' => null, 'student_id' => null, 'lead_id' => $lead->id, 'channel_id' => $email->id, 'locale' => $lead->locale],
        ] as $preference) {
            NotificationPreference::query()->updateOrCreate(
                [
                    'user_id' => $preference['user_id'],
                    'student_id' => $preference['student_id'],
                    'lead_id' => $preference['lead_id'],
                    'channel_id' => $preference['channel_id'],
                ],
                [
                    'enabled' => true,
                    'locale' => $preference['locale'],
                    'settings' => ['demo' => true],
                ],
            );
        }

        $rule = ReminderRule::query()->where('code', 'lesson_tomorrow')->firstOrFail();

        ReminderSchedule::query()->updateOrCreate(
            [
                'rule_id' => $rule->id,
                'target_type' => 'student',
                'target_id' => $student->id,
            ],
            [
                'message_id' => $message->id,
                'scheduled_at' => now()->addDay()->setTime(9, 0),
                'status' => ReminderSchedule::STATUS_SCHEDULED,
                'processed_at' => null,
                'metadata' => ['demo' => true],
            ],
        );

        $thread = CommunicationThread::query()->updateOrCreate(
            ['thread_number' => 'THR-DEMO-NOTIFICATION-001'],
            [
                'subject' => 'Lesson coordination',
                'target_type' => 'student',
                'target_id' => $student->id,
                'student_id' => $student->id,
                'lead_id' => $lead->id,
                'status' => CommunicationThread::STATUS_OPEN,
                'metadata' => ['demo' => true],
            ],
        );

        $communicationMessage = CommunicationMessage::query()->updateOrCreate(
            [
                'thread_id' => $thread->id,
                'direction' => CommunicationMessage::DIRECTION_OUTBOUND,
                'body' => 'Please confirm the lesson time.',
            ],
            [
                'channel_id' => $email->id,
                'user_id' => $user->id,
                'student_id' => $student->id,
                'lead_id' => $lead->id,
                'sent_at' => now()->subMinutes(45),
                'metadata' => ['demo' => true],
            ],
        );

        CommunicationAttachment::query()->updateOrCreate(
            [
                'message_id' => $communicationMessage->id,
                'path' => 'communications/demo-lesson-plan.pdf',
            ],
            [
                'disk' => 'local',
                'original_name' => 'demo-lesson-plan.pdf',
                'mime_type' => 'application/pdf',
                'size' => 120_000,
                'metadata' => ['demo' => true],
            ],
        );

        NotificationActivity::query()->updateOrCreate(
            [
                'message_id' => $message->id,
                'recipient_id' => $recipient->id,
                'delivery_id' => $delivery->id,
                'activity_type' => NotificationActivity::TYPE_DELIVERED,
            ],
            [
                'user_id' => $user->id,
                'student_id' => $student->id,
                'lead_id' => null,
                'description' => 'Demo lesson reminder delivered.',
                'occurred_at' => now()->subMinutes(50),
                'metadata' => ['demo' => true],
            ],
        );
    }
}
