<?php

namespace Database\Seeders;

use App\Models\ExamResultStatus;
use Database\Seeders\Concerns\SeedsFactoryBackedDictionaries;
use Illuminate\Database\Seeder;

class ExamResultStatusSeeder extends Seeder
{
    use SeedsFactoryBackedDictionaries;

    public function run(): void
    {
        $this->seedFactoryBackedDictionary(ExamResultStatus::class, 'code', [
            ['code' => 'pending', 'state' => 'pending', 'attributes' => $this->attributes(['ru' => 'Ожидает', 'en' => 'Pending', 'lt' => 'Laukiama', 'pl' => 'Oczekuje'], '#64748b')],
            ['code' => 'passed', 'state' => 'passed', 'attributes' => $this->attributes(['ru' => 'Сдано', 'en' => 'Passed', 'lt' => 'Islaikyta', 'pl' => 'Zdane'], '#16a34a')],
            ['code' => 'failed', 'state' => 'failed', 'attributes' => $this->attributes(['ru' => 'Не сдано', 'en' => 'Failed', 'lt' => 'Neislaikyta', 'pl' => 'Niezdane'], '#dc2626')],
            ['code' => 'needs_retake', 'state' => 'needsRetake', 'attributes' => $this->attributes(['ru' => 'Нужна пересдача', 'en' => 'Needs retake', 'lt' => 'Reikia perlaikymo', 'pl' => 'Wymaga poprawki'], '#f97316')],
            ['code' => 'cancelled', 'state' => 'cancelled', 'attributes' => $this->attributes(['ru' => 'Отменено', 'en' => 'Cancelled', 'lt' => 'Atsaukta', 'pl' => 'Anulowane'], '#64748b')],
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
