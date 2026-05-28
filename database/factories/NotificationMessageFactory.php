<?php

namespace Database\Factories;

use App\Models\NotificationChannel;
use App\Models\NotificationMessage;
use App\Models\NotificationTemplate;
use App\Models\NotificationTemplateVersion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NotificationMessage>
 */
class NotificationMessageFactory extends Factory
{
    protected $model = NotificationMessage::class;

    public function definition(): array
    {
        return [
            'message_number' => 'MSG-FOUNDATION-'.$this->faker->unique()->numerify('####'),
            'channel_id' => NotificationChannel::factory()->state([
                'code' => 'test_channel_'.$this->faker->unique()->numerify('####'),
            ]),
            'template_id' => null,
            'template_version_id' => null,
            'subject' => $this->faker->sentence(4),
            'body' => $this->faker->paragraph(),
            'priority' => NotificationMessage::PRIORITY_NORMAL,
            'status' => NotificationMessage::STATUS_DRAFT,
            'scheduled_at' => null,
            'sent_at' => null,
            'failed_at' => null,
            'created_by_id' => null,
            'metadata' => null,
        ];
    }

    public function scheduled(): static
    {
        return $this->state(fn (): array => [
            'status' => NotificationMessage::STATUS_SCHEDULED,
            'scheduled_at' => now()->addHour(),
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn (): array => [
            'status' => NotificationMessage::STATUS_DRAFT,
            'scheduled_at' => null,
            'sent_at' => null,
            'failed_at' => null,
        ]);
    }

    public function queued(): static
    {
        return $this->state(fn (): array => [
            'status' => NotificationMessage::STATUS_QUEUED,
            'scheduled_at' => null,
            'sent_at' => null,
            'failed_at' => null,
        ]);
    }

    public function sent(): static
    {
        return $this->state(fn (): array => [
            'status' => NotificationMessage::STATUS_SENT,
            'sent_at' => now(),
            'failed_at' => null,
        ]);
    }

    public function delivered(): static
    {
        return $this->state(fn (): array => [
            'status' => NotificationMessage::STATUS_DELIVERED,
            'sent_at' => now()->subMinutes(5),
            'failed_at' => null,
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (): array => [
            'status' => NotificationMessage::STATUS_FAILED,
            'failed_at' => now(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (): array => [
            'status' => NotificationMessage::STATUS_CANCELLED,
            'metadata' => ['cancelled_by_factory' => true],
        ]);
    }

    public function urgent(): static
    {
        return $this->state(fn (): array => [
            'priority' => NotificationMessage::PRIORITY_URGENT,
        ]);
    }

    public function high(): static
    {
        return $this->state(fn (): array => [
            'priority' => NotificationMessage::PRIORITY_HIGH,
        ]);
    }

    public function normal(): static
    {
        return $this->state(fn (): array => [
            'priority' => NotificationMessage::PRIORITY_NORMAL,
        ]);
    }

    public function fromTemplate(
        NotificationTemplate|NotificationTemplateVersion|null $template = null,
    ): static {
        return $this->state(function () use ($template): array {
            $version = $template instanceof NotificationTemplateVersion
                ? $template
                : null;

            $notificationTemplate = $template instanceof NotificationTemplate
                ? $template
                : $version?->template;

            if (! $notificationTemplate instanceof NotificationTemplate) {
                $version = NotificationTemplateVersion::factory()->published()->create();
                $notificationTemplate = $version->template;
            }

            if (! $version instanceof NotificationTemplateVersion) {
                $version = $notificationTemplate->versions()->published()->first()
                    ?? NotificationTemplateVersion::factory()->published()->create([
                        'template_id' => $notificationTemplate->id,
                    ]);
            }

            return [
                'channel_id' => $notificationTemplate->channel_id
                    ?? NotificationChannel::factory()->email(),
                'template_id' => $notificationTemplate->id,
                'template_version_id' => $version->id,
                'subject' => $version->subject('en'),
                'body' => $version->body('en') ?? 'Template message body.',
            ];
        });
    }
}
