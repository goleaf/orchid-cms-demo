<?php

namespace Database\Seeders;

use App\Enums\LeadStatus as LeadStatusEnum;
use App\Models\LeadLostReason;
use App\Models\LeadSource;
use App\Models\LeadStatus;
use App\Models\LeadTag;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class CrmDictionarySeeder extends Seeder
{
    public function run(): void
    {
        $this->seedDictionary(LeadStatus::class, 'code', $this->statuses());
        $this->seedDictionary(LeadSource::class, 'code', $this->sources());
        $this->seedDictionary(LeadLostReason::class, 'code', $this->lostReasons());
        $this->seedDictionary(LeadTag::class, 'slug', $this->tags());
    }

    /**
     * @param  class-string<Model>  $modelClass
     * @param  array<int, array<string, mixed>>  $records
     */
    private function seedDictionary(string $modelClass, string $keyColumn, array $records): void
    {
        foreach ($records as $sortOrder => $record) {
            $attributes = [
                'name' => $record['values']['ru'] ?? $record['values']['en'],
                'name_translations' => $record['values'],
                'color' => $record['color'] ?? null,
                'is_system' => true,
                'is_active' => true,
                'sort_order' => ($sortOrder + 1) * 10,
            ];

            if ($modelClass === LeadStatus::class) {
                $attributes = [
                    ...$attributes,
                    'is_default' => $record['is_default'] ?? false,
                    'is_final' => $record['is_final'] ?? false,
                    'is_success' => $record['is_success'] ?? false,
                    'is_lost' => $record['is_lost'] ?? false,
                ];
            }

            $modelClass::query()->updateOrCreate(
                [$keyColumn => $record[$keyColumn]],
                $attributes,
            );
        }
    }

    /**
     * @return array<int, array{code: string, values: array<string, string>}>
     */
    private function statuses(): array
    {
        return collect(LeadStatusEnum::cases())
            ->map(fn (LeadStatusEnum $status): array => [
                'code' => $status->value,
                'values' => $this->statusValues($status),
                'color' => $this->statusColor($status),
                'is_default' => $status === LeadStatusEnum::New,
                'is_final' => $status->isFinal(),
                'is_success' => $status->isSuccess(),
                'is_lost' => $status->isLost(),
            ])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    private function statusValues(LeadStatusEnum $status): array
    {
        return match ($status) {
            LeadStatusEnum::New => ['ru' => 'Новая заявка', 'en' => 'New lead', 'lt' => 'Nauja uzklausa', 'pl' => 'Nowy lead'],
            LeadStatusEnum::NoAnswer => ['ru' => 'Не дозвонились', 'en' => 'No answer', 'lt' => 'Neatsiliepe', 'pl' => 'Brak odpowiedzi'],
            LeadStatusEnum::Contacted => ['ru' => 'Связались', 'en' => 'Contacted', 'lt' => 'Susisiekta', 'pl' => 'Skontaktowano'],
            LeadStatusEnum::Consultation => ['ru' => 'Консультация проведена', 'en' => 'Consultation done', 'lt' => 'Konsultacija atlikta', 'pl' => 'Konsultacja wykonana'],
            LeadStatusEnum::ConsultationDone => ['ru' => 'Консультация проведена', 'en' => 'Consultation done', 'lt' => 'Konsultacija atlikta', 'pl' => 'Konsultacja wykonana'],
            LeadStatusEnum::WaitingDocuments => ['ru' => 'Ждёт документы', 'en' => 'Waiting for documents', 'lt' => 'Laukiama dokumentu', 'pl' => 'Oczekuje na dokumenty'],
            LeadStatusEnum::WaitingPayment => ['ru' => 'Ждёт оплату', 'en' => 'Waiting for payment', 'lt' => 'Laukiama apmokejimo', 'pl' => 'Oczekuje na platnosc'],
            LeadStatusEnum::ReadyToEnroll => ['ru' => 'Готов к записи', 'en' => 'Ready to enroll', 'lt' => 'Pasiruoses registracijai', 'pl' => 'Gotowy do zapisu'],
            LeadStatusEnum::Enrolled => ['ru' => 'Записан в обучение', 'en' => 'Enrolled', 'lt' => 'Uzregistruotas mokymui', 'pl' => 'Zapisany na kurs'],
            LeadStatusEnum::AssignedToGroup => ['ru' => 'Записан в группу', 'en' => 'Assigned to group', 'lt' => 'Priskirta grupei', 'pl' => 'Przypisany do grupy'],
            LeadStatusEnum::BecameStudent => ['ru' => 'Стал учеником', 'en' => 'Became student', 'lt' => 'Tapo mokiniu', 'pl' => 'Zostal uczniem'],
            LeadStatusEnum::Lost => ['ru' => 'Отказ', 'en' => 'Lost', 'lt' => 'Prarasta', 'pl' => 'Utracony'],
            LeadStatusEnum::Rejected => ['ru' => 'Отказ', 'en' => 'Rejected', 'lt' => 'Atmesta', 'pl' => 'Odrzucony'],
            LeadStatusEnum::Duplicate => ['ru' => 'Дубль', 'en' => 'Duplicate', 'lt' => 'Dublikatas', 'pl' => 'Duplikat'],
            LeadStatusEnum::Spam => ['ru' => 'Спам', 'en' => 'Spam', 'lt' => 'Slamstas', 'pl' => 'Spam'],
            LeadStatusEnum::Archived => ['ru' => 'Архив', 'en' => 'Archived', 'lt' => 'Archyvas', 'pl' => 'Archiwum'],
        };
    }

    private function statusColor(LeadStatusEnum $status): string
    {
        return match ($status) {
            LeadStatusEnum::New => '#2563eb',
            LeadStatusEnum::NoAnswer => '#f97316',
            LeadStatusEnum::Contacted => '#0891b2',
            LeadStatusEnum::Consultation, LeadStatusEnum::ConsultationDone => '#7c3aed',
            LeadStatusEnum::WaitingDocuments => '#ca8a04',
            LeadStatusEnum::WaitingPayment => '#ea580c',
            LeadStatusEnum::ReadyToEnroll, LeadStatusEnum::AssignedToGroup => '#16a34a',
            LeadStatusEnum::Enrolled, LeadStatusEnum::BecameStudent => '#15803d',
            LeadStatusEnum::Lost, LeadStatusEnum::Rejected => '#dc2626',
            LeadStatusEnum::Duplicate => '#64748b',
            LeadStatusEnum::Spam => '#991b1b',
            LeadStatusEnum::Archived => '#475569',
        };
    }

    /**
     * @return array<int, array{code: string, values: array<string, string>}>
     */
    private function sources(): array
    {
        return [
            ['code' => 'website', 'values' => ['ru' => 'Сайт', 'en' => 'Website', 'lt' => 'Svetaine', 'pl' => 'Strona']],
            ['code' => 'callback', 'values' => ['ru' => 'Обратный звонок', 'en' => 'Callback', 'lt' => 'Perskambinimas', 'pl' => 'Oddzwonienie']],
            ['code' => 'phone', 'values' => ['ru' => 'Телефон', 'en' => 'Phone', 'lt' => 'Telefonas', 'pl' => 'Telefon']],
            ['code' => 'office', 'values' => ['ru' => 'Пришёл в офис', 'en' => 'Office visit', 'lt' => 'Atvyko i biura', 'pl' => 'Wizyta w biurze']],
            ['code' => 'telegram', 'values' => ['ru' => 'Telegram', 'en' => 'Telegram', 'lt' => 'Telegram', 'pl' => 'Telegram']],
            ['code' => 'whatsapp', 'values' => ['ru' => 'WhatsApp', 'en' => 'WhatsApp', 'lt' => 'WhatsApp', 'pl' => 'WhatsApp']],
            ['code' => 'facebook', 'values' => ['ru' => 'Facebook', 'en' => 'Facebook', 'lt' => 'Facebook', 'pl' => 'Facebook']],
            ['code' => 'instagram', 'values' => ['ru' => 'Instagram', 'en' => 'Instagram', 'lt' => 'Instagram', 'pl' => 'Instagram']],
            ['code' => 'tiktok', 'values' => ['ru' => 'TikTok', 'en' => 'TikTok', 'lt' => 'TikTok', 'pl' => 'TikTok']],
            ['code' => 'google_ads', 'values' => ['ru' => 'Google Ads', 'en' => 'Google Ads', 'lt' => 'Google Ads', 'pl' => 'Google Ads']],
            ['code' => 'referral', 'values' => ['ru' => 'Рекомендация', 'en' => 'Referral', 'lt' => 'Rekomendacija', 'pl' => 'Polecenie']],
            ['code' => 'partner', 'values' => ['ru' => 'Партнёр', 'en' => 'Partner', 'lt' => 'Partneris', 'pl' => 'Partner']],
            ['code' => 'other', 'values' => ['ru' => 'Другое', 'en' => 'Other', 'lt' => 'Kita', 'pl' => 'Inne']],
        ];
    }

    /**
     * @return array<int, array{code: string, values: array<string, string>}>
     */
    private function lostReasons(): array
    {
        return [
            ['code' => 'price', 'values' => ['ru' => 'Не подошла цена', 'en' => 'Price did not fit', 'lt' => 'Netiko kaina', 'pl' => 'Cena nie pasowala']],
            ['code' => 'schedule', 'values' => ['ru' => 'Не подошло расписание', 'en' => 'Schedule did not fit', 'lt' => 'Netiko tvarkarastis', 'pl' => 'Termin nie pasowal']],
            ['code' => 'location', 'values' => ['ru' => 'Не подошёл филиал', 'en' => 'Location did not fit', 'lt' => 'Netiko filialas', 'pl' => 'Lokalizacja nie pasowala']],
            ['code' => 'competitor', 'values' => ['ru' => 'Выбрал конкурента', 'en' => 'Chose competitor', 'lt' => 'Pasirinko konkurenta', 'pl' => 'Wybral konkurencje']],
            ['code' => 'no_response', 'values' => ['ru' => 'Не отвечает', 'en' => 'No response', 'lt' => 'Neatsako', 'pl' => 'Brak odpowiedzi']],
            ['code' => 'changed_mind', 'values' => ['ru' => 'Передумал', 'en' => 'Changed mind', 'lt' => 'Persigalvojo', 'pl' => 'Zmienil zdanie']],
            ['code' => 'documents', 'values' => ['ru' => 'Проблема с документами', 'en' => 'Document issue', 'lt' => 'Dokumentu problema', 'pl' => 'Problem z dokumentami']],
            ['code' => 'payment', 'values' => ['ru' => 'Проблема с оплатой', 'en' => 'Payment issue', 'lt' => 'Mokejimo problema', 'pl' => 'Problem z platnoscia']],
            ['code' => 'language', 'values' => ['ru' => 'Не подошёл язык обучения', 'en' => 'Training language did not fit', 'lt' => 'Netiko mokymo kalba', 'pl' => 'Jezyk nauki nie pasowal']],
            ['code' => 'car_type', 'values' => ['ru' => 'Не подошёл автомобиль / коробка передач', 'en' => 'Car or transmission did not fit', 'lt' => 'Netiko automobilis arba pavaru deze', 'pl' => 'Auto albo skrzynia nie pasowala']],
            ['code' => 'budget_too_low', 'values' => ['ru' => 'Бюджет слишком низкий', 'en' => 'Budget too low', 'lt' => 'Biudzetas per mazas', 'pl' => 'Budzet jest za niski']],
            ['code' => 'no_answer', 'values' => ['ru' => 'Нет ответа', 'en' => 'No answer', 'lt' => 'Nera atsakymo', 'pl' => 'Brak odpowiedzi']],
            ['code' => 'chose_competitor', 'values' => ['ru' => 'Выбрал конкурента', 'en' => 'Chose a competitor', 'lt' => 'Pasirinko konkurenta', 'pl' => 'Wybral konkurencje']],
            ['code' => 'not_ready', 'values' => ['ru' => 'Пока не готов', 'en' => 'Not ready yet', 'lt' => 'Dar nepasiruoses', 'pl' => 'Jeszcze nie gotowy']],
            ['code' => 'duplicate', 'values' => ['ru' => 'Дубликат заявки', 'en' => 'Duplicate lead', 'lt' => 'Pasikartojanti uzklausa', 'pl' => 'Duplikat leada']],
            ['code' => 'spam', 'values' => ['ru' => 'Спам', 'en' => 'Spam', 'lt' => 'Slamstas', 'pl' => 'Spam']],
            ['code' => 'other', 'values' => ['ru' => 'Другая причина', 'en' => 'Other reason', 'lt' => 'Kita priezastis', 'pl' => 'Inny powod']],
        ];
    }

    /**
     * @return array<int, array{slug: string, values: array<string, string>}>
     */
    private function tags(): array
    {
        return [
            ['slug' => 'hot_lead', 'values' => ['ru' => 'Горячий лид', 'en' => 'Hot lead', 'lt' => 'Karsta uzklausa', 'pl' => 'Goracy lead']],
            ['slug' => 'vip', 'values' => ['ru' => 'VIP', 'en' => 'VIP', 'lt' => 'VIP', 'pl' => 'VIP']],
            ['slug' => 'documents_needed', 'values' => ['ru' => 'Нужны документы', 'en' => 'Documents needed', 'lt' => 'Reikia dokumentu', 'pl' => 'Potrzebne dokumenty']],
            ['slug' => 'callback_required', 'values' => ['ru' => 'Нужен звонок', 'en' => 'Callback required', 'lt' => 'Reikia perskambinti', 'pl' => 'Wymagany kontakt']],
            ['slug' => 'ready_to_pay', 'values' => ['ru' => 'Готов к оплате', 'en' => 'Ready to pay', 'lt' => 'Pasiruoses moketi', 'pl' => 'Gotowy do platnosci']],
            ['slug' => 'repeat_request', 'values' => ['ru' => 'Повторное обращение', 'en' => 'Repeat request', 'lt' => 'Pakartotine uzklausa', 'pl' => 'Ponowne zgloszenie']],
            ['slug' => 'problematic', 'values' => ['ru' => 'Проблемный', 'en' => 'Problematic', 'lt' => 'Probleminis', 'pl' => 'Problemowy']],
            ['slug' => 'thinking', 'values' => ['ru' => 'Думает', 'en' => 'Thinking', 'lt' => 'Svarsto', 'pl' => 'Zastanawia sie']],
            ['slug' => 'urgent', 'values' => ['ru' => 'Срочно', 'en' => 'Urgent', 'lt' => 'Skubu', 'pl' => 'Pilne']],
            ['slug' => 'individual_schedule', 'values' => ['ru' => 'Индивидуальный график', 'en' => 'Individual schedule', 'lt' => 'Individualus grafikas', 'pl' => 'Indywidualny grafik']],
            ['slug' => 'wants_automatic', 'values' => ['ru' => 'Хочет автомат', 'en' => 'Wants automatic', 'lt' => 'Nori automato', 'pl' => 'Chce automat']],
            ['slug' => 'wants_manual', 'values' => ['ru' => 'Хочет механику', 'en' => 'Wants manual', 'lt' => 'Nori mechanines', 'pl' => 'Chce manual']],
            ['slug' => 'evening_training', 'values' => ['ru' => 'Вечернее обучение', 'en' => 'Evening training', 'lt' => 'Vakarinis mokymas', 'pl' => 'Szkolenie wieczorne']],
            ['slug' => 'weekends', 'values' => ['ru' => 'Выходные', 'en' => 'Weekends', 'lt' => 'Savaitgaliai', 'pl' => 'Weekendy']],
            ['slug' => 'corporate_client', 'values' => ['ru' => 'Корпоративный клиент', 'en' => 'Corporate client', 'lt' => 'Imones klientas', 'pl' => 'Klient firmowy']],
            ['slug' => 'price_sensitive', 'values' => ['ru' => 'Чувствителен к цене', 'en' => 'Price sensitive', 'lt' => 'Jautrus kainai', 'pl' => 'Wrazliwy na cene']],
        ];
    }
}
