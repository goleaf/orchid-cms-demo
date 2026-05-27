<?php

namespace Database\Seeders;

use App\Models\Language;
use App\Models\TranslationString;
use App\Models\TranslationValue;
use Illuminate\Database\Seeder;

class SystemTranslationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ($this->keys() as $definition) {
            $translationString = TranslationString::query()->updateOrCreate(
                ['key' => $definition['key']],
                [
                    'group' => $definition['group'],
                    'description' => $definition['description'] ?? null,
                    'is_system' => $definition['is_system'] ?? true,
                ],
            );

            foreach (Language::activeCodes() as $languageCode) {
                TranslationValue::query()->updateOrCreate(
                    [
                        'translation_string_id' => $translationString->id,
                        'language_code' => $languageCode,
                    ],
                    [
                        'value' => $definition['values'][$languageCode] ?? $definition['values']['en'] ?? $definition['key'],
                        'is_approved' => true,
                    ],
                );
            }
        }

        TranslationValue::flushTranslationCache();
    }

    /**
     * @return array<int, array{
     *     group: string,
     *     key: string,
     *     description?: string|null,
     *     is_system?: bool,
     *     values: array<string, string>
     * }>
     */
    private function keys(): array
    {
        return [
            $this->entry('common', 'common.actions.save', ['ru' => 'Сохранить', 'en' => 'Save', 'lt' => 'Išsaugoti', 'pl' => 'Zapisz']),
            $this->entry('common', 'common.actions.create', ['ru' => 'Создать', 'en' => 'Create', 'lt' => 'Sukurti', 'pl' => 'Utwórz']),
            $this->entry('common', 'common.actions.edit', ['ru' => 'Редактировать', 'en' => 'Edit', 'lt' => 'Redaguoti', 'pl' => 'Edytuj']),
            $this->entry('common', 'common.actions.delete', ['ru' => 'Удалить', 'en' => 'Delete', 'lt' => 'Ištrinti', 'pl' => 'Usuń']),
            $this->entry('common', 'common.actions.back', ['ru' => 'Назад', 'en' => 'Back', 'lt' => 'Atgal', 'pl' => 'Wstecz']),
            $this->entry('common', 'common.actions.export_csv', ['ru' => 'Экспорт CSV', 'en' => 'Export CSV', 'lt' => 'Eksportuoti CSV', 'pl' => 'Eksportuj CSV']),
            $this->entry('common', 'common.actions.export_json', ['ru' => 'Экспорт JSON', 'en' => 'Export JSON', 'lt' => 'Eksportuoti JSON', 'pl' => 'Eksportuj JSON']),
            $this->entry('common', 'common.actions.import', ['ru' => 'Импортировать', 'en' => 'Import', 'lt' => 'Importuoti', 'pl' => 'Importuj']),
            $this->entry('common', 'common.actions.search', ['ru' => 'Поиск', 'en' => 'Search', 'lt' => 'Paieska', 'pl' => 'Szukaj']),
            $this->entry('common', 'common.actions.activate', ['ru' => 'Активировать', 'en' => 'Activate', 'lt' => 'Aktyvuoti', 'pl' => 'Aktywuj']),
            $this->entry('common', 'common.actions.deactivate', ['ru' => 'Деактивировать', 'en' => 'Deactivate', 'lt' => 'Deaktyvuoti', 'pl' => 'Dezaktywuj']),
            $this->entry('common', 'common.actions.set_default', ['ru' => 'Сделать языком по умолчанию', 'en' => 'Set as default', 'lt' => 'Nustatyti numatytaja', 'pl' => 'Ustaw jako domyslny']),
            $this->entry('common', 'common.actions.bulk_create_missing', ['ru' => 'Создать отсутствующие значения', 'en' => 'Create missing values', 'lt' => 'Sukurti trukstamas reiksmes', 'pl' => 'Utworz brakujace wartosci']),
            $this->entry('common', 'common.status.yes', ['ru' => 'Да', 'en' => 'Yes', 'lt' => 'Taip', 'pl' => 'Tak']),
            $this->entry('common', 'common.status.no', ['ru' => 'Нет', 'en' => 'No', 'lt' => 'Ne', 'pl' => 'Nie']),
            $this->entry('common', 'common.status.active', ['ru' => 'Активен', 'en' => 'Active', 'lt' => 'Aktyvus', 'pl' => 'Aktywny']),
            $this->entry('common', 'common.status.inactive', ['ru' => 'Неактивен', 'en' => 'Inactive', 'lt' => 'Neaktyvus', 'pl' => 'Nieaktywny']),
            $this->entry('common', 'common.status.default', ['ru' => 'По умолчанию', 'en' => 'Default', 'lt' => 'Numatytasis', 'pl' => 'Domyslny']),
            $this->entry('common', 'common.empty.no_records', ['ru' => 'Записи не найдены.', 'en' => 'No records found.', 'lt' => 'Irasu nerasta.', 'pl' => 'Nie znaleziono rekordow.']),

            $this->entry('menu', 'menu.dashboard', ['ru' => 'Панель управления', 'en' => 'Dashboard', 'lt' => 'Valdymo skydas', 'pl' => 'Panel']),
            $this->entry('menu', 'menu.navigation', ['ru' => 'Навигация', 'en' => 'Navigation', 'lt' => 'Navigacija', 'pl' => 'Nawigacja']),
            $this->entry('menu', 'menu.content.home', ['ru' => 'Главная страница', 'en' => 'Homepage', 'lt' => 'Pradzios puslapis', 'pl' => 'Strona glowna']),
            $this->entry('menu', 'menu.operations', ['ru' => 'Операции', 'en' => 'Operations', 'lt' => 'Operacijos', 'pl' => 'Operacje']),
            $this->entry('menu', 'menu.operations.branches', ['ru' => 'Филиалы', 'en' => 'Branches', 'lt' => 'Filialai', 'pl' => 'Oddzialy']),
            $this->entry('menu', 'menu.operations.instructors', ['ru' => 'Инструкторы', 'en' => 'Instructors', 'lt' => 'Instruktoriai', 'pl' => 'Instruktorzy']),
            $this->entry('menu', 'menu.operations.groups', ['ru' => 'Группы', 'en' => 'Groups', 'lt' => 'Grupes', 'pl' => 'Grupy']),
            $this->entry('menu', 'menu.crm.students', ['ru' => 'CRM учеников', 'en' => 'Student CRM', 'lt' => 'Mokiniu CRM', 'pl' => 'CRM uczniow']),
            $this->entry('menu', 'menu.lms.programs', ['ru' => 'Учебные программы', 'en' => 'LMS Programs', 'lt' => 'Mokymo programos', 'pl' => 'Programy LMS']),
            $this->entry('menu', 'menu.schedule.lessons', ['ru' => 'Расписание', 'en' => 'Schedule', 'lt' => 'Tvarkarastis', 'pl' => 'Harmonogram']),
            $this->entry('menu', 'menu.fleet.vehicles', ['ru' => 'Автопарк', 'en' => 'Fleet', 'lt' => 'Transportas', 'pl' => 'Flota']),
            $this->entry('menu', 'menu.exams', ['ru' => 'Экзамены', 'en' => 'Exams', 'lt' => 'Egzaminai', 'pl' => 'Egzaminy']),
            $this->entry('menu', 'menu.finance.payments', ['ru' => 'Платежи', 'en' => 'Payments', 'lt' => 'Mokejimai', 'pl' => 'Platnosci']),
            $this->entry('menu', 'menu.documents', ['ru' => 'Документы', 'en' => 'Documents', 'lt' => 'Dokumentai', 'pl' => 'Dokumenty']),
            $this->entry('menu', 'menu.marketing', ['ru' => 'Маркетинг', 'en' => 'Marketing', 'lt' => 'Marketingas', 'pl' => 'Marketing']),
            $this->entry('menu', 'menu.marketing.campaigns', ['ru' => 'Кампании', 'en' => 'Campaigns', 'lt' => 'Kampanijos', 'pl' => 'Kampanie']),
            $this->entry('menu', 'menu.marketing.pipeline', ['ru' => 'Воронка продаж', 'en' => 'Pipeline', 'lt' => 'Pardavimu eiga', 'pl' => 'Lejek']),
            $this->entry('menu', 'menu.marketing.leads', ['ru' => 'Лиды', 'en' => 'Leads', 'lt' => 'Uzkalusos', 'pl' => 'Leady']),
            $this->entry('menu', 'menu.website.view', ['ru' => 'Открыть сайт', 'en' => 'View Website', 'lt' => 'Atidaryti svetaine', 'pl' => 'Zobacz strone']),
            $this->entry('menu', 'menu.settings', ['ru' => 'Настройки', 'en' => 'Settings', 'lt' => 'Nustatymai', 'pl' => 'Ustawienia']),
            $this->entry('menu', 'menu.settings.languages', ['ru' => 'Языки', 'en' => 'Languages', 'lt' => 'Kalbos', 'pl' => 'Jezyki']),
            $this->entry('menu', 'menu.settings.translations', ['ru' => 'Переводы', 'en' => 'Translations', 'lt' => 'Vertimai', 'pl' => 'Tlumaczenia']),
            $this->entry('menu', 'menu.access_controls', ['ru' => 'Управление доступом', 'en' => 'Access Controls', 'lt' => 'Prieigos valdymas', 'pl' => 'Kontrola dostepu']),
            $this->entry('menu', 'menu.system.users', ['ru' => 'Пользователи', 'en' => 'Users', 'lt' => 'Vartotojai', 'pl' => 'Uzytkownicy']),
            $this->entry('menu', 'menu.system.roles', ['ru' => 'Роли', 'en' => 'Roles', 'lt' => 'Roles', 'pl' => 'Role']),
            $this->entry('menu', 'menu.docs', ['ru' => 'Документация', 'en' => 'Documentation', 'lt' => 'Dokumentacija', 'pl' => 'Dokumentacja']),

            $this->entry('languages', 'languages.title', ['ru' => 'Языки', 'en' => 'Languages', 'lt' => 'Kalbos', 'pl' => 'Jezyki']),
            $this->entry('languages', 'languages.description', ['ru' => 'Управление языками интерфейса, сайта и кабинетов.', 'en' => 'Manage languages for the interface, website, and cabinets.', 'lt' => 'Tvarkykite sasajos, svetaines ir kabinetu kalbas.', 'pl' => 'Zarzadzaj jezykami interfejsu, strony i paneli.']),
            $this->entry('languages', 'languages.create_title', ['ru' => 'Создать язык', 'en' => 'Create language', 'lt' => 'Sukurti kalba', 'pl' => 'Utworz jezyk']),
            $this->entry('languages', 'languages.edit_title', ['ru' => 'Редактировать язык', 'en' => 'Edit language', 'lt' => 'Redaguoti kalba', 'pl' => 'Edytuj jezyk']),
            $this->entry('languages', 'languages.fields.code', ['ru' => 'Код', 'en' => 'Code', 'lt' => 'Kodas', 'pl' => 'Kod']),
            $this->entry('languages', 'languages.fields.name', ['ru' => 'Название', 'en' => 'Name', 'lt' => 'Pavadinimas', 'pl' => 'Nazwa']),
            $this->entry('languages', 'languages.fields.native_name', ['ru' => 'Родное название', 'en' => 'Native name', 'lt' => 'Gimtasis pavadinimas', 'pl' => 'Nazwa natywna']),
            $this->entry('languages', 'languages.fields.is_default', ['ru' => 'Язык по умолчанию', 'en' => 'Default language', 'lt' => 'Numatytoji kalba', 'pl' => 'Jezyk domyslny']),
            $this->entry('languages', 'languages.fields.is_active', ['ru' => 'Активен', 'en' => 'Active', 'lt' => 'Aktyvus', 'pl' => 'Aktywny']),
            $this->entry('languages', 'languages.fields.sort_order', ['ru' => 'Порядок сортировки', 'en' => 'Sort order', 'lt' => 'Rusiavimo tvarka', 'pl' => 'Kolejnosc sortowania']),
            $this->entry('languages', 'languages.messages.saved', ['ru' => 'Язык сохранён.', 'en' => 'Language saved.', 'lt' => 'Kalba issaugota.', 'pl' => 'Jezyk zapisany.']),
            $this->entry('languages', 'languages.messages.deleted', ['ru' => 'Язык удалён.', 'en' => 'Language deleted.', 'lt' => 'Kalba istrinta.', 'pl' => 'Jezyk usuniety.']),
            $this->entry('languages', 'languages.messages.default_set', ['ru' => 'Язык по умолчанию обновлён.', 'en' => 'Default language updated.', 'lt' => 'Numatytoji kalba atnaujinta.', 'pl' => 'Jezyk domyslny zaktualizowany.']),

            $this->entry('translations', 'translations.title', ['ru' => 'Переводы', 'en' => 'Translations', 'lt' => 'Vertimai', 'pl' => 'Tlumaczenia']),
            $this->entry('translations', 'translations.description', ['ru' => 'Управление ключами интерфейса и значениями для активных языков.', 'en' => 'Manage interface keys and values for active languages.', 'lt' => 'Tvarkykite sasajos raktus ir aktyviu kalbu reiksmes.', 'pl' => 'Zarzadzaj kluczami interfejsu i wartosciami aktywnych jezykow.']),
            $this->entry('translations', 'translations.create_title', ['ru' => 'Создать ключ перевода', 'en' => 'Create translation key', 'lt' => 'Sukurti vertimo rakta', 'pl' => 'Utworz klucz tlumaczenia']),
            $this->entry('translations', 'translations.edit_title', ['ru' => 'Редактировать перевод', 'en' => 'Edit translation', 'lt' => 'Redaguoti vertima', 'pl' => 'Edytuj tlumaczenie']),
            $this->entry('translations', 'translations.fields.group', ['ru' => 'Группа', 'en' => 'Group', 'lt' => 'Grupe', 'pl' => 'Grupa']),
            $this->entry('translations', 'translations.fields.key', ['ru' => 'Ключ', 'en' => 'Key', 'lt' => 'Raktas', 'pl' => 'Klucz']),
            $this->entry('translations', 'translations.fields.description', ['ru' => 'Описание', 'en' => 'Description', 'lt' => 'Aprasymas', 'pl' => 'Opis']),
            $this->entry('translations', 'translations.fields.is_system', ['ru' => 'Системный ключ', 'en' => 'System key', 'lt' => 'Sisteminis raktas', 'pl' => 'Klucz systemowy']),
            $this->entry('translations', 'translations.fields.value', ['ru' => 'Значение', 'en' => 'Value', 'lt' => 'Reiksme', 'pl' => 'Wartosc']),
            $this->entry('translations', 'translations.fields.is_approved', ['ru' => 'Одобрено', 'en' => 'Approved', 'lt' => 'Patvirtinta', 'pl' => 'Zatwierdzone']),
            $this->entry('translations', 'translations.fields.missing_count', ['ru' => 'Не хватает', 'en' => 'Missing', 'lt' => 'Truksta', 'pl' => 'Brakuje']),
            $this->entry('translations', 'translations.fields.import_file', ['ru' => 'Файл импорта', 'en' => 'Import file', 'lt' => 'Importo failas', 'pl' => 'Plik importu']),
            $this->entry('translations', 'translations.filters.group', ['ru' => 'Фильтр по группе', 'en' => 'Filter by group', 'lt' => 'Filtruoti pagal grupe', 'pl' => 'Filtruj wedlug grupy']),
            $this->entry('translations', 'translations.filters.search', ['ru' => 'Поиск по ключу или значению', 'en' => 'Search by key or value', 'lt' => 'Paieska pagal rakta arba reiksme', 'pl' => 'Szukaj po kluczu lub wartosci']),
            $this->entry('translations', 'translations.messages.saved', ['ru' => 'Перевод сохранён.', 'en' => 'Translation saved.', 'lt' => 'Vertimas issaugotas.', 'pl' => 'Tlumaczenie zapisane.']),
            $this->entry('translations', 'translations.messages.deleted', ['ru' => 'Ключ перевода удалён.', 'en' => 'Translation key deleted.', 'lt' => 'Vertimo raktas istrintas.', 'pl' => 'Klucz tlumaczenia usuniety.']),
            $this->entry('translations', 'translations.messages.missing_created', ['ru' => 'Отсутствующие значения созданы.', 'en' => 'Missing values created.', 'lt' => 'Trukstamos reiksmes sukurtos.', 'pl' => 'Brakujace wartosci utworzone.']),
            $this->entry('translations', 'translations.messages.imported', ['ru' => 'Переводы импортированы.', 'en' => 'Translations imported.', 'lt' => 'Vertimai importuoti.', 'pl' => 'Tlumaczenia zaimportowane.']),
            $this->entry('translations', 'translations.messages.import_failed', ['ru' => 'Не удалось импортировать файл.', 'en' => 'Could not import the file.', 'lt' => 'Nepavyko importuoti failo.', 'pl' => 'Nie mozna zaimportowac pliku.']),

            $this->entry('permissions', 'permissions.groups.system', ['ru' => 'Система', 'en' => 'System', 'lt' => 'Sistema', 'pl' => 'System']),
            $this->entry('permissions', 'permissions.groups.content', ['ru' => 'Контент', 'en' => 'Content', 'lt' => 'Turinys', 'pl' => 'Tresc']),
            $this->entry('permissions', 'permissions.groups.operations', ['ru' => 'Операции', 'en' => 'Operations', 'lt' => 'Operacijos', 'pl' => 'Operacje']),
            $this->entry('permissions', 'permissions.groups.marketing', ['ru' => 'Маркетинг', 'en' => 'Marketing', 'lt' => 'Marketingas', 'pl' => 'Marketing']),
            $this->entry('permissions', 'permissions.content.home', ['ru' => 'Главная страница', 'en' => 'Homepage', 'lt' => 'Pradzios puslapis', 'pl' => 'Strona glowna']),
            $this->entry('permissions', 'permissions.lms.programs', ['ru' => 'Учебные программы', 'en' => 'LMS programs', 'lt' => 'Mokymo programos', 'pl' => 'Programy LMS']),
            $this->entry('permissions', 'permissions.documents', ['ru' => 'Документы', 'en' => 'Documents', 'lt' => 'Dokumentai', 'pl' => 'Dokumenty']),
            $this->entry('permissions', 'permissions.operations.branches', ['ru' => 'Филиалы', 'en' => 'Branches', 'lt' => 'Filialai', 'pl' => 'Oddzialy']),
            $this->entry('permissions', 'permissions.operations.instructors', ['ru' => 'Инструкторы', 'en' => 'Instructors', 'lt' => 'Instruktoriai', 'pl' => 'Instruktorzy']),
            $this->entry('permissions', 'permissions.operations.groups', ['ru' => 'Учебные группы', 'en' => 'Training groups', 'lt' => 'Mokymo grupes', 'pl' => 'Grupy szkoleniowe']),
            $this->entry('permissions', 'permissions.crm.students', ['ru' => 'CRM учеников', 'en' => 'Student CRM', 'lt' => 'Mokiniu CRM', 'pl' => 'CRM uczniow']),
            $this->entry('permissions', 'permissions.schedule.lessons', ['ru' => 'Расписание', 'en' => 'Schedule', 'lt' => 'Tvarkarastis', 'pl' => 'Harmonogram']),
            $this->entry('permissions', 'permissions.fleet.vehicles', ['ru' => 'Автопарк', 'en' => 'Fleet', 'lt' => 'Transportas', 'pl' => 'Flota']),
            $this->entry('permissions', 'permissions.exams', ['ru' => 'Экзамены', 'en' => 'Exams', 'lt' => 'Egzaminai', 'pl' => 'Egzaminy']),
            $this->entry('permissions', 'permissions.finance.payments', ['ru' => 'Платежи', 'en' => 'Payments', 'lt' => 'Mokejimai', 'pl' => 'Platnosci']),
            $this->entry('permissions', 'permissions.marketing.campaigns', ['ru' => 'Кампании', 'en' => 'Campaigns', 'lt' => 'Kampanijos', 'pl' => 'Kampanie']),
            $this->entry('permissions', 'permissions.marketing.pipeline', ['ru' => 'Воронка продаж', 'en' => 'Sales pipeline', 'lt' => 'Pardavimu eiga', 'pl' => 'Lejek sprzedazy']),
            $this->entry('permissions', 'permissions.marketing.leads', ['ru' => 'Лиды', 'en' => 'Leads', 'lt' => 'Uzkalusos', 'pl' => 'Leady']),
            $this->entry('permissions', 'permissions.system.roles', ['ru' => 'Роли', 'en' => 'Roles', 'lt' => 'Roles', 'pl' => 'Role']),
            $this->entry('permissions', 'permissions.system.users', ['ru' => 'Пользователи', 'en' => 'Users', 'lt' => 'Vartotojai', 'pl' => 'Uzytkownicy']),
            $this->entry('permissions', 'permissions.system.languages.view', ['ru' => 'Просмотр языков', 'en' => 'View languages', 'lt' => 'Perziureti kalbas', 'pl' => 'Podglad jezykow']),
            $this->entry('permissions', 'permissions.system.languages.create', ['ru' => 'Создание языков', 'en' => 'Create languages', 'lt' => 'Kurti kalbas', 'pl' => 'Tworzenie jezykow']),
            $this->entry('permissions', 'permissions.system.languages.update', ['ru' => 'Редактирование языков', 'en' => 'Update languages', 'lt' => 'Atnaujinti kalbas', 'pl' => 'Aktualizacja jezykow']),
            $this->entry('permissions', 'permissions.system.languages.delete', ['ru' => 'Удаление языков', 'en' => 'Delete languages', 'lt' => 'Trinti kalbas', 'pl' => 'Usuwanie jezykow']),
            $this->entry('permissions', 'permissions.system.translations.view', ['ru' => 'Просмотр переводов', 'en' => 'View translations', 'lt' => 'Perziureti vertimus', 'pl' => 'Podglad tlumaczen']),
            $this->entry('permissions', 'permissions.system.translations.update', ['ru' => 'Редактирование переводов', 'en' => 'Update translations', 'lt' => 'Atnaujinti vertimus', 'pl' => 'Aktualizacja tlumaczen']),
            $this->entry('permissions', 'permissions.system.translations.export', ['ru' => 'Экспорт переводов', 'en' => 'Export translations', 'lt' => 'Eksportuoti vertimus', 'pl' => 'Eksport tlumaczen']),
            $this->entry('permissions', 'permissions.system.translations.import', ['ru' => 'Импорт переводов', 'en' => 'Import translations', 'lt' => 'Importuoti vertimus', 'pl' => 'Import tlumaczen']),

            $this->entry('crm', 'crm.leads.title', ['ru' => 'Лиды', 'en' => 'Leads', 'lt' => 'Uzkalusos', 'pl' => 'Leady']),
            $this->entry('crm', 'crm.leads.fields.full_name', ['ru' => 'Полное имя', 'en' => 'Full name', 'lt' => 'Vardas ir pavarde', 'pl' => 'Imie i nazwisko']),
            $this->entry('crm', 'crm.leads.fields.phone', ['ru' => 'Телефон', 'en' => 'Phone', 'lt' => 'Telefonas', 'pl' => 'Telefon']),
            $this->entry('crm', 'crm.leads.fields.email', ['ru' => 'Email', 'en' => 'Email', 'lt' => 'El. pastas', 'pl' => 'Email']),
            $this->entry('crm', 'crm.leads.fields.status', ['ru' => 'Статус', 'en' => 'Status', 'lt' => 'Busena', 'pl' => 'Status']),
            $this->entry('crm', 'crm.leads.fields.source', ['ru' => 'Источник', 'en' => 'Source', 'lt' => 'Saltinis', 'pl' => 'Zrodlo']),
            $this->entry('crm', 'crm.leads.actions.save', ['ru' => 'Сохранить лид', 'en' => 'Save lead', 'lt' => 'Issaugoti uzklausa', 'pl' => 'Zapisz lead']),

            ...$this->statusEntries(),
            ...$this->sourceEntries(),
            ...$this->websiteEntries(),
        ];
    }

    /**
     * @param  array<string, string>  $values
     * @return array{group: string, key: string, values: array<string, string>, description: null, is_system: bool}
     */
    private function entry(string $group, string $key, array $values): array
    {
        return [
            'group' => $group,
            'key' => $key,
            'description' => null,
            'is_system' => true,
            'values' => $values,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function statusEntries(): array
    {
        return [
            $this->entry('statuses', 'crm.leads.statuses.new', ['ru' => 'Новая заявка', 'en' => 'New lead', 'lt' => 'Nauja uzklausa', 'pl' => 'Nowy lead']),
            $this->entry('statuses', 'crm.leads.statuses.no_answer', ['ru' => 'Не дозвонились', 'en' => 'No answer', 'lt' => 'Neatsiliepe', 'pl' => 'Brak odpowiedzi']),
            $this->entry('statuses', 'crm.leads.statuses.contacted', ['ru' => 'Связались', 'en' => 'Contacted', 'lt' => 'Susisiekta', 'pl' => 'Skontaktowano']),
            $this->entry('statuses', 'crm.leads.statuses.consultation_done', ['ru' => 'Консультация проведена', 'en' => 'Consultation done', 'lt' => 'Konsultacija atlikta', 'pl' => 'Konsultacja wykonana']),
            $this->entry('statuses', 'crm.leads.statuses.waiting_documents', ['ru' => 'Ждёт документы', 'en' => 'Waiting for documents', 'lt' => 'Laukiama dokumentu', 'pl' => 'Oczekuje na dokumenty']),
            $this->entry('statuses', 'crm.leads.statuses.waiting_payment', ['ru' => 'Ждёт оплату', 'en' => 'Waiting for payment', 'lt' => 'Laukiama apmokejimo', 'pl' => 'Oczekuje na platnosc']),
            $this->entry('statuses', 'crm.leads.statuses.assigned_to_group', ['ru' => 'Записан в группу', 'en' => 'Assigned to group', 'lt' => 'Priskirta grupei', 'pl' => 'Przypisany do grupy']),
            $this->entry('statuses', 'crm.leads.statuses.became_student', ['ru' => 'Стал учеником', 'en' => 'Became student', 'lt' => 'Tapo mokiniu', 'pl' => 'Zostal uczniem']),
            $this->entry('statuses', 'crm.leads.statuses.rejected', ['ru' => 'Отказ', 'en' => 'Rejected', 'lt' => 'Atmesta', 'pl' => 'Odrzucony']),
            $this->entry('statuses', 'crm.leads.statuses.duplicate', ['ru' => 'Дубль', 'en' => 'Duplicate', 'lt' => 'Dublikatas', 'pl' => 'Duplikat']),
            $this->entry('statuses', 'crm.leads.statuses.spam', ['ru' => 'Спам', 'en' => 'Spam', 'lt' => 'Slamstas', 'pl' => 'Spam']),
            $this->entry('statuses', 'crm.leads.statuses.archived', ['ru' => 'Архив', 'en' => 'Archived', 'lt' => 'Archyvas', 'pl' => 'Archiwum']),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function sourceEntries(): array
    {
        return [
            $this->entry('sources', 'crm.sources.website', ['ru' => 'Сайт', 'en' => 'Website', 'lt' => 'Svetaine', 'pl' => 'Strona']),
            $this->entry('sources', 'crm.sources.phone', ['ru' => 'Телефон', 'en' => 'Phone', 'lt' => 'Telefonas', 'pl' => 'Telefon']),
            $this->entry('sources', 'crm.sources.telegram', ['ru' => 'Telegram', 'en' => 'Telegram', 'lt' => 'Telegram', 'pl' => 'Telegram']),
            $this->entry('sources', 'crm.sources.whatsapp', ['ru' => 'WhatsApp', 'en' => 'WhatsApp', 'lt' => 'WhatsApp', 'pl' => 'WhatsApp']),
            $this->entry('sources', 'crm.sources.google_ads', ['ru' => 'Google Ads', 'en' => 'Google Ads', 'lt' => 'Google Ads', 'pl' => 'Google Ads']),
            $this->entry('sources', 'crm.sources.referral', ['ru' => 'Рекомендация', 'en' => 'Referral', 'lt' => 'Rekomendacija', 'pl' => 'Polecenie']),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function websiteEntries(): array
    {
        return [
            $this->entry('website', 'website.nav.home', ['ru' => 'Главная', 'en' => 'Home', 'lt' => 'Pradzia', 'pl' => 'Strona glowna']),
            $this->entry('website', 'website.nav.programs', ['ru' => 'Программы', 'en' => 'Programs', 'lt' => 'Programos', 'pl' => 'Programy']),
            $this->entry('website', 'website.nav.instructors', ['ru' => 'Инструкторы', 'en' => 'Instructors', 'lt' => 'Instruktoriai', 'pl' => 'Instruktorzy']),
            $this->entry('website', 'website.nav.fleet', ['ru' => 'Автопарк', 'en' => 'Fleet', 'lt' => 'Transportas', 'pl' => 'Flota']),
            $this->entry('website', 'website.nav.contacts', ['ru' => 'Контакты', 'en' => 'Contacts', 'lt' => 'Kontaktai', 'pl' => 'Kontakty']),
            $this->entry('website', 'website.actions.apply', ['ru' => 'Записаться', 'en' => 'Apply', 'lt' => 'Registruotis', 'pl' => 'Zapisz sie']),
            $this->entry('website', 'website.seo.default_title', ['ru' => 'Автошкола DrivePro Academy', 'en' => 'DrivePro Academy driving school', 'lt' => 'DrivePro Academy vairavimo mokykla', 'pl' => 'Szkola jazdy DrivePro Academy']),
            $this->entry('website', 'website.seo.default_description', ['ru' => 'Программы обучения, инструкторы, группы и запись в автошколу.', 'en' => 'Training programs, instructors, groups, and driving school enrollment.', 'lt' => 'Mokymo programos, instruktoriai, grupes ir registracija.', 'pl' => 'Programy szkolen, instruktorzy, grupy i zapisy.']),
        ];
    }
}
