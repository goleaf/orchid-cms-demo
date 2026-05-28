<?php

namespace Tests\Feature;

use App\Models\TranslationString;
use App\Orchid\PlatformProvider;
use App\Support\Access\SuperadminPermissions;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\NotificationTranslationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationLocalizationPermissionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app()->setLocale('en');
        $this->seed([LanguageSeeder::class, NotificationTranslationSeeder::class]);
    }

    public function test_requested_notification_translation_keys_exist_for_all_active_locales(): void
    {
        $keys = [
            ...$this->prefixed('menu.notifications', [
                '',
                '.messages',
                '.templates',
                '.reminders',
                '.deliveries',
                '.threads',
                '.preferences',
                '.channels',
                '.settings',
            ]),
            ...$this->prefixed('notifications.fields.', [
                'channel',
                'template',
                'template_version',
                'subject',
                'body',
                'priority',
                'status',
                'recipient',
                'user',
                'student',
                'lead',
                'email',
                'phone',
                'locale',
                'scheduled_at',
                'sent_at',
                'delivered_at',
                'failed_at',
                'provider',
                'provider_message_id',
                'attempt_no',
                'error_message',
            ]),
            ...$this->prefixed('notifications.actions.', [
                'create',
                'save',
                'send',
                'schedule',
                'cancel',
                'retry',
                'mark_delivered',
                'mark_failed',
                'preview',
                'publish_version',
                'create_thread',
                'add_message',
                'process_due_reminders',
            ]),
            ...$this->prefixed('notifications.channels.', [
                'internal',
                'email',
                'sms_placeholder',
                'whatsapp_placeholder',
                'telegram_placeholder',
                'push_placeholder',
            ]),
            ...$this->prefixed('notifications.statuses.', [
                'draft',
                'scheduled',
                'queued',
                'sent',
                'delivered',
                'failed',
                'cancelled',
                'archived',
            ]),
            ...$this->prefixed('notifications.priorities.', [
                'low',
                'normal',
                'high',
                'urgent',
            ]),
            ...$this->prefixed('notifications.validation.', [
                'channel_not_active',
                'template_not_published',
                'unsafe_template_content',
                'recipient_required',
                'invalid_target',
                'message_cannot_be_sent',
                'delivery_cannot_be_retried',
                'invalid_reminder_trigger',
                'invalid_schedule_date',
                'preference_not_allowed',
                'invalid_priority',
                'invalid_direction',
            ]),
            'permissions.groups.notifications',
            ...$this->prefixed('permissions.', $this->notificationPermissions()),
        ];

        $translations = TranslationString::query()
            ->whereIn('key', $keys)
            ->with('values:id,translation_string_id,language_code,value')
            ->get()
            ->keyBy('key');

        $this->assertSame([], array_values(array_diff($keys, $translations->keys()->all())));

        foreach ($keys as $key) {
            $this->assertEqualsCanonicalizing(['en', 'lt', 'pl', 'ru'], $translations[$key]->values->pluck('language_code')->all(), $key);

            foreach (['ru', 'en', 'lt', 'pl'] as $locale) {
                $value = tkey($key, [], $locale);

                $this->assertNotSame($key, $value, $key.' '.$locale);
                $this->assertNotSame('', $value, $key.' '.$locale);
            }
        }
    }

    public function test_notification_permission_labels_are_registered_and_translated(): void
    {
        $registered = collect((new PlatformProvider(app()))->permissions())
            ->flatMap(fn (object $group): array => $group->items)
            ->keyBy('slug');

        foreach ($this->notificationPermissions() as $permission) {
            $this->assertContains($permission, SuperadminPermissions::all());
            $this->assertTrue($registered->has($permission), $permission);
            $this->assertSame(tkey('permissions.'.$permission), $registered[$permission]['description']);
            $this->assertNotSame('permissions.'.$permission, $registered[$permission]['description']);
        }
    }

    /**
     * @return array<int, string>
     */
    private function notificationPermissions(): array
    {
        return [
            'notifications.messages.view',
            'notifications.messages.create',
            'notifications.messages.send',
            'notifications.messages.cancel',
            'notifications.messages.retry',
            'notifications.templates.view',
            'notifications.templates.create',
            'notifications.templates.update',
            'notifications.templates.publish',
            'notifications.reminders.view',
            'notifications.reminders.manage',
            'notifications.reminders.process',
            'notifications.deliveries.view',
            'notifications.deliveries.manage',
            'notifications.threads.view',
            'notifications.threads.manage',
            'notifications.preferences.manage',
            'notifications.channels.manage',
            'notifications.export',
        ];
    }

    /**
     * @param  array<int, string>  $suffixes
     * @return array<int, string>
     */
    private function prefixed(string $prefix, array $suffixes): array
    {
        return array_map(fn (string $suffix): string => $prefix.$suffix, $suffixes);
    }
}
