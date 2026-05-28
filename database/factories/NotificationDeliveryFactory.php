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
}
