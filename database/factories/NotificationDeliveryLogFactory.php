<?php

namespace Database\Factories;

use App\Models\NotificationChannel;
use App\Models\NotificationDeliveryLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NotificationDeliveryLog>
 */
class NotificationDeliveryLogFactory extends Factory
{
    protected $model = NotificationDeliveryLog::class;

    public function definition(): array
    {
        return [
            'uuid' => $this->faker->uuid(),
            'user_id' => null,
            'student_profile_id' => null,
            'marketing_lead_id' => null,
            'student_communication_id' => null,
            'notification_channel_id' => NotificationChannel::factory(),
            'communication_template_id' => null,
            'communication_reminder_id' => null,
            'database_notification_id' => null,
            'direction' => 'outbound',
            'status' => NotificationDeliveryLog::STATUS_QUEUED,
            'recipient_name' => $this->faker->name(),
            'recipient_email' => $this->faker->optional()->safeEmail(),
            'recipient_phone' => $this->faker->optional()->phoneNumber(),
            'recipient_external_id' => null,
            'subject' => $this->faker->sentence(4),
            'body' => $this->faker->paragraph(),
            'provider' => null,
            'provider_message_id' => null,
            'provider_status' => null,
            'error_message' => null,
            'queued_at' => now(),
            'scheduled_at' => null,
            'sent_at' => null,
            'failed_at' => null,
            'read_at' => null,
            'created_by_id' => null,
            'metadata' => null,
        ];
    }

    public function sent(): static
    {
        return $this->state(fn (): array => [
            'status' => NotificationDeliveryLog::STATUS_SENT,
            'sent_at' => now(),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (): array => [
            'status' => NotificationDeliveryLog::STATUS_FAILED,
            'failed_at' => now(),
            'error_message' => 'Delivery failed.',
        ]);
    }
}
