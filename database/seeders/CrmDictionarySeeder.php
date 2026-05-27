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
            $modelClass::query()->updateOrCreate(
                [$keyColumn => $record[$keyColumn]],
                [
                    'name' => $record['values']['ru'] ?? $record['values']['en'],
                    'name_translations' => $record['values'],
                    'is_system' => true,
                    'is_active' => true,
                    'sort_order' => ($sortOrder + 1) * 10,
                ],
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
            LeadStatusEnum::ConsultationDone => ['ru' => 'Консультация проведена', 'en' => 'Consultation done', 'lt' => 'Konsultacija atlikta', 'pl' => 'Konsultacja wykonana'],
            LeadStatusEnum::WaitingDocuments => ['ru' => 'Ждёт документы', 'en' => 'Waiting for documents', 'lt' => 'Laukiama dokumentu', 'pl' => 'Oczekuje na dokumenty'],
            LeadStatusEnum::WaitingPayment => ['ru' => 'Ждёт оплату', 'en' => 'Waiting for payment', 'lt' => 'Laukiama apmokejimo', 'pl' => 'Oczekuje na platnosc'],
            LeadStatusEnum::AssignedToGroup => ['ru' => 'Записан в группу', 'en' => 'Assigned to group', 'lt' => 'Priskirta grupei', 'pl' => 'Przypisany do grupy'],
            LeadStatusEnum::BecameStudent => ['ru' => 'Стал учеником', 'en' => 'Became student', 'lt' => 'Tapo mokiniu', 'pl' => 'Zostal uczniem'],
            LeadStatusEnum::Rejected => ['ru' => 'Отказ', 'en' => 'Rejected', 'lt' => 'Atmesta', 'pl' => 'Odrzucony'],
            LeadStatusEnum::Duplicate => ['ru' => 'Дубль', 'en' => 'Duplicate', 'lt' => 'Dublikatas', 'pl' => 'Duplikat'],
            LeadStatusEnum::Spam => ['ru' => 'Спам', 'en' => 'Spam', 'lt' => 'Slamstas', 'pl' => 'Spam'],
            LeadStatusEnum::Archived => ['ru' => 'Архив', 'en' => 'Archived', 'lt' => 'Archyvas', 'pl' => 'Archiwum'],
        };
    }

    /**
     * @return array<int, array{code: string, values: array<string, string>}>
     */
    private function sources(): array
    {
        return [
            ['code' => 'website', 'values' => ['ru' => 'Сайт', 'en' => 'Website', 'lt' => 'Svetaine', 'pl' => 'Strona']],
            ['code' => 'phone', 'values' => ['ru' => 'Телефон', 'en' => 'Phone', 'lt' => 'Telefonas', 'pl' => 'Telefon']],
            ['code' => 'telegram', 'values' => ['ru' => 'Telegram', 'en' => 'Telegram', 'lt' => 'Telegram', 'pl' => 'Telegram']],
            ['code' => 'whatsapp', 'values' => ['ru' => 'WhatsApp', 'en' => 'WhatsApp', 'lt' => 'WhatsApp', 'pl' => 'WhatsApp']],
            ['code' => 'facebook', 'values' => ['ru' => 'Facebook', 'en' => 'Facebook', 'lt' => 'Facebook', 'pl' => 'Facebook']],
            ['code' => 'google_ads', 'values' => ['ru' => 'Google Ads', 'en' => 'Google Ads', 'lt' => 'Google Ads', 'pl' => 'Google Ads']],
            ['code' => 'referral', 'values' => ['ru' => 'Рекомендация', 'en' => 'Referral', 'lt' => 'Rekomendacija', 'pl' => 'Polecenie']],
        ];
    }

    /**
     * @return array<int, array{code: string, values: array<string, string>}>
     */
    private function lostReasons(): array
    {
        return [
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
            ['slug' => 'documents_needed', 'values' => ['ru' => 'Нужны документы', 'en' => 'Documents needed', 'lt' => 'Reikia dokumentu', 'pl' => 'Potrzebne dokumenty']],
            ['slug' => 'callback_required', 'values' => ['ru' => 'Нужен звонок', 'en' => 'Callback required', 'lt' => 'Reikia perskambinti', 'pl' => 'Wymagany kontakt']],
            ['slug' => 'price_sensitive', 'values' => ['ru' => 'Чувствителен к цене', 'en' => 'Price sensitive', 'lt' => 'Jautrus kainai', 'pl' => 'Wrazliwy na cene']],
        ];
    }
}
