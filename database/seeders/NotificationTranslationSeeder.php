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
            ...$this->menuEntries(),
            ...$this->fieldEntries(),
            ...$this->actionEntries(),
            ...$this->channelEntries(),
            ...$this->statusEntries(),
            ...$this->priorityEntries(),
            ...$this->validationEntries(),
            ...$this->permissionEntries(),
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
    private function menuEntries(): array
    {
        $items = [
            'notifications' => $this->translations('Уведомления', 'Notifications', 'Pranesimai', 'Powiadomienia'),
            'notifications.messages' => $this->translations('Сообщения', 'Messages', 'Zinutes', 'Wiadomosci'),
            'notifications.templates' => $this->translations('Шаблоны', 'Templates', 'Sablonai', 'Szablony'),
            'notifications.reminders' => $this->translations('Напоминания', 'Reminders', 'Priminimai', 'Przypomnienia'),
            'notifications.deliveries' => $this->translations('Доставки', 'Deliveries', 'Pristatymai', 'Dostarczenia'),
            'notifications.threads' => $this->translations('Диалоги', 'Threads', 'Gijos', 'Watki'),
            'notifications.preferences' => $this->translations('Предпочтения', 'Preferences', 'Nuostatos', 'Preferencje'),
            'notifications.channels' => $this->translations('Каналы', 'Channels', 'Kanalai', 'Kanaly'),
            'notifications.settings' => $this->translations('Настройки уведомлений', 'Notification settings', 'Pranesimu nustatymai', 'Ustawienia powiadomien'),
        ];

        return collect($items)
            ->map(fn (array $translations, string $key): array => $this->entry('menu', 'menu.'.$key, $translations))
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function fieldEntries(): array
    {
        $fields = [
            'channel' => $this->translations('Канал', 'Channel', 'Kanalas', 'Kanal'),
            'template' => $this->translations('Шаблон', 'Template', 'Sablonas', 'Szablon'),
            'template_version' => $this->translations('Версия шаблона', 'Template version', 'Sablono versija', 'Wersja szablonu'),
            'subject' => $this->translations('Тема', 'Subject', 'Tema', 'Temat'),
            'body' => $this->translations('Текст', 'Body', 'Tekstas', 'Tresc'),
            'priority' => $this->translations('Приоритет', 'Priority', 'Prioritetas', 'Priorytet'),
            'status' => $this->translations('Статус', 'Status', 'Busena', 'Status'),
            'recipient' => $this->translations('Получатель', 'Recipient', 'Gavejas', 'Odbiorca'),
            'user' => $this->translations('Пользователь', 'User', 'Vartotojas', 'Uzytkownik'),
            'student' => $this->translations('Ученик', 'Student', 'Mokinys', 'Uczen'),
            'lead' => $this->translations('Лид', 'Lead', 'Uzklausa', 'Lead'),
            'email' => $this->translations('Эл. почта', 'Email', 'El. pastas', 'E-mail'),
            'phone' => $this->translations('Телефон', 'Phone', 'Telefonas', 'Telefon'),
            'locale' => $this->translations('Язык', 'Locale', 'Lokalė', 'Jezyk'),
            'scheduled_at' => $this->translations('Запланировано на', 'Scheduled at', 'Suplanuota', 'Zaplanowano na'),
            'sent_at' => $this->translations('Отправлено', 'Sent at', 'Issiusta', 'Wyslano'),
            'delivered_at' => $this->translations('Доставлено', 'Delivered at', 'Pristatyta', 'Dostarczono'),
            'failed_at' => $this->translations('Ошибка', 'Failed at', 'Nepavyko', 'Nieudane'),
            'provider' => $this->translations('Провайдер', 'Provider', 'Tiekejas', 'Dostawca'),
            'provider_message_id' => $this->translations('ID сообщения провайдера', 'Provider message ID', 'Tiekejo zinutes ID', 'ID wiadomosci dostawcy'),
            'attempt_no' => $this->translations('Номер попытки', 'Attempt number', 'Bandymo numeris', 'Numer proby'),
            'error_message' => $this->translations('Текст ошибки', 'Error message', 'Klaidos tekstas', 'Komunikat bledu'),
        ];

        return collect($fields)
            ->map(fn (array $translations, string $key): array => $this->entry('notifications', 'notifications.fields.'.$key, $translations))
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function actionEntries(): array
    {
        $actions = [
            'create' => $this->translations('Создать', 'Create', 'Sukurti', 'Utworz'),
            'save' => $this->translations('Сохранить', 'Save', 'Issaugoti', 'Zapisz'),
            'send' => $this->translations('Отправить', 'Send', 'Siusti', 'Wyslij'),
            'schedule' => $this->translations('Запланировать', 'Schedule', 'Suplanuoti', 'Zaplanuj'),
            'cancel' => $this->translations('Отменить', 'Cancel', 'Atsaukti', 'Anuluj'),
            'retry' => $this->translations('Повторить', 'Retry', 'Kartoti', 'Ponow'),
            'mark_delivered' => $this->translations('Отметить доставленным', 'Mark delivered', 'Pazymeti pristatytu', 'Oznacz jako dostarczone'),
            'mark_failed' => $this->translations('Отметить ошибкой', 'Mark failed', 'Pazymeti nepavykusiu', 'Oznacz jako nieudane'),
            'preview' => $this->translations('Предпросмотр', 'Preview', 'Perziura', 'Podglad'),
            'publish_version' => $this->translations('Опубликовать версию', 'Publish version', 'Publikuoti versija', 'Opublikuj wersje'),
            'create_thread' => $this->translations('Создать диалог', 'Create thread', 'Sukurti gija', 'Utworz watek'),
            'add_message' => $this->translations('Добавить сообщение', 'Add message', 'Prideti zinute', 'Dodaj wiadomosc'),
            'process_due_reminders' => $this->translations('Обработать напоминания', 'Process due reminders', 'Apdoroti priminimus', 'Przetworz przypomnienia'),
        ];

        return collect($actions)
            ->map(fn (array $translations, string $key): array => $this->entry('notifications', 'notifications.actions.'.$key, $translations))
            ->values()
            ->all();
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
    private function statusEntries(): array
    {
        $statuses = [
            'draft' => $this->translations('Черновик', 'Draft', 'Juodrastis', 'Szkic'),
            'scheduled' => $this->translations('Запланировано', 'Scheduled', 'Suplanuota', 'Zaplanowano'),
            'queued' => $this->translations('В очереди', 'Queued', 'Eileje', 'W kolejce'),
            'sent' => $this->translations('Отправлено', 'Sent', 'Issiusta', 'Wyslane'),
            'delivered' => $this->translations('Доставлено', 'Delivered', 'Pristatyta', 'Dostarczone'),
            'failed' => $this->translations('Ошибка', 'Failed', 'Nepavyko', 'Nieudane'),
            'cancelled' => $this->translations('Отменено', 'Cancelled', 'Atsaukta', 'Anulowane'),
            'archived' => $this->translations('Архив', 'Archived', 'Archyvas', 'Archiwum'),
        ];

        return collect($statuses)
            ->map(fn (array $translations, string $key): array => $this->entry('notifications', 'notifications.statuses.'.$key, $translations))
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function priorityEntries(): array
    {
        $priorities = [
            'low' => $this->translations('Низкий', 'Low', 'Zemas', 'Niski'),
            'normal' => $this->translations('Обычный', 'Normal', 'Normalus', 'Normalny'),
            'high' => $this->translations('Высокий', 'High', 'Aukstas', 'Wysoki'),
            'urgent' => $this->translations('Срочный', 'Urgent', 'Skubus', 'Pilny'),
        ];

        return collect($priorities)
            ->map(fn (array $translations, string $key): array => $this->entry('notifications', 'notifications.priorities.'.$key, $translations))
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function validationEntries(): array
    {
        $messages = [
            'channel_not_active' => $this->translations('Выбранный канал уведомлений неактивен.', 'The selected notification channel is not active.', 'Pasirinktas pranesimu kanalas neaktyvus.', 'Wybrany kanal powiadomien jest nieaktywny.'),
            'template_not_published' => $this->translations('Выберите опубликованный шаблон уведомления.', 'Select a published notification template.', 'Pasirinkite paskelbta pranesimo sablona.', 'Wybierz opublikowany szablon powiadomienia.'),
            'unsafe_template_content' => $this->translations('Текст шаблона содержит небезопасное содержимое.', 'Template content contains unsafe content.', 'Sablono tekste yra nesaugaus turinio.', 'Tresci szablonu zawieraja niebezpieczna zawartosc.'),
            'recipient_required' => $this->translations('Укажите получателя уведомления.', 'Enter a notification recipient.', 'Nurodykite pranesimo gaveja.', 'Podaj odbiorce powiadomienia.'),
            'invalid_target' => $this->translations('Цель уведомления недоступна.', 'The notification target is unavailable.', 'Pranesimo tikslas nepasiekiamas.', 'Cel powiadomienia jest niedostepny.'),
            'message_cannot_be_sent' => $this->translations('Это уведомление нельзя отправить.', 'This notification message cannot be sent.', 'Sio pranesimo issiusti negalima.', 'Tego powiadomienia nie mozna wyslac.'),
            'delivery_cannot_be_retried' => $this->translations('Эту доставку нельзя повторить.', 'This delivery cannot be retried.', 'Sio pristatymo pakartoti negalima.', 'Tego dostarczenia nie mozna ponowic.'),
            'invalid_reminder_trigger' => $this->translations('Тип запуска напоминания некорректен.', 'The reminder trigger is invalid.', 'Priminimo paleidiklis neteisingas.', 'Wyzwalacz przypomnienia jest nieprawidlowy.'),
            'invalid_schedule_date' => $this->translations('Дата напоминания некорректна.', 'The reminder schedule date is invalid.', 'Priminimo data neteisinga.', 'Data przypomnienia jest nieprawidlowa.'),
            'preference_not_allowed' => $this->translations('Эта настройка уведомлений недоступна.', 'This notification preference is not allowed.', 'Si pranesimu nuostata neleidziama.', 'Ta preferencja powiadomien jest niedozwolona.'),
            'invalid_priority' => $this->translations('Приоритет уведомления некорректен.', 'The notification priority is invalid.', 'Pranesimo prioritetas neteisingas.', 'Priorytet powiadomienia jest nieprawidlowy.'),
            'invalid_direction' => $this->translations('Направление сообщения некорректно.', 'The communication direction is invalid.', 'Komunikacijos kryptis neteisinga.', 'Kierunek komunikacji jest nieprawidlowy.'),
        ];

        return collect($messages)
            ->map(fn (array $translations, string $key): array => $this->entry('notifications', 'notifications.validation.'.$key, $translations))
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function permissionEntries(): array
    {
        $permissions = [
            'groups.notifications' => $this->translations('Уведомления', 'Notifications', 'Pranesimai', 'Powiadomienia'),
            'notifications.messages.view' => $this->translations('Просмотр сообщений', 'View messages', 'Perziureti zinutes', 'Podglad wiadomosci'),
            'notifications.messages.create' => $this->translations('Создание сообщений', 'Create messages', 'Kurti zinutes', 'Tworzenie wiadomosci'),
            'notifications.messages.send' => $this->translations('Отправка сообщений', 'Send messages', 'Siusti zinutes', 'Wysylanie wiadomosci'),
            'notifications.messages.cancel' => $this->translations('Отмена сообщений', 'Cancel messages', 'Atsaukti zinutes', 'Anulowanie wiadomosci'),
            'notifications.messages.retry' => $this->translations('Повтор отправки сообщений', 'Retry messages', 'Kartoti zinutes', 'Ponawianie wiadomosci'),
            'notifications.templates.view' => $this->translations('Просмотр шаблонов', 'View templates', 'Perziureti sablonus', 'Podglad szablonow'),
            'notifications.templates.create' => $this->translations('Создание шаблонов', 'Create templates', 'Kurti sablonus', 'Tworzenie szablonow'),
            'notifications.templates.update' => $this->translations('Редактирование шаблонов', 'Update templates', 'Atnaujinti sablonus', 'Aktualizacja szablonow'),
            'notifications.templates.publish' => $this->translations('Публикация шаблонов', 'Publish templates', 'Publikuoti sablonus', 'Publikowanie szablonow'),
            'notifications.reminders.view' => $this->translations('Просмотр напоминаний', 'View reminders', 'Perziureti priminimus', 'Podglad przypomnien'),
            'notifications.reminders.manage' => $this->translations('Управление напоминаниями', 'Manage reminders', 'Tvarkyti priminimus', 'Zarzadzanie przypomnieniami'),
            'notifications.reminders.process' => $this->translations('Обработка напоминаний', 'Process reminders', 'Apdoroti priminimus', 'Przetwarzanie przypomnien'),
            'notifications.deliveries.view' => $this->translations('Просмотр доставок', 'View deliveries', 'Perziureti pristatymus', 'Podglad dostarczen'),
            'notifications.deliveries.manage' => $this->translations('Управление доставками', 'Manage deliveries', 'Tvarkyti pristatymus', 'Zarzadzanie dostarczeniami'),
            'notifications.threads.view' => $this->translations('Просмотр диалогов', 'View threads', 'Perziureti gijas', 'Podglad watkow'),
            'notifications.threads.manage' => $this->translations('Управление диалогами', 'Manage threads', 'Tvarkyti gijas', 'Zarzadzanie watkami'),
            'notifications.preferences.manage' => $this->translations('Управление предпочтениями', 'Manage preferences', 'Tvarkyti nuostatas', 'Zarzadzanie preferencjami'),
            'notifications.channels.manage' => $this->translations('Управление каналами', 'Manage channels', 'Tvarkyti kanalus', 'Zarzadzanie kanalami'),
            'notifications.export' => $this->translations('Экспорт уведомлений', 'Export notifications', 'Eksportuoti pranesimus', 'Eksport powiadomien'),
        ];

        return collect($permissions)
            ->map(fn (array $translations, string $key): array => $this->entry('permissions', 'permissions.'.$key, $translations))
            ->values()
            ->all();
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
