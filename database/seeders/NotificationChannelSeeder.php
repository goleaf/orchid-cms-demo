<?php

namespace Database\Seeders;

use App\Models\NotificationChannel;
use Database\Seeders\Concerns\SeedsFactoryBackedDictionaries;
use Illuminate\Database\Seeder;

class NotificationChannelSeeder extends Seeder
{
    use SeedsFactoryBackedDictionaries;

    public function run(): void
    {
        $this->seedFactoryBackedDictionary(NotificationChannel::class, 'code', $this->records());
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function records(): array
    {
        return [
            [
                'code' => NotificationChannel::CODE_INTERNAL,
                'state' => 'internal',
                'sort_order' => 10,
                'attributes' => [
                    'name_translations' => $this->translations('Внутренние уведомления', 'Internal notifications', 'Vidiniai pranesimai', 'Powiadomienia wewnetrzne'),
                    'description_translations' => $this->translations('Уведомления внутри панели школы.', 'Notifications inside the school panel.', 'Pranesimai mokyklos valdymo skydelyje.', 'Powiadomienia w panelu szkoly.'),
                    'driver' => 'database',
                    'provider' => 'orchid',
                    'is_system' => true,
                    'is_active' => true,
                    'supports_templates' => true,
                    'supports_scheduling' => true,
                ],
            ],
            [
                'code' => NotificationChannel::CODE_EMAIL,
                'state' => 'email',
                'sort_order' => 20,
                'attributes' => [
                    'name_translations' => $this->translations('Эл. почта', 'Email', 'El. pastas', 'E-mail'),
                    'description_translations' => $this->translations('Письма через текущую почтовую настройку.', 'Email through the current mail configuration.', 'El. laiskai per dabartine pasto konfiguracija.', 'E-mail przez aktualna konfiguracje poczty.'),
                    'is_system' => true,
                    'is_active' => true,
                    'supports_templates' => true,
                    'supports_scheduling' => true,
                ],
            ],
            [
                'code' => 'sms_placeholder',
                'state' => 'smsPlaceholder',
                'sort_order' => 30,
                'attributes' => [
                    'name_translations' => $this->translations('SMS заготовка', 'SMS placeholder', 'SMS paruosinys', 'Szablon SMS'),
                    'description_translations' => $this->translations('Заготовка для будущего SMS-провайдера без отправки сообщений.', 'Placeholder for a future SMS provider without sending messages.', 'Busimo SMS tiekejo paruosinys be zinuciu siuntimo.', 'Miejsce na przyszlego dostawce SMS bez wysylania wiadomosci.'),
                    'is_system' => true,
                    'is_active' => true,
                ],
            ],
            [
                'code' => 'whatsapp_placeholder',
                'state' => 'whatsappPlaceholder',
                'sort_order' => 40,
                'attributes' => [
                    'name_translations' => $this->translations('WhatsApp заготовка', 'WhatsApp placeholder', 'WhatsApp paruosinys', 'Szablon WhatsApp'),
                    'description_translations' => $this->translations('Заготовка для будущей WhatsApp-интеграции.', 'Placeholder for future WhatsApp integration.', 'Busimos WhatsApp integracijos paruosinys.', 'Miejsce na przyszla integracje WhatsApp.'),
                    'is_system' => true,
                    'is_active' => true,
                ],
            ],
            [
                'code' => 'telegram_placeholder',
                'state' => 'telegramPlaceholder',
                'sort_order' => 50,
                'attributes' => [
                    'name_translations' => $this->translations('Telegram заготовка', 'Telegram placeholder', 'Telegram paruosinys', 'Szablon Telegram'),
                    'description_translations' => $this->translations('Заготовка для будущего Telegram-бота.', 'Placeholder for a future Telegram bot.', 'Busimo Telegram roboto paruosinys.', 'Miejsce na przyszlego bota Telegram.'),
                    'is_system' => true,
                    'is_active' => true,
                ],
            ],
            [
                'code' => 'push_placeholder',
                'state' => 'pushPlaceholder',
                'sort_order' => 60,
                'attributes' => [
                    'name_translations' => $this->translations('Push заготовка', 'Push placeholder', 'Push paruosinys', 'Szablon push'),
                    'description_translations' => $this->translations('Заготовка для будущих push-уведомлений.', 'Placeholder for future push notifications.', 'Busimu push pranesimu paruosinys.', 'Miejsce na przyszle powiadomienia push.'),
                    'is_system' => true,
                    'is_active' => true,
                ],
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    private function translations(string $ru, string $en, string $lt, string $pl): array
    {
        return compact('ru', 'en', 'lt', 'pl');
    }
}
