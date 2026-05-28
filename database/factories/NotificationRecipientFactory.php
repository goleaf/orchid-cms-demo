<?php

namespace Database\Factories;

use App\Models\NotificationMessage;
use App\Models\NotificationRecipient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NotificationRecipient>
 */
class NotificationRecipientFactory extends Factory
{
    protected $model = NotificationRecipient::class;

    public function definition(): array
    {
        return [
            'message_id' => NotificationMessage::factory(),
            'user_id' => null,
            'student_id' => null,
            'lead_id' => null,
            'email' => $this->faker->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'locale' => 'en',
            'status' => NotificationRecipient::STATUS_PENDING,
            'metadata' => null,
        ];
    }
}
