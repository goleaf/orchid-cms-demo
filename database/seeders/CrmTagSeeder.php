<?php

namespace Database\Seeders;

use App\Models\LeadTag;
use Database\Seeders\Concerns\SeedsFactoryBackedDictionaries;
use Illuminate\Database\Seeder;

class CrmTagSeeder extends Seeder
{
    use SeedsFactoryBackedDictionaries;

    public function run(): void
    {
        $this->seedFactoryBackedDictionary(LeadTag::class, 'slug', [
            ['slug' => 'hot', 'state' => 'hot'],
            ['slug' => 'hot_lead', 'attributes' => $this->attributes(['ru' => 'Горячий лид', 'en' => 'Hot lead', 'lt' => 'Karsta uzklausa', 'pl' => 'Goracy lead'], '#dc2626')],
            ['slug' => 'vip', 'state' => 'vip'],
            ['slug' => 'needs_call', 'state' => 'needsCall'],
            ['slug' => 'documents_needed', 'attributes' => $this->attributes(['ru' => 'Нужны документы', 'en' => 'Documents needed', 'lt' => 'Reikia dokumentu', 'pl' => 'Potrzebne dokumenty'], '#ca8a04')],
            ['slug' => 'needs_documents', 'state' => 'needsDocuments'],
            ['slug' => 'callback_required', 'attributes' => $this->attributes(['ru' => 'Нужен звонок', 'en' => 'Callback required', 'lt' => 'Reikia perskambinti', 'pl' => 'Wymagany kontakt'], '#0891b2')],
            ['slug' => 'ready_to_pay', 'state' => 'readyToPay'],
            ['slug' => 'repeat_request', 'state' => 'repeatRequest'],
            ['slug' => 'problematic', 'state' => 'problematic'],
            ['slug' => 'thinking', 'state' => 'thinking'],
            ['slug' => 'urgent', 'state' => 'urgent'],
            ['slug' => 'individual_schedule', 'state' => 'individualSchedule'],
            ['slug' => 'wants_automatic', 'state' => 'wantsAutomatic'],
            ['slug' => 'wants_manual', 'state' => 'wantsManual'],
            ['slug' => 'evening_training', 'state' => 'eveningTraining'],
            ['slug' => 'weekends', 'attributes' => $this->attributes(['ru' => 'Выходные', 'en' => 'Weekends', 'lt' => 'Savaitgaliai', 'pl' => 'Weekendy'], '#6366f1')],
            ['slug' => 'weekend_training', 'state' => 'weekendTraining'],
            ['slug' => 'corporate_client', 'state' => 'corporateClient'],
            ['slug' => 'price_sensitive', 'attributes' => $this->attributes(['ru' => 'Чувствителен к цене', 'en' => 'Price sensitive', 'lt' => 'Jautrus kainai', 'pl' => 'Wrazliwy na cene'], '#f97316')],
        ]);
    }

    /**
     * @param  array<string, string>  $translations
     * @return array<string, mixed>
     */
    private function attributes(array $translations, string $color): array
    {
        return [
            'name' => $translations['ru'],
            'name_translations' => $translations,
            'description_translations' => $translations,
            'color' => $color,
            'is_system' => true,
            'is_active' => true,
        ];
    }
}
