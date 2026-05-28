<?php

namespace Database\Factories;

use App\Models\CommunicationTemplate;
use App\Models\NotificationChannel;
use App\Models\Student;
use App\Models\StudentCommunication;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentCommunication>
 */
class StudentCommunicationFactory extends Factory
{
    protected $model = StudentCommunication::class;

    public function definition(): array
    {
        $channel = $this->faker->randomElement(NotificationChannel::codeValues());

        return [
            'student_profile_id' => Student::factory(),
            'student_enrollment_id' => null,
            'marketing_lead_id' => null,
            'user_id' => User::factory(),
            'notification_channel_id' => null,
            'communication_template_id' => CommunicationTemplate::factory(),
            'communication_reminder_id' => null,
            'channel' => $channel,
            'direction' => $this->faker->randomElement(['inbound', 'outbound']),
            'subject' => $this->faker->optional()->sentence(4),
            'body' => $this->faker->paragraph(),
            'communicated_at' => now()->subHour(),
            'client_replied_at' => null,
            'callback_required_at' => null,
            'metadata' => null,
        ];
    }
}
