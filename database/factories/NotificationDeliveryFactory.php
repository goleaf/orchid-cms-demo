<?php

namespace Database\Factories;

use App\Models\NotificationChannel;
use App\Models\NotificationDelivery;
use App\Models\NotificationMessage;
use App\Models\NotificationRecipient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NotificationDelivery>
 */
class NotificationDeliveryFactory extends Factory
{
    protected $model = NotificationDelivery::class;

    public function definition(): array
    {
        return [
            'message_id' => NotificationMessage::factory(),
            'recipient_id' => NotificationRecipient::factory(),
            'channel_id' => NotificationChannel::factory()->state([
                'code' => 'delivery_channel_'.$this->faker->unique()->numerify('####'),
            ]),
            'status' => NotificationDelivery::STATUS_PENDING,
            'provider' => 'placeholder',
            'provider_message_id' => null,
            'attempt_no' => 1,
            'sent_at' => null,
            'delivered_at' => null,
            'failed_at' => null,
            'error_message' => null,
            'metadata' => null,
        ];
    }

    public function queued(): static
    {
        return $this->state(fn (): array => [
            'status' => NotificationDelivery::STATUS_QUEUED,
            'sent_at' => null,
            'delivered_at' => null,
            'failed_at' => null,
            'error_message' => null,
        ]);
    }

    public function sent(): static
    {
        return $this->state(fn (): array => [
            'status' => NotificationDelivery::STATUS_SENT,
            'sent_at' => now(),
            'delivered_at' => null,
            'failed_at' => null,
            'error_message' => null,
        ]);
    }

    public function delivered(): static
    {
        return $this->state(fn (): array => [
            'status' => NotificationDelivery::STATUS_DELIVERED,
            'sent_at' => now()->subMinutes(10),
            'delivered_at' => now(),
            'failed_at' => null,
            'error_message' => null,
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (): array => [
            'status' => NotificationDelivery::STATUS_FAILED,
            'failed_at' => now(),
            'error_message' => 'Factory delivery failure.',
        ]);
    }

    public function retryable(): static
    {
        return $this->failed()->state(fn (): array => [
            'attempt_no' => 1,
            'metadata' => ['retryable' => true],
        ]);
    }

    public function placeholder(): static
    {
        return $this->queued()->state(fn (): array => [
            'provider' => 'placeholder',
            'provider_message_id' => null,
            'metadata' => ['placeholder' => true],
        ]);
    }
}
