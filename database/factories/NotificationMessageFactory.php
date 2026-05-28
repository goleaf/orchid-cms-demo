<?php

namespace Database\Factories;

use App\Models\NotificationChannel;
use App\Models\NotificationMessage;
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
}
