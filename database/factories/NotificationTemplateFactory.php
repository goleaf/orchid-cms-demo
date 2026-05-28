<?php

namespace Database\Factories;

use App\Models\NotificationChannel;
use App\Models\NotificationTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NotificationTemplate>
 */
class NotificationTemplateFactory extends Factory
{
    protected $model = NotificationTemplate::class;

    public function definition(): array
    {
        $code = 'template_'.$this->faker->unique()->bothify('####');
        $name = str($code)->replace('_', ' ')->title()->toString();

        return [
            'code' => $code,
            'channel_id' => null,
            'name_translations' => $this->translations($name),
            'description_translations' => $this->translations($this->faker->sentence(6)),
            'template_group' => $this->faker->randomElement(['general', 'student', 'lead', 'reminder', 'internal']),
            'is_active' => true,
            'is_system' => false,
        ];
    }

    public function forChannel(NotificationChannel $channel): static
    {
        return $this->state(fn (): array => [
            'channel_id' => $channel->id,
        ]);
    }

    /**
     * @return array<string, string>
     */
    private function translations(string $value): array
    {
        return [
            'ru' => $value,
            'en' => $value,
            'lt' => $value,
            'pl' => $value,
        ];
    }
}
