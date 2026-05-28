<?php

namespace Database\Seeders;

use App\Models\CommunicationTemplate;
use App\Models\NotificationChannel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class CommunicationSeeder extends Seeder
{
    public function run(): void
    {
        $channels = collect($this->channels())
            ->mapWithKeys(function (array $state): array {
                $channel = $this->upsert(
                    new NotificationChannel,
                    ['code' => $state['code']],
                    NotificationChannel::factory()->state($state)->make()->attributesToArray(),
                );

                return [$channel->code => $channel];
            });

        foreach ($this->templates($channels->all()) as $state) {
            $this->upsert(
                new CommunicationTemplate,
                ['code' => $state['code']],
                CommunicationTemplate::factory()->state($state)->make()->attributesToArray(),
            );
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function channels(): array
    {
        return [
            [
                'code' => NotificationChannel::CODE_INTERNAL,
                'name_translations' => $this->translations('Внутренние уведомления', 'Internal notifications', 'Vidiniai pranesimai', 'Powiadomienia wewnetrzne'),
                'description_translations' => $this->translations('Уведомления внутри админ-панели.', 'Notifications inside the admin panel.', 'Pranesimai administravimo skydelyje.', 'Powiadomienia w panelu administracyjnym.'),
                'driver' => 'database',
                'provider' => 'orchid',
                'is_system' => true,
                'supports_internal' => true,
                'supports_external' => false,
                'supports_delivery_status' => true,
                'sort_order' => 10,
            ],
            [
                'code' => NotificationChannel::CODE_EMAIL,
                'name_translations' => $this->translations('Эл. почта', 'Email', 'El. pastas', 'E-mail'),
                'description_translations' => $this->translations('Письма через текущую почтовую конфигурацию.', 'Email through the current mail configuration.', 'El. laiskai per dabartine pasto konfiguracija.', 'Email przez aktualna konfiguracje poczty.'),
                'driver' => 'mail',
                'provider' => 'laravel',
                'is_system' => true,
                'supports_internal' => false,
                'supports_external' => true,
                'supports_delivery_status' => true,
                'sort_order' => 20,
            ],
            [
                'code' => NotificationChannel::CODE_PHONE,
                'name_translations' => $this->translations('Телефон', 'Phone', 'Telefonas', 'Telefon'),
                'description_translations' => $this->translations('Ручные звонки менеджеров.', 'Manual manager calls.', 'Rankiniai vadybininku skambuciai.', 'Reczne rozmowy menedzerow.'),
                'driver' => 'manual',
                'provider' => null,
                'is_system' => true,
                'supports_internal' => false,
                'supports_external' => true,
                'supports_delivery_status' => false,
                'sort_order' => 30,
            ],
            [
                'code' => NotificationChannel::CODE_SMS,
                'name_translations' => $this->translations('SMS', 'SMS', 'SMS', 'SMS'),
                'description_translations' => $this->translations('Заготовка для будущего SMS-провайдера.', 'Placeholder for a future SMS provider.', 'Busimo SMS tiekejo paruosinys.', 'Miejsce na przyszlego dostawce SMS.'),
                'driver' => 'placeholder',
                'provider' => null,
                'is_system' => true,
                'supports_internal' => false,
                'supports_external' => true,
                'supports_delivery_status' => false,
                'sort_order' => 40,
            ],
            [
                'code' => NotificationChannel::CODE_WHATSAPP,
                'name_translations' => $this->translations('WhatsApp', 'WhatsApp', 'WhatsApp', 'WhatsApp'),
                'description_translations' => $this->translations('Заготовка для будущей WhatsApp-интеграции.', 'Placeholder for future WhatsApp integration.', 'Busimos WhatsApp integracijos paruosinys.', 'Miejsce na przyszla integracje WhatsApp.'),
                'driver' => 'placeholder',
                'provider' => null,
                'is_system' => true,
                'supports_internal' => false,
                'supports_external' => true,
                'supports_delivery_status' => false,
                'sort_order' => 50,
            ],
            [
                'code' => NotificationChannel::CODE_TELEGRAM,
                'name_translations' => $this->translations('Telegram', 'Telegram', 'Telegram', 'Telegram'),
                'description_translations' => $this->translations('Заготовка для будущего Telegram-бота.', 'Placeholder for a future Telegram bot.', 'Busimo Telegram roboto paruosinys.', 'Miejsce na przyszlego bota Telegram.'),
                'driver' => 'placeholder',
                'provider' => null,
                'is_system' => true,
                'supports_internal' => false,
                'supports_external' => true,
                'supports_delivery_status' => false,
                'sort_order' => 60,
            ],
        ];
    }

    /**
     * @param  array<string, NotificationChannel>  $channels
     * @return array<int, array<string, mixed>>
     */
    private function templates(array $channels): array
    {
        return [
            [
                'code' => 'internal-reminder',
                'type' => CommunicationTemplate::TYPE_INTERNAL,
                'notification_channel_id' => $channels[NotificationChannel::CODE_INTERNAL]?->id ?? null,
                'channel' => NotificationChannel::CODE_INTERNAL,
                'name_translations' => $this->translations('Внутреннее напоминание', 'Internal reminder', 'Vidinis priminimas', 'Wewnetrzne przypomnienie'),
                'subject_translations' => $this->translations('Напоминание: :title', 'Reminder: :title', 'Priminimas: :title', 'Przypomnienie: :title'),
                'body_translations' => $this->translations('Проверьте задачу до :due_date.', 'Check the task by :due_date.', 'Patikrinkite uzduoti iki :due_date.', 'Sprawdz zadanie do :due_date.'),
                'variable_keys' => ['title', 'due_date'],
                'is_system' => true,
                'sort_order' => 10,
            ],
            [
                'code' => 'student-document-reminder',
                'type' => CommunicationTemplate::TYPE_STUDENT,
                'notification_channel_id' => $channels[NotificationChannel::CODE_EMAIL]?->id ?? null,
                'channel' => NotificationChannel::CODE_EMAIL,
                'name_translations' => $this->translations('Напоминание о документах ученику', 'Student document reminder', 'Mokinio dokumentu priminimas', 'Przypomnienie o dokumentach ucznia'),
                'subject_translations' => $this->translations('Нужны документы для обучения', 'Documents needed for training', 'Reikalingi dokumentai mokymui', 'Dokumenty potrzebne do kursu'),
                'body_translations' => $this->translations('Здравствуйте, :student_name. Пожалуйста, подготовьте недостающие документы.', 'Hello :student_name. Please prepare the missing documents.', 'Sveiki, :student_name. Prasome parengti trukstamus dokumentus.', 'Dzien dobry, :student_name. Przygotuj brakujace dokumenty.'),
                'variable_keys' => ['student_name'],
                'is_system' => true,
                'sort_order' => 20,
            ],
            [
                'code' => 'lead-follow-up',
                'type' => CommunicationTemplate::TYPE_LEAD,
                'notification_channel_id' => $channels[NotificationChannel::CODE_PHONE]?->id ?? null,
                'channel' => NotificationChannel::CODE_PHONE,
                'name_translations' => $this->translations('Повторный контакт с лидом', 'Lead follow-up', 'Pakartotinis kontaktas su uzklausa', 'Ponowny kontakt z leadem'),
                'subject_translations' => $this->translations('Связаться с :lead_name', 'Contact :lead_name', 'Susisiekti su :lead_name', 'Skontaktuj sie z :lead_name'),
                'body_translations' => $this->translations('Уточнить интерес к обучению и удобное расписание.', 'Confirm training interest and preferred schedule.', 'Patikslinti susidomejima mokymu ir patogu grafika.', 'Potwierdzic zainteresowanie kursem i dogodny termin.'),
                'variable_keys' => ['lead_name'],
                'is_system' => true,
                'sort_order' => 30,
            ],
            [
                'code' => 'lesson-reminder-placeholder',
                'type' => CommunicationTemplate::TYPE_REMINDER,
                'notification_channel_id' => $channels[NotificationChannel::CODE_SMS]?->id ?? null,
                'channel' => NotificationChannel::CODE_SMS,
                'name_translations' => $this->translations('Заготовка SMS о занятии', 'Lesson SMS placeholder', 'Pamokos SMS paruosinys', 'Szablon SMS o lekcji'),
                'subject_translations' => null,
                'body_translations' => $this->translations('Напоминание: занятие :lesson_at.', 'Reminder: lesson at :lesson_at.', 'Priminimas: pamoka :lesson_at.', 'Przypomnienie: lekcja :lesson_at.'),
                'variable_keys' => ['lesson_at'],
                'is_system' => true,
                'sort_order' => 40,
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @param  array<string, mixed>  $values
     */
    private function upsert(Model $model, array $attributes, array $values): Model
    {
        /** @var class-string<Model> $class */
        $class = $model::class;

        return $class::query()->updateOrCreate($attributes, $values);
    }

    /**
     * @return array<string, string>
     */
    private function translations(string $ru, string $en, string $lt, string $pl): array
    {
        return compact('ru', 'en', 'lt', 'pl');
    }
}
