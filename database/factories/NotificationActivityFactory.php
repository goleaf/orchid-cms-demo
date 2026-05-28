<?php

namespace Database\Factories;

use App\Models\NotificationActivity;
use App\Models\NotificationMessage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NotificationActivity>
 */
class NotificationActivityFactory extends Factory
{
    protected $model = NotificationActivity::class;

    public function definition(): array
    {
        return [
            'message_id' => NotificationMessage::factory(),
            'recipient_id' => null,
            'delivery_id' => null,
            'user_id' => null,
            'student_id' => null,
            'lead_id' => null,
            'activity_type' => NotificationActivity::TYPE_CREATED,
            'description' => $this->faker->sentence(),
            'occurred_at' => now(),
            'metadata' => null,
        ];
    }
}
