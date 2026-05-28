<?php

namespace Database\Seeders;

use App\Models\Language;

class NotificationTranslationSeeder extends SystemTranslationSeeder
{
    public function run(): void
    {
        if (! Language::query()->exists()) {
            $this->call(LanguageSeeder::class);
        }

        $this->seedEntries($this->entries());
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function entries(): array
    {
        return [
            ...$this->channelEntries(),
            ...$this->templateEntries(),
            ...$this->reminderEntries(),
            $this->entry('notifications', 'notifications.seeders.channels_seeded', $this->translations('Каналы уведомлений обновлены.', 'Notification channels were updated.', 'Pranesimu kanalai atnaujinti.', 'Kanaly powiadomien zostaly zaktualizowane.')),
            $this->entry('notifications', 'notifications.seeders.templates_seeded', $this->translations('Шаблоны уведомлений обновлены.', 'Notification templates were updated.', 'Pranesimu sablonai atnaujinti.', 'Szablony powiadomien zostaly zaktualizowane.')),
            $this->entry('notifications', 'notifications.seeders.reminders_seeded', $this->translations('Правила напоминаний обновлены.', 'Reminder rules were updated.', 'Priminimu taisykles atnaujintos.', 'Reguly przypomnien zostaly zaktualizowane.')),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function channelEntries(): array
    {
        $channels = [
            'internal' => $this->translations('Внутренние уведомления', 'Internal notifications', 'Vidiniai pranesimai', 'Powiadomienia wewnetrzne'),
            'email' => $this->translations('Эл. почта', 'Email', 'El. pastas', 'E-mail'),
            'sms_placeholder' => $this->translations('SMS заготовка', 'SMS placeholder', 'SMS paruosinys', 'Szablon SMS'),
            'whatsapp_placeholder' => $this->translations('WhatsApp заготовка', 'WhatsApp placeholder', 'WhatsApp paruosinys', 'Szablon WhatsApp'),
            'telegram_placeholder' => $this->translations('Telegram заготовка', 'Telegram placeholder', 'Telegram paruosinys', 'Szablon Telegram'),
            'push_placeholder' => $this->translations('Push заготовка', 'Push placeholder', 'Push paruosinys', 'Szablon push'),
        ];

        $entries = [];

        foreach ($channels as $code => $translations) {
            $entries[] = $this->entry('notifications', 'notifications.channels.'.$code, $translations);
            $entries[] = $this->entry('communication', 'communication.channels.'.$code, $translations);
        }

        return $entries;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function templateEntries(): array
    {
        $templates = [
            'student_welcome' => $this->translations('Приветствие ученика', 'Student welcome', 'Mokinio pasveikinimas', 'Powitanie ucznia'),
            'lead_follow_up' => $this->translations('Повторный контакт с лидом', 'Lead follow-up', 'Pakartotinis kontaktas su uzklausa', 'Ponowny kontakt z leadem'),
            'lesson_reminder' => $this->translations('Напоминание о занятии', 'Lesson reminder', 'Pamokos priminimas', 'Przypomnienie o lekcji'),
            'driving_lesson_reminder' => $this->translations('Напоминание о вождении', 'Driving lesson reminder', 'Vairavimo pamokos priminimas', 'Przypomnienie o jezdzie'),
            'payment_due' => $this->translations('Напоминание об оплате', 'Payment due', 'Mokejimo priminimas', 'Przypomnienie o platnosci'),
            'document_missing' => $this->translations('Не хватает документа', 'Missing document', 'Truksta dokumento', 'Brakujacy dokument'),
            'document_rejected' => $this->translations('Документ отклонен', 'Document rejected', 'Dokumentas atmestas', 'Dokument odrzucony'),
            'exam_reminder' => $this->translations('Напоминание об экзамене', 'Exam reminder', 'Egzamino priminimas', 'Przypomnienie o egzaminie'),
            'contract_generated' => $this->translations('Договор создан', 'Contract generated', 'Sutartis sukurta', 'Umowa utworzona'),
        ];

        return collect($templates)
            ->map(fn (array $translations, string $code): array => $this->entry('notifications', 'notifications.templates.'.$code, $translations))
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function reminderEntries(): array
    {
        $reminders = [
            'lesson_tomorrow' => $this->translations('Занятие завтра', 'Lesson tomorrow', 'Pamoka rytoj', 'Lekcja jutro'),
            'lesson_one_hour_before' => $this->translations('Занятие через час', 'Lesson one hour before', 'Pamoka po valandos', 'Lekcja za godzine'),
            'payment_due' => $this->translations('Срок оплаты', 'Payment due', 'Mokejimo terminas', 'Termin platnosci'),
            'document_missing' => $this->translations('Не хватает документа', 'Document missing', 'Truksta dokumento', 'Brak dokumentu'),
            'exam_reminder' => $this->translations('Напоминание об экзамене', 'Exam reminder', 'Egzamino priminimas', 'Przypomnienie o egzaminie'),
            'lead_follow_up' => $this->translations('Повторный контакт с лидом', 'Lead follow-up', 'Pakartotinis kontaktas su uzklausa', 'Ponowny kontakt z leadem'),
        ];

        return collect($reminders)
            ->map(fn (array $translations, string $code): array => $this->entry('notifications', 'notifications.reminders.'.$code, $translations))
            ->values()
            ->all();
    }

    /**
     * @return array<string, string>
     */
    private function translations(string $ru, string $en, string $lt, string $pl): array
    {
        return compact('ru', 'en', 'lt', 'pl');
    }
}
