<?php

namespace Database\Factories;

use App\Models\CommunicationReminder;
use App\Models\CommunicationTemplate;
use App\Models\NotificationChannel;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CommunicationReminder>
 */
class CommunicationReminderFactory extends Factory
{
    protected $model = CommunicationReminder::class;

    public function definition(): array
    {
        return [
            'uuid' => $this->faker->uuid(),
            'marketing_lead_id' => null,
            'student_profile_id' => null,
            'student_enrollment_id' => null,
            'assigned_to_user_id' => User::factory(),
            'notification_channel_id' => NotificationChannel::factory(),
            'communication_template_id' => CommunicationTemplate::factory(),
            'status' => CommunicationReminder::STATUS_SCHEDULED,
            'priority' => CommunicationReminder::PRIORITY_NORMAL,
            'title_translations' => [
                'ru' => $this->faker->sentence(3),
                'en' => $this->faker->sentence(3),
                'lt' => $this->faker->sentence(3),
                'pl' => $this->faker->sentence(3),
            ],
            'body_translations' => null,
            'note' => $this->faker->optional()->sentence(),
            'due_at' => now()->addDay(),
            'completed_at' => null,
            'cancelled_at' => null,
            'last_attempted_at' => null,
            'created_by_id' => null,
            'updated_by_id' => null,
            'completed_by_id' => null,
            'metadata' => null,
        ];
    }

    public function due(): static
    {
        return $this->state(fn (): array => ['due_at' => now()->subMinute()]);
    }

    public function completed(): static
    {
        return $this->state(fn (): array => [
            'status' => CommunicationReminder::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);
    }
}
