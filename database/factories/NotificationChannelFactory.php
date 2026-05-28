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
            'is_internal' => $code === NotificationChannel::CODE_INTERNAL,
            'is_email' => $code === NotificationChannel::CODE_EMAIL,
            'is_sms_placeholder' => $code === NotificationChannel::CODE_SMS,
            'is_whatsapp_placeholder' => $code === NotificationChannel::CODE_WHATSAPP,
            'is_telegram_placeholder' => $code === NotificationChannel::CODE_TELEGRAM,
            'is_push_placeholder' => false,
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
            'name_translations' => $this->translations('Internal notifications'),
            'description_translations' => $this->translations('Notifications inside the school admin panel.'),
            'driver' => 'database',
            'provider' => 'orchid',
            'is_internal' => true,
            'is_email' => false,
            'is_sms_placeholder' => false,
            'is_whatsapp_placeholder' => false,
            'is_telegram_placeholder' => false,
            'is_push_placeholder' => false,
            'supports_internal' => true,
            'supports_external' => false,
            'supports_delivery_status' => true,
        ]);
    }

    public function email(): static
    {
        return $this->state(fn (): array => [
            'code' => NotificationChannel::CODE_EMAIL,
            'name_translations' => $this->translations('Email'),
            'description_translations' => $this->translations('Email through the configured Laravel mailer.'),
            'driver' => 'mail',
            'provider' => 'laravel',
            'is_internal' => false,
            'is_email' => true,
            'is_sms_placeholder' => false,
            'is_whatsapp_placeholder' => false,
            'is_telegram_placeholder' => false,
            'is_push_placeholder' => false,
            'supports_internal' => false,
            'supports_external' => true,
            'supports_delivery_status' => true,
        ]);
    }

    public function smsPlaceholder(): static
    {
        return $this->placeholder('sms_placeholder');
    }

    public function whatsappPlaceholder(): static
    {
        return $this->placeholder('whatsapp_placeholder');
    }

    public function telegramPlaceholder(): static
    {
        return $this->placeholder('telegram_placeholder');
    }

    public function pushPlaceholder(): static
    {
        return $this->placeholder('push_placeholder');
    }

    public function active(): static
    {
        return $this->state(fn (): array => [
            'is_active' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => [
            'is_active' => false,
        ]);
    }

    public function translated(): static
    {
        return $this->state(fn (): array => [
            'name_translations' => [
                'ru' => 'Переведенный канал',
                'en' => 'Translated channel',
                'lt' => 'Isverstas kanalas',
                'pl' => 'Przetlumaczony kanal',
            ],
            'description_translations' => [
                'ru' => 'Тестовое описание канала.',
                'en' => 'Test channel description.',
                'lt' => 'Bandomasis kanalo aprasymas.',
                'pl' => 'Testowy opis kanalu.',
            ],
        ]);
    }

    public function placeholder(string $code): static
    {
        return $this->state(fn (): array => [
            'code' => $code,
            'name_translations' => $this->translations(str($code)->replace('_', ' ')->title()->toString()),
            'description_translations' => $this->translations('Placeholder for a future external messaging provider.'),
            'driver' => 'placeholder',
            'provider' => null,
            'is_internal' => false,
            'is_email' => false,
            'is_sms_placeholder' => in_array($code, [NotificationChannel::CODE_SMS, 'sms_placeholder'], true),
            'is_whatsapp_placeholder' => in_array($code, [NotificationChannel::CODE_WHATSAPP, 'whatsapp_placeholder'], true),
            'is_telegram_placeholder' => in_array($code, [NotificationChannel::CODE_TELEGRAM, 'telegram_placeholder'], true),
            'is_push_placeholder' => $code === 'push_placeholder',
            'supports_internal' => false,
            'supports_external' => true,
            'supports_delivery_status' => false,
            'settings' => ['placeholder' => true],
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
