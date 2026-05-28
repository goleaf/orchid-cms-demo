<?php

namespace Database\Factories;

use App\Models\CommunicationMessage;
use App\Models\CommunicationThread;
use App\Models\NotificationChannel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CommunicationMessage>
 */
class CommunicationMessageFactory extends Factory
{
    protected $model = CommunicationMessage::class;

    public function definition(): array
    {
        return [
            'thread_id' => CommunicationThread::factory(),
            'direction' => CommunicationMessage::DIRECTION_OUTBOUND,
            'channel_id' => NotificationChannel::factory()->state([
                'code' => 'communication_channel_'.$this->faker->unique()->numerify('####'),
            ]),
            'body' => $this->faker->paragraph(),
            'user_id' => null,
            'student_id' => null,
            'lead_id' => null,
            'sent_at' => now(),
            'metadata' => null,
        ];
    }
}
