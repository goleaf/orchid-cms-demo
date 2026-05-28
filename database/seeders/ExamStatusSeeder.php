<?php

namespace Database\Seeders;

use App\Models\ExamStatus;
use Database\Seeders\Concerns\SeedsFactoryBackedDictionaries;
use Illuminate\Database\Seeder;

class ExamStatusSeeder extends Seeder
{
    use SeedsFactoryBackedDictionaries;

    public function run(): void
    {
        $this->seedFactoryBackedDictionary(ExamStatus::class, 'code', [
            ['code' => 'draft', 'state' => 'draft', 'attributes' => $this->attributes(['ru' => 'Черновик', 'en' => 'Draft', 'lt' => 'Juodrastis', 'pl' => 'Szkic'], '#64748b')],
            ['code' => 'scheduled', 'state' => 'scheduled', 'attributes' => $this->attributes(['ru' => 'Запланирован', 'en' => 'Scheduled', 'lt' => 'Suplanuotas', 'pl' => 'Zaplanowany'], '#2563eb')],
            ['code' => 'open', 'state' => 'open', 'attributes' => $this->attributes(['ru' => 'Открыт', 'en' => 'Open', 'lt' => 'Atviras', 'pl' => 'Otwarty'], '#16a34a')],
            ['code' => 'in_progress', 'state' => 'inProgress', 'attributes' => $this->attributes(['ru' => 'Идет', 'en' => 'In progress', 'lt' => 'Vyksta', 'pl' => 'W toku'], '#0f766e')],
            ['code' => 'completed', 'state' => 'completed', 'attributes' => $this->attributes(['ru' => 'Завершен', 'en' => 'Completed', 'lt' => 'Baigtas', 'pl' => 'Zakonczony'], '#475569')],
            ['code' => 'cancelled', 'state' => 'cancelled', 'attributes' => $this->attributes(['ru' => 'Отменен', 'en' => 'Cancelled', 'lt' => 'Atsauktas', 'pl' => 'Anulowany'], '#dc2626')],
            ['code' => 'archived', 'state' => 'archived', 'attributes' => $this->attributes(['ru' => 'Архив', 'en' => 'Archived', 'lt' => 'Archyvuotas', 'pl' => 'Zarchiwizowany'], '#334155')],
        ]);
    }

    /**
     * @param  array<string, string>  $translations
     * @return array<string, mixed>
     */
    private function attributes(array $translations, string $color): array
    {
        return [
            'name' => $translations['en'],
            'name_translations' => $translations,
            'description_translations' => $translations,
            'color' => $color,
            'is_system' => true,
            'is_active' => true,
        ];
    }
}
