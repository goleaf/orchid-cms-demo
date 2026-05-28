<?php

namespace Database\Factories;

use App\Models\NotificationChannel;
use App\Models\NotificationPreference;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NotificationPreference>
 */
class NotificationPreferenceFactory extends Factory
{
    protected $model = NotificationPreference::class;

    public function definition(): array
    {
        return [
            'user_id' => null,
            'student_id' => null,
            'lead_id' => null,
            'channel_id' => NotificationChannel::factory()->state([
                'code' => 'preference_channel_'.$this->faker->unique()->numerify('####'),
            ]),
            'enabled' => true,
            'locale' => 'en',
            'settings' => null,
        ];
    }
}
