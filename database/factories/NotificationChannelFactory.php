<?php

namespace Database\Factories;

use App\Models\NotificationChannel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NotificationChannel>
 */
class NotificationChannelFactory extends Factory
{
    protected $model = NotificationChannel::class;

    public function definition(): array
    {
        $code = $this->faker->randomElement(NotificationChannel::codeValues());

        return [
            'code' => $code,
            'name_translations' => [
                'ru' => str($code)->replace('_', ' ')->title()->toString(),
                'en' => str($code)->replace('_', ' ')->title()->toString(),
                'lt' => str($code)->replace('_', ' ')->title()->toString(),
                'pl' => str($code)->replace('_', ' ')->title()->toString(),
            ],
            'description_translations' => null,
            'driver' => in_array($code, ['sms', 'whatsapp', 'telegram'], true) ? 'placeholder' : $code,
            'provider' => null,
            'is_system' => false,
            'is_active' => true,
            'supports_internal' => $code === NotificationChannel::CODE_INTERNAL,
            'supports_external' => $code !== NotificationChannel::CODE_INTERNAL,
            'supports_templates' => true,
            'supports_scheduling' => true,
            'supports_delivery_status' => in_array($code, ['email', 'sms', 'whatsapp', 'telegram'], true),
            'sort_order' => $this->faker->numberBetween(1, 100),
            'settings' => null,
            'created_by_id' => null,
            'updated_by_id' => null,
        ];
    }

    public function internal(): static
    {
        return $this->state(fn (): array => [
            'code' => NotificationChannel::CODE_INTERNAL,
            'driver' => 'database',
            'supports_internal' => true,
            'supports_external' => false,
        ]);
    }

    public function placeholder(string $code): static
    {
        return $this->state(fn (): array => [
            'code' => $code,
            'driver' => 'placeholder',
            'provider' => null,
        ]);
    }
}
