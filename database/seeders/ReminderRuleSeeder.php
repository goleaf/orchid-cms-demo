<?php

namespace Database\Seeders;

use App\Models\NotificationTemplate;
use App\Models\ReminderRule;
use Illuminate\Database\Seeder;

class ReminderRuleSeeder extends Seeder
{
    public function run(): void
    {
        if (NotificationTemplate::query()->whereIn('code', collect($this->records())->pluck('template_code')->all())->count() < collect($this->records())->pluck('template_code')->unique()->count()) {
            $this->call(NotificationTemplateSeeder::class);
        }

        $templates = NotificationTemplate::query()
            ->whereIn('code', collect($this->records())->pluck('template_code')->all())
            ->get(['id', 'code'])
            ->keyBy('code');

        foreach ($this->records() as $record) {
            $factory = ReminderRule::factory();
            $state = $record['state'] ?? null;

            if (is_string($state) && method_exists($factory, $state)) {
                $factory = $factory->{$state}();
            }

            $rule = $factory->active()->make([
                'code' => $record['code'],
                'name_translations' => $record['name_translations'],
                'trigger_type' => $record['trigger_type'],
                'target_type' => $record['target_type'],
                'template_id' => $templates[$record['template_code']]?->id ?? null,
                'offset_minutes' => $record['offset_minutes'],
                'metadata' => ['seeded' => true],
            ]);

            $attributes = $rule->only($rule->getFillable());
            unset($attributes['code']);

            ReminderRule::query()->updateOrCreate(
                ['code' => $record['code']],
                $attributes,
            );
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function records(): array
    {
        return [
            [
                'code' => 'lesson_tomorrow',
                'state' => 'lessonTomorrow',
                'name_translations' => $this->translations('Занятие завтра', 'Lesson tomorrow', 'Pamoka rytoj', 'Lekcja jutro'),
                'trigger_type' => ReminderRule::TRIGGER_BEFORE_LESSON,
                'target_type' => 'student',
                'template_code' => 'lesson_reminder',
                'offset_minutes' => -1440,
            ],
            [
                'code' => 'lesson_one_hour_before',
                'state' => 'lessonOneHourBefore',
                'name_translations' => $this->translations('Занятие через час', 'Lesson one hour before', 'Pamoka po valandos', 'Lekcja za godzine'),
                'trigger_type' => ReminderRule::TRIGGER_BEFORE_LESSON,
                'target_type' => 'student',
                'template_code' => 'driving_lesson_reminder',
                'offset_minutes' => -60,
            ],
            [
                'code' => 'payment_due',
                'state' => 'paymentDue',
                'name_translations' => $this->translations('Срок оплаты', 'Payment due', 'Mokejimo terminas', 'Termin platnosci'),
                'trigger_type' => ReminderRule::TRIGGER_BEFORE_PAYMENT_DUE,
                'target_type' => 'student',
                'template_code' => 'payment_due',
                'offset_minutes' => -1440,
            ],
            [
                'code' => 'document_missing',
                'state' => 'documentMissing',
                'name_translations' => $this->translations('Не хватает документа', 'Document missing', 'Truksta dokumento', 'Brak dokumentu'),
                'trigger_type' => ReminderRule::TRIGGER_MANUAL,
                'target_type' => 'student',
                'template_code' => 'document_missing',
                'offset_minutes' => 0,
            ],
            [
                'code' => 'exam_reminder',
                'state' => 'examReminder',
                'name_translations' => $this->translations('Напоминание об экзамене', 'Exam reminder', 'Egzamino priminimas', 'Przypomnienie o egzaminie'),
                'trigger_type' => ReminderRule::TRIGGER_BEFORE_EXAM,
                'target_type' => 'student',
                'template_code' => 'exam_reminder',
                'offset_minutes' => -1440,
            ],
            [
                'code' => 'lead_follow_up',
                'state' => 'leadFollowUp',
                'name_translations' => $this->translations('Повторный контакт с лидом', 'Lead follow-up', 'Pakartotinis kontaktas su uzklausa', 'Ponowny kontakt z leadem'),
                'trigger_type' => ReminderRule::TRIGGER_AFTER_SIGNUP,
                'target_type' => 'lead',
                'template_code' => 'lead_follow_up',
                'offset_minutes' => 1440,
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
