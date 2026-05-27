<?php

namespace Database\Seeders;

use App\Models\LeadStatus;
use Database\Seeders\Concerns\SeedsFactoryBackedDictionaries;
use Illuminate\Database\Seeder;

class CrmStatusSeeder extends Seeder
{
    use SeedsFactoryBackedDictionaries;

    public function run(): void
    {
        $this->seedFactoryBackedDictionary(LeadStatus::class, 'code', $this->records());

        LeadStatus::query()
            ->where('code', '!=', 'new')
            ->update(['is_default' => false]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function records(): array
    {
        return [
            ['code' => 'new', 'state' => 'newStatus'],
            ['code' => 'no_answer', 'state' => 'noAnswer'],
            ['code' => 'contacted', 'state' => 'contacted'],
            ['code' => 'consultation', 'state' => 'consultation'],
            ['code' => 'consultation_done', 'attributes' => $this->attributes(
                ['ru' => 'Консультация проведена', 'en' => 'Consultation done', 'lt' => 'Konsultacija atlikta', 'pl' => 'Konsultacja wykonana'],
                '#7c3aed',
            )],
            ['code' => 'waiting_documents', 'state' => 'waitingDocuments'],
            ['code' => 'waiting_payment', 'state' => 'waitingPayment'],
            ['code' => 'ready_to_enroll', 'state' => 'readyToEnroll'],
            ['code' => 'enrolled', 'state' => 'enrolled'],
            ['code' => 'assigned_to_group', 'attributes' => $this->attributes(
                ['ru' => 'Записан в группу', 'en' => 'Assigned to group', 'lt' => 'Priskirta grupei', 'pl' => 'Przypisany do grupy'],
                '#16a34a',
            )],
            ['code' => 'became_student', 'attributes' => $this->attributes(
                ['ru' => 'Стал учеником', 'en' => 'Became student', 'lt' => 'Tapo mokiniu', 'pl' => 'Zostal uczniem'],
                '#15803d',
                ['is_final' => true, 'is_success' => true],
            )],
            ['code' => 'lost', 'state' => 'lost'],
            ['code' => 'rejected', 'attributes' => $this->attributes(
                ['ru' => 'Отказ', 'en' => 'Rejected', 'lt' => 'Atmesta', 'pl' => 'Odrzucony'],
                '#dc2626',
                ['is_final' => true, 'is_lost' => true],
            )],
            ['code' => 'duplicate', 'state' => 'duplicate'],
            ['code' => 'spam', 'state' => 'spam'],
            ['code' => 'archived', 'state' => 'archived'],
        ];
    }

    /**
     * @param  array<string, string>  $translations
     * @param  array<string, mixed>  $flags
     * @return array<string, mixed>
     */
    private function attributes(array $translations, string $color, array $flags = []): array
    {
        return [
            'name' => $translations['ru'],
            'name_translations' => $translations,
            'description_translations' => $translations,
            'color' => $color,
            'is_system' => true,
            'is_active' => true,
            'is_public' => false,
            'is_default' => false,
            'is_final' => false,
            'is_success' => false,
            'is_lost' => false,
            'is_duplicate' => false,
            'is_spam' => false,
            ...$flags,
        ];
    }
}
