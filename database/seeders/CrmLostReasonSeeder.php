<?php

namespace Database\Seeders;

use App\Models\LeadLostReason;
use Database\Seeders\Concerns\SeedsFactoryBackedDictionaries;
use Illuminate\Database\Seeder;

class CrmLostReasonSeeder extends Seeder
{
    use SeedsFactoryBackedDictionaries;

    public function run(): void
    {
        $this->seedFactoryBackedDictionary(LeadLostReason::class, 'code', [
            ['code' => 'price', 'state' => 'price'],
            ['code' => 'schedule', 'state' => 'schedule'],
            ['code' => 'location', 'state' => 'location'],
            ['code' => 'competitor', 'state' => 'competitor'],
            ['code' => 'no_response', 'state' => 'noResponse'],
            ['code' => 'changed_mind', 'state' => 'changedMind'],
            ['code' => 'documents', 'state' => 'documents'],
            ['code' => 'payment', 'state' => 'payment'],
            ['code' => 'language', 'state' => 'language'],
            ['code' => 'car_type', 'state' => 'carType'],
            ['code' => 'budget_too_low', 'attributes' => $this->attributes(['ru' => 'Бюджет слишком низкий', 'en' => 'Budget too low', 'lt' => 'Biudzetas per mazas', 'pl' => 'Budzet jest za niski'])],
            ['code' => 'no_answer', 'attributes' => $this->attributes(['ru' => 'Нет ответа', 'en' => 'No answer', 'lt' => 'Nera atsakymo', 'pl' => 'Brak odpowiedzi'])],
            ['code' => 'chose_competitor', 'attributes' => $this->attributes(['ru' => 'Выбрал конкурента', 'en' => 'Chose competitor', 'lt' => 'Pasirinko konkurenta', 'pl' => 'Wybral konkurencje'])],
            ['code' => 'not_ready', 'attributes' => $this->attributes(['ru' => 'Пока не готов', 'en' => 'Not ready yet', 'lt' => 'Dar nepasiruoses', 'pl' => 'Jeszcze nie gotowy'])],
            ['code' => 'duplicate', 'state' => 'duplicate'],
            ['code' => 'spam', 'state' => 'spam'],
            ['code' => 'other', 'state' => 'other'],
        ]);
    }

    /**
     * @param  array<string, string>  $translations
     * @return array<string, mixed>
     */
    private function attributes(array $translations): array
    {
        return [
            'name' => $translations['ru'],
            'name_translations' => $translations,
            'description_translations' => $translations,
            'color' => '#dc2626',
            'is_system' => true,
            'is_active' => true,
        ];
    }
}
