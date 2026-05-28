<?php

namespace Database\Seeders;

use App\Models\ExamAttemptStatus;
use Database\Seeders\Concerns\SeedsFactoryBackedDictionaries;
use Illuminate\Database\Seeder;

class ExamAttemptStatusSeeder extends Seeder
{
    use SeedsFactoryBackedDictionaries;

    public function run(): void
    {
        $this->seedFactoryBackedDictionary(ExamAttemptStatus::class, 'code', [
            ['code' => 'planned', 'state' => 'planned', 'attributes' => $this->attributes(['ru' => 'Запланирована', 'en' => 'Planned', 'lt' => 'Suplanuota', 'pl' => 'Zaplanowana'], '#64748b')],
            ['code' => 'allowed', 'state' => 'allowed', 'attributes' => $this->attributes(['ru' => 'Допущена', 'en' => 'Allowed', 'lt' => 'Leista', 'pl' => 'Dopuszczona'], '#16a34a')],
            ['code' => 'blocked', 'state' => 'blocked', 'attributes' => $this->attributes(['ru' => 'Заблокирована', 'en' => 'Blocked', 'lt' => 'Uzblokuota', 'pl' => 'Zablokowana'], '#dc2626')],
            ['code' => 'in_progress', 'state' => 'inProgress', 'attributes' => $this->attributes(['ru' => 'Идет', 'en' => 'In progress', 'lt' => 'Vyksta', 'pl' => 'W toku'], '#0f766e')],
            ['code' => 'passed', 'state' => 'passed', 'attributes' => $this->attributes(['ru' => 'Сдана', 'en' => 'Passed', 'lt' => 'Islaikyta', 'pl' => 'Zdana'], '#16a34a')],
            ['code' => 'failed', 'state' => 'failed', 'attributes' => $this->attributes(['ru' => 'Не сдана', 'en' => 'Failed', 'lt' => 'Neislaikyta', 'pl' => 'Niezdana'], '#dc2626')],
            ['code' => 'no_show', 'state' => 'noShow', 'attributes' => $this->attributes(['ru' => 'Неявка', 'en' => 'No-show', 'lt' => 'Neatvyko', 'pl' => 'Nieobecnosc'], '#f97316')],
            ['code' => 'cancelled', 'state' => 'cancelled', 'attributes' => $this->attributes(['ru' => 'Отменена', 'en' => 'Cancelled', 'lt' => 'Atsaukta', 'pl' => 'Anulowana'], '#64748b')],
            ['code' => 'archived', 'state' => 'archived', 'attributes' => $this->attributes(['ru' => 'Архив', 'en' => 'Archived', 'lt' => 'Archyvuota', 'pl' => 'Zarchiwizowana'], '#334155')],
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
