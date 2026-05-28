<?php

namespace Database\Factories;

use App\Models\CommunicationTemplate;
use App\Models\NotificationChannel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CommunicationTemplate>
 */
class CommunicationTemplateFactory extends Factory
{
    protected $model = CommunicationTemplate::class;

    public function definition(): array
    {
        return [
            'code' => $this->faker->unique()->slug(3),
            'type' => $this->faker->randomElement(CommunicationTemplate::typeValues()),
            'notification_channel_id' => NotificationChannel::factory(),
            'channel' => null,
            'name_translations' => [
                'ru' => $this->faker->words(3, true),
                'en' => $this->faker->words(3, true),
                'lt' => $this->faker->words(3, true),
                'pl' => $this->faker->words(3, true),
            ],
            'subject_translations' => [
                'ru' => $this->faker->sentence(4),
                'en' => $this->faker->sentence(4),
                'lt' => $this->faker->sentence(4),
                'pl' => $this->faker->sentence(4),
            ],
            'body_translations' => [
                'ru' => $this->faker->paragraph(),
                'en' => $this->faker->paragraph(),
                'lt' => $this->faker->paragraph(),
                'pl' => $this->faker->paragraph(),
            ],
            'variable_keys' => ['student_name', 'lead_name', 'due_date'],
            'is_system' => false,
            'is_active' => true,
            'sort_order' => $this->faker->numberBetween(1, 100),
            'metadata' => null,
            'created_by_id' => null,
            'updated_by_id' => null,
        ];
    }

    public function student(): static
    {
        return $this->state(fn (): array => ['type' => CommunicationTemplate::TYPE_STUDENT]);
    }

    public function lead(): static
    {
        return $this->state(fn (): array => ['type' => CommunicationTemplate::TYPE_LEAD]);
    }
}
