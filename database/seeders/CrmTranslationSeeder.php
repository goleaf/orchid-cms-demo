<?php

namespace Database\Seeders;

use App\Models\Language;

class CrmTranslationSeeder extends SystemTranslationSeeder
{
    public function run(): void
    {
        if (! Language::query()->exists()) {
            $this->call(LanguageSeeder::class);
        }

        parent::run();

        $this->seedEntries($this->crmLeadModuleEntries());
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function crmLeadModuleEntries(): array
    {
        return [
            ...$this->crmMenuEntries(),
            ...$this->crmLeadScreenEntries(),
            ...$this->crmLeadDictionaryAliasEntries(),
            ...$this->crmTaskAndCallEntries(),
            ...$this->crmActivityAndFilterEntries(),
            ...$this->crmPipelineAndDictionaryEntries(),
            ...$this->crmValidationAndPermissionEntries(),
            ...$this->crmValidationAttributeEntries(),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function crmMenuEntries(): array
    {
        return [
            $this->entry('menu', 'menu.crm.unassigned', ['ru' => 'Без менеджера', 'en' => 'Unassigned', 'lt' => 'Nepriskirta', 'pl' => 'Nieprzypisane']),
            $this->entry('menu', 'menu.crm.settings', ['ru' => 'Настройки CRM', 'en' => 'CRM settings', 'lt' => 'CRM nustatymai', 'pl' => 'Ustawienia CRM']),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function crmLeadScreenEntries(): array
    {
        return [
            $this->entry('crm', 'crm.leads.view_title', ['ru' => 'Просмотр лида', 'en' => 'View lead', 'lt' => 'Perziureti uzklausa', 'pl' => 'Podglad leada']),
            $this->entry('crm', 'crm.leads.empty.no_tasks', ['ru' => 'Задач нет.', 'en' => 'No tasks.', 'lt' => 'Uzduociu nera.', 'pl' => 'Brak zadan.']),
            $this->entry('crm', 'crm.leads.empty.no_activities', ['ru' => 'Активности нет.', 'en' => 'No activities.', 'lt' => 'Veiklos nera.', 'pl' => 'Brak aktywnosci.']),
            $this->entry('crm', 'crm.leads.empty.no_duplicates', ['ru' => 'Дубликаты не найдены.', 'en' => 'No duplicates found.', 'lt' => 'Dublikatu nerasta.', 'pl' => 'Nie znaleziono duplikatow.']),
            $this->entry('crm', 'crm.leads.sections.contact_information', ['ru' => 'Контактная информация', 'en' => 'Contact information', 'lt' => 'Kontaktine informacija', 'pl' => 'Dane kontaktowe']),
            $this->entry('crm', 'crm.leads.sections.activities', ['ru' => 'Активности', 'en' => 'Activities', 'lt' => 'Veiklos', 'pl' => 'Aktywnosci']),
            $this->entry('crm', 'crm.leads.sections.conversion', ['ru' => 'Конвертация', 'en' => 'Conversion', 'lt' => 'Konversija', 'pl' => 'Konwersja']),
            $this->entry('crm', 'crm.leads.fields.id', ['ru' => 'ID', 'en' => 'ID', 'lt' => 'ID', 'pl' => 'ID']),
            $this->entry('crm', 'crm.leads.fields.lead_number', ['ru' => 'Номер лида', 'en' => 'Lead number', 'lt' => 'Uzklausos numeris', 'pl' => 'Numer leada']),
            $this->entry('crm', 'crm.leads.fields.middle_name', ['ru' => 'Отчество', 'en' => 'Middle name', 'lt' => 'Antras vardas', 'pl' => 'Drugie imie']),
            $this->entry('crm', 'crm.leads.fields.preferred_training_language', ['ru' => 'Язык обучения', 'en' => 'Training language', 'lt' => 'Mokymo kalba', 'pl' => 'Jezyk szkolenia']),
            $this->entry('crm', 'crm.leads.fields.preferred_gearbox', ['ru' => 'Коробка передач', 'en' => 'Gearbox', 'lt' => 'Pavaru deze', 'pl' => 'Skrzynia biegow']),
            $this->entry('crm', 'crm.leads.fields.converted_student', ['ru' => 'Созданный ученик', 'en' => 'Converted student', 'lt' => 'Sukurtas mokinys', 'pl' => 'Utworzony uczen']),
            $this->entry('crm', 'crm.leads.fields.converted_enrollment', ['ru' => 'Созданная запись', 'en' => 'Converted enrollment', 'lt' => 'Sukurta registracija', 'pl' => 'Utworzony zapis']),
            $this->entry('crm', 'crm.leads.fields.created_by', ['ru' => 'Создал', 'en' => 'Created by', 'lt' => 'Sukure', 'pl' => 'Utworzyl']),
            $this->entry('crm', 'crm.leads.fields.updated_by', ['ru' => 'Обновил', 'en' => 'Updated by', 'lt' => 'Atnaujino', 'pl' => 'Zaktualizowal']),
            $this->entry('crm', 'crm.leads.actions.save_and_return', ['ru' => 'Сохранить и вернуться', 'en' => 'Save and return', 'lt' => 'Issaugoti ir grizti', 'pl' => 'Zapisz i wroc']),
            $this->entry('crm', 'crm.leads.actions.edit', ['ru' => 'Редактировать', 'en' => 'Edit', 'lt' => 'Redaguoti', 'pl' => 'Edytuj']),
            $this->entry('crm', 'crm.leads.actions.delete', ['ru' => 'Удалить', 'en' => 'Delete', 'lt' => 'Istrinti', 'pl' => 'Usun']),
            $this->entry('crm', 'crm.leads.actions.archive', ['ru' => 'Архивировать', 'en' => 'Archive', 'lt' => 'Archyvuoti', 'pl' => 'Archiwizuj']),
            $this->entry('crm', 'crm.leads.actions.complete_task', ['ru' => 'Выполнить задачу', 'en' => 'Complete task', 'lt' => 'Uzbaigti uzduoti', 'pl' => 'Zakoncz zadanie']),
            $this->entry('crm', 'crm.leads.actions.cancel_task', ['ru' => 'Отменить задачу', 'en' => 'Cancel task', 'lt' => 'Atsaukti uzduoti', 'pl' => 'Anuluj zadanie']),
            $this->entry('crm', 'crm.leads.actions.reopen', ['ru' => 'Открыть заново', 'en' => 'Reopen', 'lt' => 'Atidaryti is naujo', 'pl' => 'Otworz ponownie']),
            $this->entry('crm', 'crm.leads.actions.prepare_conversion', ['ru' => 'Подготовить к конвертации', 'en' => 'Prepare conversion', 'lt' => 'Paruosti konversijai', 'pl' => 'Przygotuj konwersje']),
            $this->entry('crm', 'crm.leads.actions.convert_to_student', ['ru' => 'Конвертировать в ученика', 'en' => 'Convert to student', 'lt' => 'Konvertuoti i mokini', 'pl' => 'Konwertuj na ucznia']),
            $this->entry('crm', 'crm.leads.actions.export_csv', ['ru' => 'Экспорт CSV', 'en' => 'Export CSV', 'lt' => 'Eksportuoti CSV', 'pl' => 'Eksportuj CSV']),
            $this->entry('crm', 'crm.leads.actions.clear_filters', ['ru' => 'Очистить фильтры', 'en' => 'Clear filters', 'lt' => 'Isvalyti filtrus', 'pl' => 'Wyczysc filtry']),
            $this->entry('crm', 'crm.leads.messages.deleted', ['ru' => 'Лид удалён.', 'en' => 'Lead deleted.', 'lt' => 'Uzklausa istrinta.', 'pl' => 'Lead usuniety.']),
            $this->entry('crm', 'crm.leads.messages.archived', ['ru' => 'Лид архивирован.', 'en' => 'Lead archived.', 'lt' => 'Uzklausa archyvuota.', 'pl' => 'Lead zarchiwizowany.']),
            $this->entry('crm', 'crm.leads.messages.status_changed', ['ru' => 'Статус лида изменён.', 'en' => 'Lead status changed.', 'lt' => 'Uzklausos busena pakeista.', 'pl' => 'Status leada zmieniony.']),
            $this->entry('crm', 'crm.leads.messages.manager_assigned', ['ru' => 'Менеджер назначен.', 'en' => 'Manager assigned.', 'lt' => 'Vadybininkas priskirtas.', 'pl' => 'Menedzer przypisany.']),
            $this->entry('crm', 'crm.leads.messages.note_added', ['ru' => 'Заметка добавлена.', 'en' => 'Note added.', 'lt' => 'Pastaba prideta.', 'pl' => 'Notatka dodana.']),
            $this->entry('crm', 'crm.leads.messages.call_logged', ['ru' => 'Звонок зафиксирован.', 'en' => 'Call logged.', 'lt' => 'Skambutis uzfiksuotas.', 'pl' => 'Rozmowa zapisana.']),
            $this->entry('crm', 'crm.leads.messages.task_created', ['ru' => 'Задача создана.', 'en' => 'Task created.', 'lt' => 'Uzduotis sukurta.', 'pl' => 'Zadanie utworzone.']),
            $this->entry('crm', 'crm.leads.messages.task_completed', ['ru' => 'Задача выполнена.', 'en' => 'Task completed.', 'lt' => 'Uzduotis uzbaigta.', 'pl' => 'Zadanie zakonczone.']),
            $this->entry('crm', 'crm.leads.messages.task_cancelled', ['ru' => 'Задача отменена.', 'en' => 'Task cancelled.', 'lt' => 'Uzduotis atsaukta.', 'pl' => 'Zadanie anulowane.']),
            $this->entry('crm', 'crm.leads.messages.reopened', ['ru' => 'Лид открыт заново.', 'en' => 'Lead reopened.', 'lt' => 'Uzklausa atidaryta is naujo.', 'pl' => 'Lead otwarty ponownie.']),
            $this->entry('crm', 'crm.leads.messages.prepared_for_conversion', ['ru' => 'Лид подготовлен к конвертации.', 'en' => 'Lead prepared for conversion.', 'lt' => 'Uzklausa paruosta konversijai.', 'pl' => 'Lead przygotowany do konwersji.']),
            $this->entry('crm', 'crm.leads.messages.export_started', ['ru' => 'Экспорт лидов запущен.', 'en' => 'Lead export started.', 'lt' => 'Uzklausu eksportas pradetas.', 'pl' => 'Eksport leadow rozpoczety.']),
            $this->entry('crm', 'crm.leads.messages.duplicate_detected', ['ru' => 'Найден возможный дубль.', 'en' => 'Possible duplicate detected.', 'lt' => 'Aptiktas galimas dublikatas.', 'pl' => 'Wykryto mozliwy duplikat.']),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function crmLeadDictionaryAliasEntries(): array
    {
        return [
            ...$this->entries('crm', 'crm.leads.sources.', [
                'website' => ['ru' => 'Сайт', 'en' => 'Website', 'lt' => 'Svetaine', 'pl' => 'Strona'],
                'callback' => ['ru' => 'Обратный звонок', 'en' => 'Callback', 'lt' => 'Perskambinimas', 'pl' => 'Oddzwonienie'],
                'contact_form' => ['ru' => 'Контактная форма', 'en' => 'Contact form', 'lt' => 'Kontaktine forma', 'pl' => 'Formularz kontaktowy'],
                'phone' => ['ru' => 'Телефон', 'en' => 'Phone', 'lt' => 'Telefonas', 'pl' => 'Telefon'],
                'office' => ['ru' => 'Офис', 'en' => 'Office', 'lt' => 'Biuras', 'pl' => 'Biuro'],
                'google_ads' => ['ru' => 'Google Ads', 'en' => 'Google Ads', 'lt' => 'Google Ads', 'pl' => 'Google Ads'],
                'facebook' => ['ru' => 'Facebook', 'en' => 'Facebook', 'lt' => 'Facebook', 'pl' => 'Facebook'],
                'instagram' => ['ru' => 'Instagram', 'en' => 'Instagram', 'lt' => 'Instagram', 'pl' => 'Instagram'],
                'tiktok' => ['ru' => 'TikTok', 'en' => 'TikTok', 'lt' => 'TikTok', 'pl' => 'TikTok'],
                'telegram' => ['ru' => 'Telegram', 'en' => 'Telegram', 'lt' => 'Telegram', 'pl' => 'Telegram'],
                'whatsapp' => ['ru' => 'WhatsApp', 'en' => 'WhatsApp', 'lt' => 'WhatsApp', 'pl' => 'WhatsApp'],
                'referral' => ['ru' => 'Рекомендация', 'en' => 'Referral', 'lt' => 'Rekomendacija', 'pl' => 'Polecenie'],
                'partner' => ['ru' => 'Партнёр', 'en' => 'Partner', 'lt' => 'Partneris', 'pl' => 'Partner'],
                'other' => ['ru' => 'Другое', 'en' => 'Other', 'lt' => 'Kita', 'pl' => 'Inne'],
            ]),
            ...$this->entries('crm', 'crm.leads.lost_reasons.', [
                'price' => ['ru' => 'Цена', 'en' => 'Price', 'lt' => 'Kaina', 'pl' => 'Cena'],
                'schedule' => ['ru' => 'Расписание', 'en' => 'Schedule', 'lt' => 'Tvarkarastis', 'pl' => 'Termin'],
                'location' => ['ru' => 'Локация', 'en' => 'Location', 'lt' => 'Vieta', 'pl' => 'Lokalizacja'],
                'competitor' => ['ru' => 'Конкурент', 'en' => 'Competitor', 'lt' => 'Konkurentas', 'pl' => 'Konkurencja'],
                'no_response' => ['ru' => 'Не отвечает', 'en' => 'No response', 'lt' => 'Neatsako', 'pl' => 'Brak odpowiedzi'],
                'changed_mind' => ['ru' => 'Передумал', 'en' => 'Changed mind', 'lt' => 'Persigalvojo', 'pl' => 'Zmienil zdanie'],
                'documents' => ['ru' => 'Документы', 'en' => 'Documents', 'lt' => 'Dokumentai', 'pl' => 'Dokumenty'],
                'payment' => ['ru' => 'Оплата', 'en' => 'Payment', 'lt' => 'Mokejimas', 'pl' => 'Platnosc'],
                'language' => ['ru' => 'Язык', 'en' => 'Language', 'lt' => 'Kalba', 'pl' => 'Jezyk'],
                'car_type' => ['ru' => 'Тип авто', 'en' => 'Car type', 'lt' => 'Automobilio tipas', 'pl' => 'Typ auta'],
                'duplicate' => ['ru' => 'Дубликат', 'en' => 'Duplicate', 'lt' => 'Dublikatas', 'pl' => 'Duplikat'],
                'spam' => ['ru' => 'Спам', 'en' => 'Spam', 'lt' => 'Slamstas', 'pl' => 'Spam'],
                'other' => ['ru' => 'Другое', 'en' => 'Other', 'lt' => 'Kita', 'pl' => 'Inne'],
            ]),
            ...$this->entries('crm', 'crm.leads.tags.', [
                'hot' => ['ru' => 'Горячий', 'en' => 'Hot', 'lt' => 'Karstas', 'pl' => 'Goracy'],
                'vip' => ['ru' => 'VIP', 'en' => 'VIP', 'lt' => 'VIP', 'pl' => 'VIP'],
                'needs_call' => ['ru' => 'Нужен звонок', 'en' => 'Needs call', 'lt' => 'Reikia skambucio', 'pl' => 'Wymaga telefonu'],
                'ready_to_pay' => ['ru' => 'Готов к оплате', 'en' => 'Ready to pay', 'lt' => 'Pasiruoses moketi', 'pl' => 'Gotowy do platnosci'],
                'needs_documents' => ['ru' => 'Нужны документы', 'en' => 'Needs documents', 'lt' => 'Reikia dokumentu', 'pl' => 'Wymaga dokumentow'],
                'repeat_request' => ['ru' => 'Повторное обращение', 'en' => 'Repeat request', 'lt' => 'Pakartotine uzklausa', 'pl' => 'Ponowne zgloszenie'],
                'problematic' => ['ru' => 'Проблемный', 'en' => 'Problematic', 'lt' => 'Probleminis', 'pl' => 'Problemowy'],
                'thinking' => ['ru' => 'Думает', 'en' => 'Thinking', 'lt' => 'Svarsto', 'pl' => 'Zastanawia sie'],
                'urgent' => ['ru' => 'Срочно', 'en' => 'Urgent', 'lt' => 'Skubu', 'pl' => 'Pilne'],
                'individual_schedule' => ['ru' => 'Индивидуальный график', 'en' => 'Individual schedule', 'lt' => 'Individualus grafikas', 'pl' => 'Indywidualny grafik'],
                'wants_automatic' => ['ru' => 'Хочет автомат', 'en' => 'Wants automatic', 'lt' => 'Nori automato', 'pl' => 'Chce automat'],
                'wants_manual' => ['ru' => 'Хочет механику', 'en' => 'Wants manual', 'lt' => 'Nori mechanines', 'pl' => 'Chce manual'],
                'evening_training' => ['ru' => 'Вечернее обучение', 'en' => 'Evening training', 'lt' => 'Vakarinis mokymas', 'pl' => 'Szkolenie wieczorne'],
                'weekend_training' => ['ru' => 'Обучение по выходным', 'en' => 'Weekend training', 'lt' => 'Savaitgalio mokymai', 'pl' => 'Szkolenie weekendowe'],
                'corporate_client' => ['ru' => 'Корпоративный клиент', 'en' => 'Corporate client', 'lt' => 'Imones klientas', 'pl' => 'Klient firmowy'],
            ]),
            ...$this->entries('crm', 'crm.leads.priorities.', [
                'low' => ['ru' => 'Низкий', 'en' => 'Low', 'lt' => 'Zemas', 'pl' => 'Niski'],
                'normal' => ['ru' => 'Обычный', 'en' => 'Normal', 'lt' => 'Iprastas', 'pl' => 'Normalny'],
                'high' => ['ru' => 'Высокий', 'en' => 'High', 'lt' => 'Aukstas', 'pl' => 'Wysoki'],
                'urgent' => ['ru' => 'Срочный', 'en' => 'Urgent', 'lt' => 'Skubus', 'pl' => 'Pilny'],
            ]),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function crmTaskAndCallEntries(): array
    {
        return [
            $this->entry('crm', 'crm.tasks.empty.no_tasks', ['ru' => 'Задач нет.', 'en' => 'No tasks.', 'lt' => 'Uzduociu nera.', 'pl' => 'Brak zadan.']),
            $this->entry('crm', 'crm.tasks.fields.created_by', ['ru' => 'Создал', 'en' => 'Created by', 'lt' => 'Sukure', 'pl' => 'Utworzyl']),
            $this->entry('crm', 'crm.tasks.fields.cancelled_at', ['ru' => 'Отменено', 'en' => 'Cancelled at', 'lt' => 'Atsaukta', 'pl' => 'Anulowano']),
            $this->entry('crm', 'crm.tasks.fields.created_at', ['ru' => 'Создано', 'en' => 'Created at', 'lt' => 'Sukurta', 'pl' => 'Utworzono']),
            $this->entry('crm', 'crm.leads.filters.all_priorities', ['ru' => 'Все приоритеты', 'en' => 'All priorities', 'lt' => 'Visi prioritetai', 'pl' => 'Wszystkie priorytety']),
            $this->entry('crm', 'crm.tasks.defaults.contact_new_manual_lead', ['ru' => 'Связаться с новым ручным лидом', 'en' => 'Contact the new manual lead', 'lt' => 'Susisiekti su nauja rankine uzklausa', 'pl' => 'Skontaktuj sie z nowym recznym leadem']),
            $this->entry('crm', 'crm.calls.title', ['ru' => 'Звонки', 'en' => 'Calls', 'lt' => 'Skambuciai', 'pl' => 'Rozmowy']),
            $this->entry('crm', 'crm.calls.fields.result', ['ru' => 'Результат', 'en' => 'Result', 'lt' => 'Rezultatas', 'pl' => 'Wynik']),
            $this->entry('crm', 'crm.calls.fields.duration_seconds', ['ru' => 'Длительность, сек.', 'en' => 'Duration, seconds', 'lt' => 'Trukme sekundemis', 'pl' => 'Czas w sekundach']),
            $this->entry('crm', 'crm.calls.fields.comment', ['ru' => 'Комментарий', 'en' => 'Comment', 'lt' => 'Komentaras', 'pl' => 'Komentarz']),
            $this->entry('crm', 'crm.calls.fields.next_follow_up_at', ['ru' => 'Следующий контакт', 'en' => 'Next follow-up', 'lt' => 'Kitas kontaktas', 'pl' => 'Nastepny kontakt']),
            ...$this->entries('crm', 'crm.calls.results.', [
                'reached' => ['ru' => 'Дозвонились', 'en' => 'Reached', 'lt' => 'Atsiliepe', 'pl' => 'Dodzwoniono sie'],
                'no_answer' => ['ru' => 'Не ответил', 'en' => 'No answer', 'lt' => 'Neatsiliepe', 'pl' => 'Brak odpowiedzi'],
                'wrong_number' => ['ru' => 'Неверный номер', 'en' => 'Wrong number', 'lt' => 'Neteisingas numeris', 'pl' => 'Zly numer'],
                'call_back_later' => ['ru' => 'Перезвонить позже', 'en' => 'Call back later', 'lt' => 'Perskambinti veliau', 'pl' => 'Oddzwonic pozniej'],
                'thinking' => ['ru' => 'Думает', 'en' => 'Thinking', 'lt' => 'Svarsto', 'pl' => 'Zastanawia sie'],
                'ready_to_pay' => ['ru' => 'Готов оплатить', 'en' => 'Ready to pay', 'lt' => 'Pasiruoses moketi', 'pl' => 'Gotowy do platnosci'],
                'refused' => ['ru' => 'Отказался', 'en' => 'Refused', 'lt' => 'Atsisake', 'pl' => 'Odmowil'],
            ]),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function crmActivityAndFilterEntries(): array
    {
        return [
            $this->entry('crm', 'crm.activities.types.archived', ['ru' => 'Архивировано', 'en' => 'Archived', 'lt' => 'Archyvuota', 'pl' => 'Zarchiwizowano']),
            $this->entry('crm', 'crm.leads.filters.training_group', ['ru' => 'Фильтр по группе', 'en' => 'Filter by training group', 'lt' => 'Filtruoti pagal mokymo grupe', 'pl' => 'Filtruj wedlug grupy szkoleniowej']),
            $this->entry('crm', 'crm.leads.filters.course_category', ['ru' => 'Фильтр по категории курса', 'en' => 'Filter by course category', 'lt' => 'Filtruoti pagal kurso kategorija', 'pl' => 'Filtruj wedlug kategorii kursu']),
            $this->entry('crm', 'crm.leads.filters.lost_reason', ['ru' => 'Фильтр по причине отказа', 'en' => 'Filter by lost reason', 'lt' => 'Filtruoti pagal praradimo priezasti', 'pl' => 'Filtruj wedlug powodu utraty']),
            $this->entry('crm', 'crm.leads.filters.all_lost_reasons', ['ru' => 'Все причины отказа', 'en' => 'All lost reasons', 'lt' => 'Visos praradimo priezastys', 'pl' => 'Wszystkie powody utraty']),
            $this->entry('crm', 'crm.leads.filters.priority', ['ru' => 'Фильтр по приоритету', 'en' => 'Filter by priority', 'lt' => 'Filtruoti pagal prioriteta', 'pl' => 'Filtruj wedlug priorytetu']),
            $this->entry('crm', 'crm.leads.filters.next_follow_up_from', ['ru' => 'Следующий контакт с', 'en' => 'Next follow-up from', 'lt' => 'Kitas kontaktas nuo', 'pl' => 'Nastepny kontakt od']),
            $this->entry('crm', 'crm.leads.filters.next_follow_up_to', ['ru' => 'Следующий контакт до', 'en' => 'Next follow-up to', 'lt' => 'Kitas kontaktas iki', 'pl' => 'Nastepny kontakt do']),
            $this->entry('crm', 'crm.leads.filters.last_contacted_from', ['ru' => 'Последний контакт с', 'en' => 'Last contacted from', 'lt' => 'Paskutinis kontaktas nuo', 'pl' => 'Ostatni kontakt od']),
            $this->entry('crm', 'crm.leads.filters.last_contacted_to', ['ru' => 'Последний контакт до', 'en' => 'Last contacted to', 'lt' => 'Paskutinis kontaktas iki', 'pl' => 'Ostatni kontakt do']),
            $this->entry('crm', 'crm.leads.filters.utm_source', ['ru' => 'UTM источник', 'en' => 'UTM source', 'lt' => 'UTM saltinis', 'pl' => 'Zrodlo UTM']),
            $this->entry('crm', 'crm.leads.filters.utm_medium', ['ru' => 'UTM канал', 'en' => 'UTM medium', 'lt' => 'UTM kanalas', 'pl' => 'Medium UTM']),
            $this->entry('crm', 'crm.leads.filters.utm_campaign', ['ru' => 'UTM кампания', 'en' => 'UTM campaign', 'lt' => 'UTM kampanija', 'pl' => 'Kampania UTM']),
            $this->entry('crm', 'crm.leads.filters.form_name', ['ru' => 'Форма', 'en' => 'Form name', 'lt' => 'Formos pavadinimas', 'pl' => 'Nazwa formularza']),
            $this->entry('crm', 'crm.leads.filters.only_my', ['ru' => 'Только мои', 'en' => 'Only mine', 'lt' => 'Tik mano', 'pl' => 'Tylko moje']),
            $this->entry('crm', 'crm.leads.filters.only_unassigned', ['ru' => 'Только без менеджера', 'en' => 'Only unassigned', 'lt' => 'Tik nepriskirtos', 'pl' => 'Tylko nieprzypisane']),
            $this->entry('crm', 'crm.leads.filters.only_due_today', ['ru' => 'Только на сегодня', 'en' => 'Only due today', 'lt' => 'Tik siandienai', 'pl' => 'Tylko na dzisiaj']),
            $this->entry('crm', 'crm.leads.filters.only_duplicates', ['ru' => 'Только дубли', 'en' => 'Only duplicates', 'lt' => 'Tik dublikatai', 'pl' => 'Tylko duplikaty']),
            $this->entry('crm', 'crm.leads.filters.only_open', ['ru' => 'Только открытые', 'en' => 'Only open', 'lt' => 'Tik atviros', 'pl' => 'Tylko otwarte']),
            $this->entry('crm', 'crm.leads.filters.only_closed', ['ru' => 'Только закрытые', 'en' => 'Only closed', 'lt' => 'Tik uzdarytos', 'pl' => 'Tylko zamkniete']),
            $this->entry('crm', 'crm.leads.filters.only_converted', ['ru' => 'Только конвертированные', 'en' => 'Only converted', 'lt' => 'Tik konvertuotos', 'pl' => 'Tylko skonwertowane']),
            $this->entry('crm', 'crm.leads.filters.only_not_converted', ['ru' => 'Только не конвертированные', 'en' => 'Only not converted', 'lt' => 'Tik nekonvertuotos', 'pl' => 'Tylko nieskonwertowane']),
            $this->entry('crm', 'crm.leads.segments.all', ['ru' => 'Все лиды', 'en' => 'All leads', 'lt' => 'Visos uzklausos', 'pl' => 'Wszystkie leady']),
            $this->entry('crm', 'crm.leads.segments.new', ['ru' => 'Новые', 'en' => 'New', 'lt' => 'Naujos', 'pl' => 'Nowe']),
            $this->entry('crm', 'crm.leads.segments.my_leads', ['ru' => 'Мои лиды', 'en' => 'My leads', 'lt' => 'Mano uzklausos', 'pl' => 'Moje leady']),
            $this->entry('crm', 'crm.leads.segments.call_today', ['ru' => 'Позвонить сегодня', 'en' => 'Call today', 'lt' => 'Skambinti siandien', 'pl' => 'Dzwonic dzisiaj']),
            $this->entry('crm', 'crm.leads.segments.overdue', ['ru' => 'Просроченные', 'en' => 'Overdue', 'lt' => 'Veluojancios', 'pl' => 'Zalegle']),
            $this->entry('crm', 'crm.leads.segments.duplicates', ['ru' => 'Дубли', 'en' => 'Duplicates', 'lt' => 'Dublikatai', 'pl' => 'Duplikaty']),
            $this->entry('crm', 'crm.leads.segments.converted', ['ru' => 'Конвертированные', 'en' => 'Converted', 'lt' => 'Konvertuotos', 'pl' => 'Skonwertowane']),
            $this->entry('crm', 'crm.leads.segments.ready_to_enroll', ['ru' => 'Готовы к записи', 'en' => 'Ready to enroll', 'lt' => 'Pasiruose registracijai', 'pl' => 'Gotowi do zapisu']),
            $this->entry('crm', 'crm.leads.segments.not_converted', ['ru' => 'Не конвертированные', 'en' => 'Not converted', 'lt' => 'Nekonvertuotos', 'pl' => 'Nieskonwertowane']),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function crmPipelineAndDictionaryEntries(): array
    {
        return [
            $this->entry('crm', 'crm.pipeline.empty.no_leads', ['ru' => 'Лидов в воронке нет.', 'en' => 'No leads in the pipeline.', 'lt' => 'Eigoje uzklausu nera.', 'pl' => 'Brak leadow w lejku.']),
            $this->entry('crm', 'crm.pipeline.actions.change_status', ['ru' => 'Изменить статус', 'en' => 'Change status', 'lt' => 'Keisti busena', 'pl' => 'Zmien status']),
            $this->entry('crm', 'crm.pipeline.actions.open_lead', ['ru' => 'Открыть лид', 'en' => 'Open lead', 'lt' => 'Atidaryti uzklausa', 'pl' => 'Otworz lead']),
            $this->entry('crm', 'crm.dictionaries.fields.description', ['ru' => 'Описание', 'en' => 'Description', 'lt' => 'Aprasymas', 'pl' => 'Opis']),
            $this->entry('crm', 'crm.dictionaries.actions.create', ['ru' => 'Создать', 'en' => 'Create', 'lt' => 'Sukurti', 'pl' => 'Utworz']),
            $this->entry('crm', 'crm.dictionaries.actions.save', ['ru' => 'Сохранить', 'en' => 'Save', 'lt' => 'Issaugoti', 'pl' => 'Zapisz']),
            $this->entry('crm', 'crm.dictionaries.actions.delete', ['ru' => 'Удалить', 'en' => 'Delete', 'lt' => 'Istrinti', 'pl' => 'Usun']),
            $this->entry('crm', 'crm.dictionaries.messages.created', ['ru' => 'Запись словаря создана.', 'en' => 'Dictionary item created.', 'lt' => 'Zodyno irasas sukurtas.', 'pl' => 'Wpis slownika utworzony.']),
            $this->entry('crm', 'crm.dictionaries.messages.updated', ['ru' => 'Запись словаря обновлена.', 'en' => 'Dictionary item updated.', 'lt' => 'Zodyno irasas atnaujintas.', 'pl' => 'Wpis slownika zaktualizowany.']),
            $this->entry('crm', 'crm.dictionaries.messages.cannot_delete_used_item', ['ru' => 'Нельзя удалить запись словаря, которая используется.', 'en' => 'Cannot delete a dictionary item that is in use.', 'lt' => 'Negalima istrinti naudojamo zodyno iraso.', 'pl' => 'Nie mozna usunac uzywanego wpisu slownika.']),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function crmValidationAndPermissionEntries(): array
    {
        return [
            $this->entry('crm', 'crm.leads.validation.lost_reason_required', ['ru' => 'Выберите причину отказа.', 'en' => 'Select a lost reason.', 'lt' => 'Pasirinkite praradimo priezasti.', 'pl' => 'Wybierz powod utraty.']),
            $this->entry('crm', 'crm.leads.validation.manager_required', ['ru' => 'Выберите менеджера.', 'en' => 'Select a manager.', 'lt' => 'Pasirinkite vadybininka.', 'pl' => 'Wybierz menedzera.']),
            $this->entry('crm', 'crm.leads.validation.task_due_date_invalid', ['ru' => 'Укажите корректный срок задачи.', 'en' => 'Enter a valid task due date.', 'lt' => 'Iveskite teisinga uzduoties termina.', 'pl' => 'Podaj poprawny termin zadania.']),
            $this->entry('crm', 'crm.leads.validation.export_not_allowed', ['ru' => 'Экспорт лидов недоступен.', 'en' => 'Lead export is not allowed.', 'lt' => 'Uzklausu eksportas negalimas.', 'pl' => 'Eksport leadow jest niedozwolony.']),
            $this->entry('permissions', 'permissions.crm.leads.archive', ['ru' => 'Архивация лидов', 'en' => 'Archive leads', 'lt' => 'Archyvuoti uzklausas', 'pl' => 'Archiwizacja leadow']),
            $this->entry('permissions', 'permissions.crm.leads.override_status_transition', ['ru' => 'Переопределение переходов статусов', 'en' => 'Override status transitions', 'lt' => 'Nepaisyti busenu perejimu', 'pl' => 'Nadpisywanie zmian statusu']),
            $this->entry('permissions', 'permissions.crm.leads.manage_tags', ['ru' => 'Управление тегами лидов', 'en' => 'Manage lead tags', 'lt' => 'Tvarkyti uzklausu zymas', 'pl' => 'Zarzadzanie tagami leadow']),
            $this->entry('permissions', 'permissions.crm.pipeline.view', ['ru' => 'Просмотр CRM воронки', 'en' => 'View CRM pipeline', 'lt' => 'Perziureti CRM eiga', 'pl' => 'Podglad lejka CRM']),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function crmValidationAttributeEntries(): array
    {
        return [
            ...$this->entries('validation', 'validation.attributes.lead.', [
                'full_name' => ['ru' => 'имя и фамилия', 'en' => 'full name', 'lt' => 'vardas ir pavarde', 'pl' => 'imie i nazwisko'],
                'phone' => ['ru' => 'телефон', 'en' => 'phone', 'lt' => 'telefonas', 'pl' => 'telefon'],
                'email' => ['ru' => 'email', 'en' => 'email', 'lt' => 'el. pastas', 'pl' => 'email'],
                'status_id' => ['ru' => 'статус', 'en' => 'status', 'lt' => 'busena', 'pl' => 'status'],
                'source_id' => ['ru' => 'источник', 'en' => 'source', 'lt' => 'saltinis', 'pl' => 'zrodlo'],
                'manager_id' => ['ru' => 'менеджер', 'en' => 'manager', 'lt' => 'vadybininkas', 'pl' => 'menedzer'],
                'lost_reason_id' => ['ru' => 'причина отказа', 'en' => 'lost reason', 'lt' => 'praradimo priezastis', 'pl' => 'powod utraty'],
                'course_id' => ['ru' => 'курс', 'en' => 'course', 'lt' => 'kursas', 'pl' => 'kurs'],
                'branch_id' => ['ru' => 'филиал', 'en' => 'branch', 'lt' => 'filialas', 'pl' => 'oddzial'],
                'training_group_id' => ['ru' => 'учебная группа', 'en' => 'training group', 'lt' => 'mokymo grupe', 'pl' => 'grupa szkoleniowa'],
                'next_follow_up_at' => ['ru' => 'следующий контакт', 'en' => 'next follow-up', 'lt' => 'kitas kontaktas', 'pl' => 'nastepny kontakt'],
            ]),
            $this->entry('validation', 'validation.attributes.lead_task.title_translations', ['ru' => 'название задачи', 'en' => 'task title', 'lt' => 'uzduoties pavadinimas', 'pl' => 'tytul zadania']),
            $this->entry('validation', 'validation.attributes.lead_task.due_at', ['ru' => 'срок задачи', 'en' => 'task due date', 'lt' => 'uzduoties terminas', 'pl' => 'termin zadania']),
            $this->entry('validation', 'validation.attributes.lead_status.code', ['ru' => 'код статуса', 'en' => 'status code', 'lt' => 'busenos kodas', 'pl' => 'kod statusu']),
            $this->entry('validation', 'validation.attributes.lead_status.name_translations', ['ru' => 'переводы названия статуса', 'en' => 'status name translations', 'lt' => 'busenos pavadinimo vertimai', 'pl' => 'tlumaczenia nazwy statusu']),
        ];
    }

    /**
     * @param  array<string, array<string, string>>  $records
     * @return array<int, array<string, mixed>>
     */
    private function entries(string $group, string $prefix, array $records): array
    {
        $entries = [];

        foreach ($records as $key => $values) {
            $entries[] = $this->entry($group, $prefix.$key, $values);
        }

        return $entries;
    }
}
