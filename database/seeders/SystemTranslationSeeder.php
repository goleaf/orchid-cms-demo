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
            $this->entry('common', 'common.actions.reset', ['ru' => 'Сбросить', 'en' => 'Reset', 'lt' => 'Atstatyti', 'pl' => 'Resetuj']),
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
            $this->entry('common', 'common.system', ['ru' => 'Система', 'en' => 'System', 'lt' => 'Sistema', 'pl' => 'System']),

            $this->entry('menu', 'menu.dashboard', ['ru' => 'Панель управления', 'en' => 'Dashboard', 'lt' => 'Valdymo skydas', 'pl' => 'Panel']),
            $this->entry('menu', 'menu.navigation', ['ru' => 'Навигация', 'en' => 'Navigation', 'lt' => 'Navigacija', 'pl' => 'Nawigacja']),
            $this->entry('menu', 'menu.content.home', ['ru' => 'Главная страница', 'en' => 'Homepage', 'lt' => 'Pradzios puslapis', 'pl' => 'Strona glowna']),
            $this->entry('menu', 'menu.operations', ['ru' => 'Операции', 'en' => 'Operations', 'lt' => 'Operacijos', 'pl' => 'Operacje']),
            $this->entry('menu', 'menu.operations.branches', ['ru' => 'Филиалы', 'en' => 'Branches', 'lt' => 'Filialai', 'pl' => 'Oddzialy']),
            $this->entry('menu', 'menu.operations.instructors', ['ru' => 'Инструкторы', 'en' => 'Instructors', 'lt' => 'Instruktoriai', 'pl' => 'Instruktorzy']),
            $this->entry('menu', 'menu.operations.groups', ['ru' => 'Группы', 'en' => 'Groups', 'lt' => 'Grupes', 'pl' => 'Grupy']),
            $this->entry('menu', 'menu.crm.students', ['ru' => 'CRM учеников', 'en' => 'Student CRM', 'lt' => 'Mokiniu CRM', 'pl' => 'CRM uczniow']),
            $this->entry('menu', 'menu.crm', ['ru' => 'CRM', 'en' => 'CRM', 'lt' => 'CRM', 'pl' => 'CRM']),
            $this->entry('menu', 'menu.crm.leads', ['ru' => 'Лиды', 'en' => 'Leads', 'lt' => 'Uzklausos', 'pl' => 'Leady']),
            $this->entry('menu', 'menu.crm.new_leads', ['ru' => 'Новые заявки', 'en' => 'New leads', 'lt' => 'Naujos uzklausos', 'pl' => 'Nowe leady']),
            $this->entry('menu', 'menu.crm.overdue_tasks', ['ru' => 'Просроченные задачи', 'en' => 'Overdue tasks', 'lt' => 'Veluojancios uzduotys', 'pl' => 'Zalegle zadania']),
            $this->entry('menu', 'menu.crm.pipeline', ['ru' => 'Воронка', 'en' => 'Pipeline', 'lt' => 'Pardavimu eiga', 'pl' => 'Lejek']),
            $this->entry('menu', 'menu.crm.statuses', ['ru' => 'Статусы', 'en' => 'Statuses', 'lt' => 'Busenos', 'pl' => 'Statusy']),
            $this->entry('menu', 'menu.crm.sources', ['ru' => 'Источники', 'en' => 'Sources', 'lt' => 'Saltiniai', 'pl' => 'Zrodla']),
            $this->entry('menu', 'menu.crm.lost_reasons', ['ru' => 'Причины отказа', 'en' => 'Lost reasons', 'lt' => 'Praradimo priezastys', 'pl' => 'Powody utraty']),
            $this->entry('menu', 'menu.crm.tags', ['ru' => 'Теги', 'en' => 'Tags', 'lt' => 'Zymos', 'pl' => 'Tagi']),
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
            $this->entry('menu', 'menu.marketing.templates', ['ru' => 'Шаблоны сообщений', 'en' => 'Message templates', 'lt' => 'Zinuciu sablonai', 'pl' => 'Szablony wiadomosci']),
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

            $this->entry('locale', 'locale.switcher.label', ['ru' => 'Язык', 'en' => 'Language', 'lt' => 'Kalba', 'pl' => 'Jezyk']),
            $this->entry('locale', 'locale.switcher.submit', ['ru' => 'Сменить', 'en' => 'Switch', 'lt' => 'Keisti', 'pl' => 'Zmien']),
            $this->entry('locale', 'locale.fields.preferred_locale', ['ru' => 'Язык интерфейса', 'en' => 'Interface language', 'lt' => 'Sasajos kalba', 'pl' => 'Jezyk interfejsu']),
            $this->entry('locale', 'locale.profile_title', ['ru' => 'Язык интерфейса', 'en' => 'Interface language', 'lt' => 'Sasajos kalba', 'pl' => 'Jezyk interfejsu']),
            $this->entry('locale', 'locale.profile_description', ['ru' => 'Выберите язык для сайта и административной панели.', 'en' => 'Choose the language for the website and admin panel.', 'lt' => 'Pasirinkite svetaines ir administravimo skydelio kalba.', 'pl' => 'Wybierz jezyk strony i panelu administracyjnego.']),
            $this->entry('locale', 'locale.messages.saved', ['ru' => 'Язык интерфейса обновлён.', 'en' => 'Interface language updated.', 'lt' => 'Sasajos kalba atnaujinta.', 'pl' => 'Jezyk interfejsu zaktualizowany.']),
            $this->entry('locale', 'locale.messages.unavailable', ['ru' => 'Этот язык недоступен.', 'en' => 'This language is not available.', 'lt' => 'Si kalba nepasiekiama.', 'pl' => 'Ten jezyk jest niedostepny.']),

            $this->entry('translatable', 'translatable.badges.default', ['ru' => 'По умолчанию', 'en' => 'Default', 'lt' => 'Numatytoji', 'pl' => 'Domyslny']),
            $this->entry('translatable', 'translatable.actions.copy_default_value', ['ru' => 'Скопировать из языка по умолчанию', 'en' => 'Copy from default language', 'lt' => 'Kopijuoti is numatytosios kalbos', 'pl' => 'Kopiuj z jezyka domyslnego']),
            $this->entry('translatable', 'translatable.help.copy_default_on_save', ['ru' => 'При сохранении значение языка по умолчанию будет записано в этот язык.', 'en' => 'When saved, the default language value will be copied into this language.', 'lt' => 'Issaugojus numatytosios kalbos reiksme bus nukopijuota i sia kalba.', 'pl' => 'Przy zapisie wartosc z jezyka domyslnego zostanie skopiowana do tego jezyka.']),
            $this->entry('translatable', 'translatable.warnings.default_missing', ['ru' => 'Заполните значение языка по умолчанию.', 'en' => 'Fill the default language value.', 'lt' => 'Uzpildykite numatytosios kalbos reiksme.', 'pl' => 'Uzupelnij wartosc jezyka domyslnego.']),
            $this->entry('translatable', 'translatable.warnings.missing_translation', ['ru' => 'Перевод отсутствует; будет использован язык по умолчанию.', 'en' => 'Translation is missing; the default language will be used.', 'lt' => 'Vertimo nera; bus naudojama numatytoji kalba.', 'pl' => 'Brakuje tlumaczenia; zostanie uzyty jezyk domyslny.']),
            $this->entry('translatable', 'translatable.seo.title', ['ru' => 'SEO переводы', 'en' => 'SEO translations', 'lt' => 'SEO vertimai', 'pl' => 'Tlumaczenia SEO']),
            $this->entry('translatable', 'translatable.seo.fields.seo_title', ['ru' => 'SEO заголовок', 'en' => 'SEO title', 'lt' => 'SEO pavadinimas', 'pl' => 'Tytul SEO']),
            $this->entry('translatable', 'translatable.seo.fields.seo_description', ['ru' => 'SEO описание', 'en' => 'SEO description', 'lt' => 'SEO aprasymas', 'pl' => 'Opis SEO']),
            $this->entry('translatable', 'translatable.seo.fields.seo_keywords', ['ru' => 'SEO ключевые слова', 'en' => 'SEO keywords', 'lt' => 'SEO raktazodziai', 'pl' => 'Slowa kluczowe SEO']),
            $this->entry('translatable', 'translatable.seo.fields.og_title', ['ru' => 'Open Graph заголовок', 'en' => 'Open Graph title', 'lt' => 'Open Graph pavadinimas', 'pl' => 'Tytul Open Graph']),
            $this->entry('translatable', 'translatable.seo.fields.og_description', ['ru' => 'Open Graph описание', 'en' => 'Open Graph description', 'lt' => 'Open Graph aprasymas', 'pl' => 'Opis Open Graph']),

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
            $this->entry('permissions', 'permissions.groups.crm', ['ru' => 'CRM', 'en' => 'CRM', 'lt' => 'CRM', 'pl' => 'CRM']),
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
            $this->entry('permissions', 'permissions.marketing.templates', ['ru' => 'Шаблоны сообщений', 'en' => 'Message templates', 'lt' => 'Zinuciu sablonai', 'pl' => 'Szablony wiadomosci']),
            $this->entry('permissions', 'permissions.crm.leads.view', ['ru' => 'Просмотр лидов', 'en' => 'View leads', 'lt' => 'Perziureti uzklausas', 'pl' => 'Podglad leadow']),
            $this->entry('permissions', 'permissions.crm.leads.create', ['ru' => 'Создание лидов', 'en' => 'Create leads', 'lt' => 'Kurti uzklausas', 'pl' => 'Tworzenie leadow']),
            $this->entry('permissions', 'permissions.crm.leads.update', ['ru' => 'Редактирование лидов', 'en' => 'Update leads', 'lt' => 'Atnaujinti uzklausas', 'pl' => 'Aktualizacja leadow']),
            $this->entry('permissions', 'permissions.crm.leads.delete', ['ru' => 'Удаление лидов', 'en' => 'Delete leads', 'lt' => 'Trinti uzklausas', 'pl' => 'Usuwanie leadow']),
            $this->entry('permissions', 'permissions.crm.leads.assign', ['ru' => 'Назначение ответственных', 'en' => 'Assign leads', 'lt' => 'Priskirti uzklausas', 'pl' => 'Przypisywanie leadow']),
            $this->entry('permissions', 'permissions.crm.leads.change_status', ['ru' => 'Изменение статуса лидов', 'en' => 'Change lead status', 'lt' => 'Keisti uzklausos busena', 'pl' => 'Zmiana statusu leada']),
            $this->entry('permissions', 'permissions.crm.leads.manage_dictionaries', ['ru' => 'Управление CRM словарями', 'en' => 'Manage CRM dictionaries', 'lt' => 'Tvarkyti CRM zodynus', 'pl' => 'Zarzadzanie slownikami CRM']),
            $this->entry('permissions', 'permissions.crm.leads.view_marketing', ['ru' => 'Просмотр маркетинговых данных', 'en' => 'View marketing data', 'lt' => 'Perziureti marketingo duomenis', 'pl' => 'Podglad danych marketingowych']),
            $this->entry('permissions', 'permissions.crm.leads.convert', ['ru' => 'Конвертация лидов', 'en' => 'Convert leads', 'lt' => 'Konvertuoti uzklausas', 'pl' => 'Konwersja leadow']),
            $this->entry('permissions', 'permissions.crm.leads.export', ['ru' => 'Экспорт лидов', 'en' => 'Export leads', 'lt' => 'Eksportuoti uzklausas', 'pl' => 'Eksport leadow']),
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

            $this->entry('profile', 'profile.title', ['ru' => 'Мой аккаунт', 'en' => 'My Account', 'lt' => 'Mano paskyra', 'pl' => 'Moje konto']),
            $this->entry('profile', 'profile.description', ['ru' => 'Обновите данные аккаунта, email, пароль и язык интерфейса.', 'en' => 'Update your account details, email, password, and interface language.', 'lt' => 'Atnaujinkite paskyros duomenis, el. pasta, slaptazodi ir sasajos kalba.', 'pl' => 'Zaktualizuj dane konta, email, haslo i jezyk interfejsu.']),
            $this->entry('profile', 'profile.actions.back_to_account', ['ru' => 'Вернуться в аккаунт', 'en' => 'Back to my account', 'lt' => 'Grizti i paskyra', 'pl' => 'Wroc do konta']),
            $this->entry('profile', 'profile.actions.sign_out', ['ru' => 'Выйти', 'en' => 'Sign out', 'lt' => 'Atsijungti', 'pl' => 'Wyloguj']),
            $this->entry('profile', 'profile.actions.update_password', ['ru' => 'Обновить пароль', 'en' => 'Update password', 'lt' => 'Atnaujinti slaptazodi', 'pl' => 'Zaktualizuj haslo']),
            $this->entry('profile', 'profile.blocks.information.title', ['ru' => 'Данные профиля', 'en' => 'Profile Information', 'lt' => 'Profilio informacija', 'pl' => 'Informacje profilu']),
            $this->entry('profile', 'profile.blocks.information.description', ['ru' => 'Обновите имя и email аккаунта.', 'en' => 'Update your account profile information and email address.', 'lt' => 'Atnaujinkite paskyros profilio informacija ir el. pasta.', 'pl' => 'Zaktualizuj informacje profilu i adres email.']),
            $this->entry('profile', 'profile.blocks.password.title', ['ru' => 'Обновить пароль', 'en' => 'Update Password', 'lt' => 'Atnaujinti slaptazodi', 'pl' => 'Zaktualizuj haslo']),
            $this->entry('profile', 'profile.blocks.password.description', ['ru' => 'Используйте длинный случайный пароль для защиты аккаунта.', 'en' => 'Ensure your account is using a long, random password to stay secure.', 'lt' => 'Naudokite ilga atsitiktini slaptazodi paskyros saugumui.', 'pl' => 'Uzywaj dlugiego losowego hasla, aby konto bylo bezpieczne.']),
            $this->entry('profile', 'profile.messages.updated', ['ru' => 'Профиль обновлён.', 'en' => 'Profile updated.', 'lt' => 'Profilis atnaujintas.', 'pl' => 'Profil zaktualizowany.']),
            $this->entry('profile', 'profile.messages.password_changed', ['ru' => 'Пароль изменён.', 'en' => 'Password changed.', 'lt' => 'Slaptazodis pakeistas.', 'pl' => 'Haslo zmienione.']),

            $this->entry('crm', 'crm.leads.title', ['ru' => 'Лиды', 'en' => 'Leads', 'lt' => 'Uzkalusos', 'pl' => 'Leady']),
            $this->entry('crm', 'crm.leads.fields.full_name', ['ru' => 'Полное имя', 'en' => 'Full name', 'lt' => 'Vardas ir pavarde', 'pl' => 'Imie i nazwisko']),
            $this->entry('crm', 'crm.leads.fields.phone', ['ru' => 'Телефон', 'en' => 'Phone', 'lt' => 'Telefonas', 'pl' => 'Telefon']),
            $this->entry('crm', 'crm.leads.fields.email', ['ru' => 'Email', 'en' => 'Email', 'lt' => 'El. pastas', 'pl' => 'Email']),
            $this->entry('crm', 'crm.leads.fields.status', ['ru' => 'Статус', 'en' => 'Status', 'lt' => 'Busena', 'pl' => 'Status']),
            $this->entry('crm', 'crm.leads.fields.source', ['ru' => 'Источник', 'en' => 'Source', 'lt' => 'Saltinis', 'pl' => 'Zrodlo']),
            $this->entry('crm', 'crm.leads.actions.save', ['ru' => 'Сохранить лид', 'en' => 'Save lead', 'lt' => 'Issaugoti uzklausa', 'pl' => 'Zapisz lead']),
            $this->entry('crm', 'crm.message_templates.title', ['ru' => 'Шаблоны сообщений', 'en' => 'Message templates', 'lt' => 'Zinuciu sablonai', 'pl' => 'Szablony wiadomosci']),
            $this->entry('crm', 'crm.message_templates.description', ['ru' => 'Готовые тексты для звонков, SMS, email и мессенджеров в карточке лида.', 'en' => 'Reusable copy for calls, SMS, email, and messenger lead follow-ups.', 'lt' => 'Pakartotiniai tekstai skambuciams, SMS, el. pastui ir zinutems.', 'pl' => 'Gotowe tresci do rozmow, SMS, emaili i komunikatorow.']),
            $this->entry('crm', 'crm.message_templates.create_title', ['ru' => 'Создать шаблон сообщения', 'en' => 'Create message template', 'lt' => 'Sukurti zinutes sablona', 'pl' => 'Utworz szablon wiadomosci']),
            $this->entry('crm', 'crm.message_templates.edit_title', ['ru' => 'Редактировать шаблон сообщения', 'en' => 'Edit message template', 'lt' => 'Redaguoti zinutes sablona', 'pl' => 'Edytuj szablon wiadomosci']),
            $this->entry('crm', 'crm.message_templates.fields.name', ['ru' => 'Название', 'en' => 'Name', 'lt' => 'Pavadinimas', 'pl' => 'Nazwa']),
            $this->entry('crm', 'crm.message_templates.fields.channel', ['ru' => 'Канал', 'en' => 'Channel', 'lt' => 'Kanalas', 'pl' => 'Kanal']),
            $this->entry('crm', 'crm.message_templates.fields.subject', ['ru' => 'Тема', 'en' => 'Subject', 'lt' => 'Tema', 'pl' => 'Temat']),
            $this->entry('crm', 'crm.message_templates.fields.body', ['ru' => 'Текст', 'en' => 'Body', 'lt' => 'Tekstas', 'pl' => 'Tresc']),
            $this->entry('crm', 'crm.message_templates.fields.is_active', ['ru' => 'Активен', 'en' => 'Active', 'lt' => 'Aktyvus', 'pl' => 'Aktywny']),
            $this->entry('crm', 'crm.message_templates.fields.sort_order', ['ru' => 'Порядок сортировки', 'en' => 'Sort order', 'lt' => 'Rusiavimo tvarka', 'pl' => 'Kolejnosc sortowania']),
            $this->entry('crm', 'crm.message_templates.filters.channel', ['ru' => 'Фильтр по каналу', 'en' => 'Filter by channel', 'lt' => 'Filtruoti pagal kanala', 'pl' => 'Filtruj wedlug kanalu']),
            $this->entry('crm', 'crm.message_templates.filters.status', ['ru' => 'Фильтр по статусу', 'en' => 'Filter by status', 'lt' => 'Filtruoti pagal busena', 'pl' => 'Filtruj wedlug statusu']),
            $this->entry('crm', 'crm.message_templates.filters.all_channels', ['ru' => 'Все каналы', 'en' => 'All channels', 'lt' => 'Visi kanalai', 'pl' => 'Wszystkie kanaly']),
            $this->entry('crm', 'crm.message_templates.filters.all_statuses', ['ru' => 'Все статусы', 'en' => 'All statuses', 'lt' => 'Visos busenos', 'pl' => 'Wszystkie statusy']),
            $this->entry('crm', 'crm.message_templates.messages.saved', ['ru' => 'Шаблон сообщения сохранён.', 'en' => 'Message template saved.', 'lt' => 'Zinutes sablonas issaugotas.', 'pl' => 'Szablon wiadomosci zapisany.']),
            $this->entry('crm', 'crm.message_templates.messages.deleted', ['ru' => 'Шаблон сообщения удалён.', 'en' => 'Message template deleted.', 'lt' => 'Zinutes sablonas istrintas.', 'pl' => 'Szablon wiadomosci usuniety.']),
            $this->entry('crm', 'crm.message_templates.messages.delete_confirm', ['ru' => 'Удалить этот шаблон сообщения?', 'en' => 'Delete this message template?', 'lt' => 'Istrinti si zinutes sablona?', 'pl' => 'Usunac ten szablon wiadomosci?']),
            $this->entry('crm', 'crm.communication.channels.any', ['ru' => 'Любой канал', 'en' => 'Any channel', 'lt' => 'Bet kuris kanalas', 'pl' => 'Dowolny kanal']),
            $this->entry('crm', 'crm.communication.channels.phone', ['ru' => 'Телефонный звонок', 'en' => 'Phone call', 'lt' => 'Telefono skambutis', 'pl' => 'Rozmowa telefoniczna']),
            $this->entry('crm', 'crm.communication.channels.sms', ['ru' => 'SMS', 'en' => 'SMS', 'lt' => 'SMS', 'pl' => 'SMS']),
            $this->entry('crm', 'crm.communication.channels.email', ['ru' => 'Email', 'en' => 'Email', 'lt' => 'El. pastas', 'pl' => 'Email']),
            $this->entry('crm', 'crm.communication.channels.whatsapp', ['ru' => 'WhatsApp', 'en' => 'WhatsApp', 'lt' => 'WhatsApp', 'pl' => 'WhatsApp']),
            $this->entry('crm', 'crm.communication.channels.telegram', ['ru' => 'Telegram', 'en' => 'Telegram', 'lt' => 'Telegram', 'pl' => 'Telegram']),
            $this->entry('crm', 'crm.communication.channels.viber', ['ru' => 'Viber', 'en' => 'Viber', 'lt' => 'Viber', 'pl' => 'Viber']),
            $this->entry('crm', 'crm.communication.channels.web_form', ['ru' => 'Веб-форма', 'en' => 'Web form', 'lt' => 'Web forma', 'pl' => 'Formularz www']),

            ...$this->crmLeadEntries(),
            ...$this->crmPipelineEntries(),
            ...$this->crmCommunicationEntries(),
            ...$this->crmTaskEntries(),
            ...$this->crmDictionaryEntries(),
            ...$this->crmActivityEntries(),
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
    private function crmLeadEntries(): array
    {
        return [
            $this->entry('crm', 'crm.leads.description', ['ru' => 'Заявки с сайта и рекламных кампаний для работы менеджеров.', 'en' => 'Website and campaign inquiries for manager follow-up.', 'lt' => 'Svetaines ir kampaniju uzklausos vadybininkams.', 'pl' => 'Zapytania ze strony i kampanii dla menedzerow.']),
            $this->entry('crm', 'crm.leads.edit_title', ['ru' => 'CRM лид: :name', 'en' => 'CRM lead: :name', 'lt' => 'CRM uzklausa: :name', 'pl' => 'Lead CRM: :name']),
            $this->entry('crm', 'crm.leads.edit_description', ['ru' => 'Карточка продаж с коммуникациями, задачами, историей и документами.', 'en' => 'Sales card with communications, tasks, history, and documents.', 'lt' => 'Pardavimu kortele su komunikacija, uzduotimis, istorija ir dokumentais.', 'pl' => 'Karta sprzedazy z komunikacja, zadaniami, historia i dokumentami.']),
            $this->entry('crm', 'crm.leads.fallback.lead', ['ru' => 'Лид', 'en' => 'Lead', 'lt' => 'Uzklausa', 'pl' => 'Lead']),

            $this->entry('crm', 'crm.leads.columns.id', ['ru' => 'ID', 'en' => 'ID', 'lt' => 'ID', 'pl' => 'ID']),
            $this->entry('crm', 'crm.leads.columns.full_name', ['ru' => 'Полное имя', 'en' => 'Full name', 'lt' => 'Vardas ir pavarde', 'pl' => 'Imie i nazwisko']),
            $this->entry('crm', 'crm.leads.columns.phone', ['ru' => 'Телефон', 'en' => 'Phone', 'lt' => 'Telefonas', 'pl' => 'Telefon']),
            $this->entry('crm', 'crm.leads.columns.email', ['ru' => 'Email', 'en' => 'Email', 'lt' => 'El. pastas', 'pl' => 'Email']),
            $this->entry('crm', 'crm.leads.columns.messenger', ['ru' => 'Мессенджер', 'en' => 'Messenger', 'lt' => 'Zinutes', 'pl' => 'Komunikator']),
            $this->entry('crm', 'crm.leads.columns.city', ['ru' => 'Город', 'en' => 'City', 'lt' => 'Miestas', 'pl' => 'Miasto']),
            $this->entry('crm', 'crm.leads.columns.campaign', ['ru' => 'Кампания', 'en' => 'Campaign', 'lt' => 'Kampanija', 'pl' => 'Kampania']),
            $this->entry('crm', 'crm.leads.columns.branch', ['ru' => 'Филиал', 'en' => 'Branch', 'lt' => 'Filialas', 'pl' => 'Oddzial']),
            $this->entry('crm', 'crm.leads.columns.course', ['ru' => 'Курс', 'en' => 'Course', 'lt' => 'Kursas', 'pl' => 'Kurs']),
            $this->entry('crm', 'crm.leads.columns.status', ['ru' => 'Статус', 'en' => 'Status', 'lt' => 'Busena', 'pl' => 'Status']),
            $this->entry('crm', 'crm.leads.columns.source', ['ru' => 'Источник', 'en' => 'Source', 'lt' => 'Saltinis', 'pl' => 'Zrodlo']),
            $this->entry('crm', 'crm.leads.columns.category', ['ru' => 'Категория', 'en' => 'Category', 'lt' => 'Kategorija', 'pl' => 'Kategoria']),
            $this->entry('crm', 'crm.leads.columns.manager', ['ru' => 'Менеджер', 'en' => 'Manager', 'lt' => 'Vadybininkas', 'pl' => 'Menedzer']),
            $this->entry('crm', 'crm.leads.columns.next_follow_up', ['ru' => 'Следующий контакт', 'en' => 'Next follow-up', 'lt' => 'Kitas kontaktas', 'pl' => 'Nastepny kontakt']),
            $this->entry('crm', 'crm.leads.columns.created_at', ['ru' => 'Создано', 'en' => 'Created', 'lt' => 'Sukurta', 'pl' => 'Utworzono']),
            $this->entry('crm', 'crm.leads.columns.budget', ['ru' => 'Бюджет', 'en' => 'Budget', 'lt' => 'Biudzetas', 'pl' => 'Budzet']),
            $this->entry('crm', 'crm.leads.columns.activity', ['ru' => 'Активность', 'en' => 'Activity', 'lt' => 'Aktyvumas', 'pl' => 'Aktywnosc']),
            $this->entry('crm', 'crm.leads.columns.converted', ['ru' => 'Конвертирован', 'en' => 'Converted', 'lt' => 'Konvertuota', 'pl' => 'Konwertowany']),
            $this->entry('crm', 'crm.leads.columns.actions', ['ru' => 'Действия', 'en' => 'Actions', 'lt' => 'Veiksmai', 'pl' => 'Akcje']),
            $this->entry('crm', 'crm.leads.columns.user', ['ru' => 'Пользователь', 'en' => 'User', 'lt' => 'Vartotojas', 'pl' => 'Uzytkownik']),
            $this->entry('crm', 'crm.leads.activity.summary', ['ru' => ':communications сообщ. / :comments замет. / :documents док.', 'en' => ':communications comms / :comments notes / :documents docs', 'lt' => ':communications zin. / :comments past. / :documents dok.', 'pl' => ':communications kom. / :comments not. / :documents dok.']),

            $this->entry('crm', 'crm.leads.filters.search', ['ru' => 'Поиск по имени, телефону или email', 'en' => 'Search by name, phone, or email', 'lt' => 'Paieska pagal varda, telefona arba email', 'pl' => 'Szukaj po imieniu, telefonie lub emailu']),
            $this->entry('crm', 'crm.leads.filters.status', ['ru' => 'Фильтр по статусу', 'en' => 'Filter by status', 'lt' => 'Filtruoti pagal busena', 'pl' => 'Filtruj wedlug statusu']),
            $this->entry('crm', 'crm.leads.filters.source', ['ru' => 'Фильтр по источнику', 'en' => 'Filter by source', 'lt' => 'Filtruoti pagal saltini', 'pl' => 'Filtruj wedlug zrodla']),
            $this->entry('crm', 'crm.leads.filters.manager', ['ru' => 'Фильтр по менеджеру', 'en' => 'Filter by manager', 'lt' => 'Filtruoti pagal vadybininka', 'pl' => 'Filtruj wedlug menedzera']),
            $this->entry('crm', 'crm.leads.filters.overdue', ['ru' => 'Просрочка', 'en' => 'Overdue', 'lt' => 'Velavimas', 'pl' => 'Zalegle']),
            $this->entry('crm', 'crm.leads.filters.all_statuses', ['ru' => 'Все статусы', 'en' => 'All statuses', 'lt' => 'Visos busenos', 'pl' => 'Wszystkie statusy']),
            $this->entry('crm', 'crm.leads.filters.all_sources', ['ru' => 'Все источники', 'en' => 'All sources', 'lt' => 'Visi saltiniai', 'pl' => 'Wszystkie zrodla']),
            $this->entry('crm', 'crm.leads.filters.all_managers', ['ru' => 'Все менеджеры', 'en' => 'All managers', 'lt' => 'Visi vadybininkai', 'pl' => 'Wszyscy menedzerowie']),
            $this->entry('crm', 'crm.leads.filters.all_tasks', ['ru' => 'Все задачи', 'en' => 'All tasks', 'lt' => 'Visos uzduotys', 'pl' => 'Wszystkie zadania']),
            $this->entry('crm', 'crm.leads.filters.only_overdue', ['ru' => 'Только просроченные', 'en' => 'Only overdue', 'lt' => 'Tik veluojancios', 'pl' => 'Tylko zalegle']),
            $this->entry('crm', 'crm.leads.filters.all_categories', ['ru' => 'Все категории', 'en' => 'All categories', 'lt' => 'Visos kategorijos', 'pl' => 'Wszystkie kategorie']),
            $this->entry('crm', 'crm.leads.filters.all_branches', ['ru' => 'Все филиалы', 'en' => 'All branches', 'lt' => 'Visi filialai', 'pl' => 'Wszystkie oddzialy']),
            $this->entry('crm', 'crm.leads.filters.flags', ['ru' => 'Флаги', 'en' => 'Flags', 'lt' => 'Pozymiai', 'pl' => 'Flagi']),
            $this->entry('crm', 'crm.leads.flags.hot', ['ru' => 'Горячий', 'en' => 'Hot', 'lt' => 'Karstas', 'pl' => 'Goracy']),
            $this->entry('crm', 'crm.leads.flags.overdue', ['ru' => 'Просрочен', 'en' => 'Overdue', 'lt' => 'Veluoja', 'pl' => 'Zalegly']),

            $this->entry('crm', 'crm.leads.actions.open', ['ru' => 'Открыть', 'en' => 'Open', 'lt' => 'Atidaryti', 'pl' => 'Otworz']),
            $this->entry('crm', 'crm.leads.actions.open_pipeline', ['ru' => 'Открыть воронку', 'en' => 'Open pipeline', 'lt' => 'Atidaryti eiga', 'pl' => 'Otworz lejek']),
            $this->entry('crm', 'crm.leads.actions.change_status', ['ru' => 'Изменить статус', 'en' => 'Change status', 'lt' => 'Keisti busena', 'pl' => 'Zmien status']),
            $this->entry('crm', 'crm.leads.actions.assign_manager', ['ru' => 'Назначить менеджера', 'en' => 'Assign manager', 'lt' => 'Priskirti vadybininka', 'pl' => 'Przypisz menedzera']),
            $this->entry('crm', 'crm.leads.actions.add_note', ['ru' => 'Добавить заметку', 'en' => 'Add note', 'lt' => 'Prideti pastaba', 'pl' => 'Dodaj notatke']),
            $this->entry('crm', 'crm.leads.actions.log_call', ['ru' => 'Добавить коммуникацию', 'en' => 'Add communication', 'lt' => 'Prideti komunikacija', 'pl' => 'Dodaj komunikacje']),
            $this->entry('crm', 'crm.leads.actions.mark_duplicate', ['ru' => 'Отметить дублем', 'en' => 'Mark duplicate', 'lt' => 'Pazymeti dublikata', 'pl' => 'Oznacz duplikat']),
            $this->entry('crm', 'crm.leads.actions.mark_spam', ['ru' => 'Отметить спамом', 'en' => 'Mark spam', 'lt' => 'Pazymeti kaip slamsta', 'pl' => 'Oznacz spam']),
            $this->entry('crm', 'crm.leads.actions.back_to_leads', ['ru' => 'Назад к лидам', 'en' => 'Back to leads', 'lt' => 'Atgal i uzklausas', 'pl' => 'Powrot do leadow']),
            $this->entry('crm', 'crm.leads.actions.save_crm_card', ['ru' => 'Сохранить карточку CRM', 'en' => 'Save CRM card', 'lt' => 'Issaugoti CRM kortele', 'pl' => 'Zapisz karte CRM']),

            $this->entry('crm', 'crm.leads.messages.updated', ['ru' => 'Карточка CRM обновлена.', 'en' => 'CRM card updated.', 'lt' => 'CRM kortele atnaujinta.', 'pl' => 'Karta CRM zaktualizowana.']),
            $this->entry('crm', 'crm.leads.messages.comment_added', ['ru' => 'Заметка добавлена.', 'en' => 'Note added.', 'lt' => 'Pastaba prideta.', 'pl' => 'Notatka dodana.']),
            $this->entry('crm', 'crm.leads.messages.communication_added', ['ru' => 'Коммуникация добавлена.', 'en' => 'Communication added.', 'lt' => 'Komunikacija prideta.', 'pl' => 'Komunikacja dodana.']),

            ...$this->crmLeadSectionEntries(),
            ...$this->crmLeadFieldEntries(),
            ...$this->crmLeadEmptyEntries(),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function crmLeadSectionEntries(): array
    {
        return [
            $this->entry('crm', 'crm.leads.sections.main_information', ['ru' => 'Основная информация', 'en' => 'Main information', 'lt' => 'Pagrindine informacija', 'pl' => 'Glowne informacje']),
            $this->entry('crm', 'crm.leads.sections.crm_information', ['ru' => 'CRM информация', 'en' => 'CRM information', 'lt' => 'CRM informacija', 'pl' => 'Informacje CRM']),
            $this->entry('crm', 'crm.leads.sections.training_interest', ['ru' => 'Интерес к обучению', 'en' => 'Training interest', 'lt' => 'Mokymo interesas', 'pl' => 'Zainteresowanie kursem']),
            $this->entry('crm', 'crm.leads.sections.marketing_data', ['ru' => 'Маркетинговые данные', 'en' => 'Marketing data', 'lt' => 'Marketingo duomenys', 'pl' => 'Dane marketingowe']),
            $this->entry('crm', 'crm.leads.sections.consent_data', ['ru' => 'Согласия', 'en' => 'Consent data', 'lt' => 'Sutikimo duomenys', 'pl' => 'Dane zgod']),
            $this->entry('crm', 'crm.leads.sections.system_data', ['ru' => 'Системные данные', 'en' => 'System data', 'lt' => 'Sistemos duomenys', 'pl' => 'Dane systemowe']),
            $this->entry('crm', 'crm.leads.sections.activity_timeline', ['ru' => 'Лента активности', 'en' => 'Activity timeline', 'lt' => 'Veiklos istorija', 'pl' => 'Historia aktywnosci']),
            $this->entry('crm', 'crm.leads.sections.latest_comments', ['ru' => 'Последние заметки', 'en' => 'Latest notes', 'lt' => 'Naujausios pastabos', 'pl' => 'Ostatnie notatki']),
            $this->entry('crm', 'crm.leads.sections.tasks', ['ru' => 'Задачи', 'en' => 'Tasks', 'lt' => 'Uzduotys', 'pl' => 'Zadania']),
            $this->entry('crm', 'crm.leads.sections.duplicates', ['ru' => 'Дубли', 'en' => 'Duplicates', 'lt' => 'Dublikatai', 'pl' => 'Duplikaty']),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function crmLeadFieldEntries(): array
    {
        return [
            $this->entry('crm', 'crm.leads.fields.first_name', ['ru' => 'Имя', 'en' => 'First name', 'lt' => 'Vardas', 'pl' => 'Imie']),
            $this->entry('crm', 'crm.leads.fields.last_name', ['ru' => 'Фамилия', 'en' => 'Last name', 'lt' => 'Pavarde', 'pl' => 'Nazwisko']),
            $this->entry('crm', 'crm.leads.fields.preferred_messenger', ['ru' => 'Предпочитаемый мессенджер', 'en' => 'Preferred messenger', 'lt' => 'Pageidaujamas kanalas', 'pl' => 'Preferowany komunikator']),
            $this->entry('crm', 'crm.leads.fields.city', ['ru' => 'Город', 'en' => 'City', 'lt' => 'Miestas', 'pl' => 'Miasto']),
            $this->entry('crm', 'crm.leads.fields.priority', ['ru' => 'Приоритет', 'en' => 'Priority', 'lt' => 'Prioritetas', 'pl' => 'Priorytet']),
            $this->entry('crm', 'crm.leads.fields.lead_score', ['ru' => 'Оценка лида', 'en' => 'Lead score', 'lt' => 'Uzklausos balas', 'pl' => 'Ocena leada']),
            $this->entry('crm', 'crm.leads.fields.manager', ['ru' => 'Менеджер', 'en' => 'Manager', 'lt' => 'Vadybininkas', 'pl' => 'Menedzer']),
            $this->entry('crm', 'crm.leads.fields.lost_reason', ['ru' => 'Причина отказа', 'en' => 'Lost reason', 'lt' => 'Praradimo priezastis', 'pl' => 'Powod utraty']),
            $this->entry('crm', 'crm.leads.fields.next_follow_up_at', ['ru' => 'Следующий контакт', 'en' => 'Next follow-up', 'lt' => 'Kitas kontaktas', 'pl' => 'Nastepny kontakt']),
            $this->entry('crm', 'crm.leads.fields.last_contacted_at', ['ru' => 'Последний контакт', 'en' => 'Last contacted', 'lt' => 'Paskutinis kontaktas', 'pl' => 'Ostatni kontakt']),
            $this->entry('crm', 'crm.leads.fields.internal_comment', ['ru' => 'Внутренний комментарий', 'en' => 'Internal comment', 'lt' => 'Vidinis komentaras', 'pl' => 'Komentarz wewnetrzny']),
            $this->entry('crm', 'crm.leads.fields.comment', ['ru' => 'Комментарий', 'en' => 'Comment', 'lt' => 'Komentaras', 'pl' => 'Komentarz']),
            $this->entry('crm', 'crm.leads.fields.branch', ['ru' => 'Филиал', 'en' => 'Branch', 'lt' => 'Filialas', 'pl' => 'Oddzial']),
            $this->entry('crm', 'crm.leads.fields.course_category', ['ru' => 'Категория прав', 'en' => 'Course category', 'lt' => 'Kurso kategorija', 'pl' => 'Kategoria kursu']),
            $this->entry('crm', 'crm.leads.fields.course', ['ru' => 'Курс', 'en' => 'Course', 'lt' => 'Kursas', 'pl' => 'Kurs']),
            $this->entry('crm', 'crm.leads.fields.training_group', ['ru' => 'Учебная группа', 'en' => 'Training group', 'lt' => 'Mokymo grupe', 'pl' => 'Grupa szkoleniowa']),
            $this->entry('crm', 'crm.leads.fields.instructor', ['ru' => 'Инструктор', 'en' => 'Instructor', 'lt' => 'Instruktorius', 'pl' => 'Instruktor']),
            $this->entry('crm', 'crm.leads.fields.desired_start_date', ['ru' => 'Желаемая дата начала', 'en' => 'Desired start date', 'lt' => 'Pageidaujama pradzios data', 'pl' => 'Pozadana data startu']),
            $this->entry('crm', 'crm.leads.fields.preferred_format', ['ru' => 'Формат обучения', 'en' => 'Preferred format', 'lt' => 'Pageidaujamas formatas', 'pl' => 'Preferowany format']),
            $this->entry('crm', 'crm.leads.fields.preferred_time', ['ru' => 'Предпочитаемое время', 'en' => 'Preferred time', 'lt' => 'Pageidaujamas laikas', 'pl' => 'Preferowany czas']),
            $this->entry('crm', 'crm.leads.fields.budget', ['ru' => 'Бюджет', 'en' => 'Budget', 'lt' => 'Biudzetas', 'pl' => 'Budzet']),
            $this->entry('crm', 'crm.leads.fields.utm_source', ['ru' => 'UTM source', 'en' => 'UTM source', 'lt' => 'UTM source', 'pl' => 'UTM source']),
            $this->entry('crm', 'crm.leads.fields.utm_medium', ['ru' => 'UTM medium', 'en' => 'UTM medium', 'lt' => 'UTM medium', 'pl' => 'UTM medium']),
            $this->entry('crm', 'crm.leads.fields.utm_campaign', ['ru' => 'UTM campaign', 'en' => 'UTM campaign', 'lt' => 'UTM campaign', 'pl' => 'UTM campaign']),
            $this->entry('crm', 'crm.leads.fields.utm_content', ['ru' => 'UTM content', 'en' => 'UTM content', 'lt' => 'UTM content', 'pl' => 'UTM content']),
            $this->entry('crm', 'crm.leads.fields.utm_term', ['ru' => 'UTM term', 'en' => 'UTM term', 'lt' => 'UTM term', 'pl' => 'UTM term']),
            $this->entry('crm', 'crm.leads.fields.referrer', ['ru' => 'Реферер', 'en' => 'Referrer', 'lt' => 'Nukreipejas', 'pl' => 'Referrer']),
            $this->entry('crm', 'crm.leads.fields.landing_page', ['ru' => 'Лендинг', 'en' => 'Landing page', 'lt' => 'Nukreipimo puslapis', 'pl' => 'Landing page']),
            $this->entry('crm', 'crm.leads.fields.form_page', ['ru' => 'Страница формы', 'en' => 'Form page', 'lt' => 'Formos puslapis', 'pl' => 'Strona formularza']),
            $this->entry('crm', 'crm.leads.fields.form_name', ['ru' => 'Название формы', 'en' => 'Form name', 'lt' => 'Formos pavadinimas', 'pl' => 'Nazwa formularza']),
            $this->entry('crm', 'crm.leads.fields.locale', ['ru' => 'Язык', 'en' => 'Locale', 'lt' => 'Kalba', 'pl' => 'Jezyk']),
            $this->entry('crm', 'crm.leads.fields.ip_address', ['ru' => 'IP адрес', 'en' => 'IP address', 'lt' => 'IP adresas', 'pl' => 'Adres IP']),
            $this->entry('crm', 'crm.leads.fields.user_agent', ['ru' => 'User agent', 'en' => 'User agent', 'lt' => 'User agent', 'pl' => 'User agent']),
            $this->entry('crm', 'crm.leads.fields.consent_accepted', ['ru' => 'Согласие принято', 'en' => 'Consent accepted', 'lt' => 'Sutikimas priimtas', 'pl' => 'Zgoda zaakceptowana']),
            $this->entry('crm', 'crm.leads.fields.consent_accepted_at', ['ru' => 'Дата согласия', 'en' => 'Consent accepted at', 'lt' => 'Sutikimo data', 'pl' => 'Data zgody']),
            $this->entry('crm', 'crm.leads.fields.uuid', ['ru' => 'UUID', 'en' => 'UUID', 'lt' => 'UUID', 'pl' => 'UUID']),
            $this->entry('crm', 'crm.leads.fields.created_at', ['ru' => 'Создано', 'en' => 'Created at', 'lt' => 'Sukurta', 'pl' => 'Utworzono']),
            $this->entry('crm', 'crm.leads.fields.updated_at', ['ru' => 'Обновлено', 'en' => 'Updated at', 'lt' => 'Atnaujinta', 'pl' => 'Zaktualizowano']),
            $this->entry('crm', 'crm.leads.fields.closed_at', ['ru' => 'Закрыто', 'en' => 'Closed at', 'lt' => 'Uzvertas', 'pl' => 'Zamknieto']),
            $this->entry('crm', 'crm.leads.fields.converted_at', ['ru' => 'Конвертировано', 'en' => 'Converted at', 'lt' => 'Konvertuota', 'pl' => 'Skonwertowano']),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function crmLeadEmptyEntries(): array
    {
        return [
            $this->entry('crm', 'crm.leads.empty.no_leads', ['ru' => 'Лиды не найдены.', 'en' => 'No leads found.', 'lt' => 'Uzklausu nerasta.', 'pl' => 'Nie znaleziono leadow.']),
            $this->entry('crm', 'crm.leads.empty.no_manager', ['ru' => 'Без менеджера', 'en' => 'No manager', 'lt' => 'Be vadybininko', 'pl' => 'Bez menedzera']),
            $this->entry('crm', 'crm.leads.empty.no_branch', ['ru' => 'Без филиала', 'en' => 'No branch', 'lt' => 'Be filialo', 'pl' => 'Bez oddzialu']),
            $this->entry('crm', 'crm.leads.empty.no_course', ['ru' => 'Без курса', 'en' => 'No course', 'lt' => 'Be kurso', 'pl' => 'Bez kursu']),
            $this->entry('crm', 'crm.leads.empty.no_group', ['ru' => 'Без группы', 'en' => 'No group', 'lt' => 'Be grupes', 'pl' => 'Bez grupy']),
            $this->entry('crm', 'crm.leads.empty.no_instructor', ['ru' => 'Без инструктора', 'en' => 'No instructor', 'lt' => 'Be instruktoriaus', 'pl' => 'Bez instruktora']),
            $this->entry('crm', 'crm.leads.empty.no_lost_reason', ['ru' => 'Причина не выбрана', 'en' => 'No lost reason', 'lt' => 'Priezastis nepasirinkta', 'pl' => 'Brak powodu utraty']),
            $this->entry('crm', 'crm.leads.empty.no_contact', ['ru' => 'Нет контакта', 'en' => 'No contact', 'lt' => 'Nera kontakto', 'pl' => 'Brak kontaktu']),
            $this->entry('crm', 'crm.leads.empty.no_follow_up', ['ru' => 'Нет следующего контакта', 'en' => 'No follow-up', 'lt' => 'Nera kito kontakto', 'pl' => 'Brak kolejnego kontaktu']),
            $this->entry('crm', 'crm.leads.empty.no_managers_found', ['ru' => 'Менеджеры не найдены', 'en' => 'No managers found', 'lt' => 'Vadybininku nerasta', 'pl' => 'Nie znaleziono menedzerow']),
            $this->entry('crm', 'crm.leads.empty.no_sources_found', ['ru' => 'Источники не найдены', 'en' => 'No sources found', 'lt' => 'Saltiniu nerasta', 'pl' => 'Nie znaleziono zrodel']),
            $this->entry('crm', 'crm.leads.empty.no_categories_found', ['ru' => 'Категории не найдены', 'en' => 'No categories found', 'lt' => 'Kategoriju nerasta', 'pl' => 'Nie znaleziono kategorii']),
            $this->entry('crm', 'crm.leads.empty.no_branches_found', ['ru' => 'Филиалы не найдены', 'en' => 'No branches found', 'lt' => 'Filialu nerasta', 'pl' => 'Nie znaleziono oddzialow']),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function crmPipelineEntries(): array
    {
        return [
            $this->entry('crm', 'crm.pipeline.title', ['ru' => 'Воронка продаж', 'en' => 'Sales pipeline', 'lt' => 'Pardavimu eiga', 'pl' => 'Lejek sprzedazy']),
            $this->entry('crm', 'crm.pipeline.description', ['ru' => 'Канбан по лидам, фильтрам, задачам, статусам и конверсии.', 'en' => 'Kanban funnel for leads, filters, tasks, statuses, and conversion.', 'lt' => 'Kanban eiga uzklausoms, filtrams, uzduotims, busenoms ir konversijai.', 'pl' => 'Kanban dla leadow, filtrow, zadan, statusow i konwersji.']),
            $this->entry('crm', 'crm.pipeline.messages.lead_moved', ['ru' => 'Лид перемещён в статус :status.', 'en' => 'Lead moved to :status.', 'lt' => 'Uzklausa perkelta i busena :status.', 'pl' => 'Lead przeniesiony do statusu :status.']),
            $this->entry('crm', 'crm.pipeline.report.leads_in_view', ['ru' => 'Лидов в выборке', 'en' => 'Leads in view', 'lt' => 'Matomos uzklausos', 'pl' => 'Leady w widoku']),
            $this->entry('crm', 'crm.pipeline.report.became_students', ['ru' => 'Стали учениками', 'en' => 'Became students', 'lt' => 'Tapo mokiniais', 'pl' => 'Zostali uczniami']),
            $this->entry('crm', 'crm.pipeline.report.conversion', ['ru' => 'Конверсия', 'en' => 'Conversion', 'lt' => 'Konversija', 'pl' => 'Konwersja']),
            $this->entry('crm', 'crm.pipeline.report.loss_rate', ['ru' => 'Доля отказов', 'en' => 'Loss rate', 'lt' => 'Praradimu dalis', 'pl' => 'Udzial utrat']),
            $this->entry('crm', 'crm.pipeline.report.status_conversion', ['ru' => 'Отчёт по статусам', 'en' => 'Status conversion report', 'lt' => 'Busenu konversijos ataskaita', 'pl' => 'Raport konwersji statusow']),
            $this->entry('crm', 'crm.pipeline.report.loss_reasons', ['ru' => 'Причины отказа', 'en' => 'Loss reasons', 'lt' => 'Praradimo priezastys', 'pl' => 'Powody utraty']),
            $this->entry('crm', 'crm.pipeline.empty.no_statuses_in_filter', ['ru' => 'В текущем фильтре нет статусов.', 'en' => 'No statuses in current filter.', 'lt' => 'Dabartiniame filtre nera busenu.', 'pl' => 'Brak statusow w biezacym filtrze.']),
            $this->entry('crm', 'crm.pipeline.empty.no_loss_reasons', ['ru' => 'Нет причин отказа в текущем фильтре.', 'en' => 'No rejected lead reasons in current filter.', 'lt' => 'Dabartiniame filtre nera praradimo priezasciu.', 'pl' => 'Brak powodow utraty w biezacym filtrze.']),
            $this->entry('crm', 'crm.pipeline.empty.no_statuses', ['ru' => 'Нет статусов', 'en' => 'No statuses', 'lt' => 'Nera busenu', 'pl' => 'Brak statusow']),
            $this->entry('crm', 'crm.pipeline.empty.no_pipeline_statuses', ['ru' => 'Статусы воронки не настроены', 'en' => 'No pipeline statuses configured', 'lt' => 'Eigos busenos nesukonfiguruotos', 'pl' => 'Statusy lejka nie sa skonfigurowane']),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function crmCommunicationEntries(): array
    {
        return [
            $this->entry('crm', 'crm.communications.title', ['ru' => 'История коммуникаций', 'en' => 'Communication history', 'lt' => 'Komunikacijos istorija', 'pl' => 'Historia komunikacji']),
            $this->entry('crm', 'crm.communications.recent_title', ['ru' => 'Последние коммуникации', 'en' => 'Recent communications', 'lt' => 'Naujausia komunikacija', 'pl' => 'Ostatnia komunikacja']),
            $this->entry('crm', 'crm.communications.fields.channel', ['ru' => 'Канал', 'en' => 'Channel', 'lt' => 'Kanalas', 'pl' => 'Kanal']),
            $this->entry('crm', 'crm.communications.fields.message_template', ['ru' => 'Шаблон сообщения', 'en' => 'Message template', 'lt' => 'Zinutes sablonas', 'pl' => 'Szablon wiadomosci']),
            $this->entry('crm', 'crm.communications.fields.direction', ['ru' => 'Направление', 'en' => 'Direction', 'lt' => 'Kryptis', 'pl' => 'Kierunek']),
            $this->entry('crm', 'crm.communications.fields.subject', ['ru' => 'Тема', 'en' => 'Subject', 'lt' => 'Tema', 'pl' => 'Temat']),
            $this->entry('crm', 'crm.communications.fields.body', ['ru' => 'Текст', 'en' => 'Body', 'lt' => 'Tekstas', 'pl' => 'Tresc']),
            $this->entry('crm', 'crm.communications.fields.client_replied', ['ru' => 'Клиент ответил', 'en' => 'Client replied', 'lt' => 'Klientas atsake', 'pl' => 'Klient odpowiedzial']),
            $this->entry('crm', 'crm.communications.fields.callback_required', ['ru' => 'Нужен обратный звонок', 'en' => 'Need callback', 'lt' => 'Reikia perskambinti', 'pl' => 'Potrzebny callback']),
            $this->entry('crm', 'crm.communications.fields.callback_required_at', ['ru' => 'Время звонка', 'en' => 'Callback time', 'lt' => 'Perskambinimo laikas', 'pl' => 'Czas callbacku']),
            $this->entry('crm', 'crm.communications.fields.call_recording_url', ['ru' => 'URL записи звонка', 'en' => 'Call recording URL', 'lt' => 'Skambucio iraso URL', 'pl' => 'URL nagrania rozmowy']),
            $this->entry('crm', 'crm.communications.fields.call_recording_reference', ['ru' => 'ID записи телефонии', 'en' => 'Telephony recording ID', 'lt' => 'Telefonijos iraso ID', 'pl' => 'ID nagrania telefonii']),
            $this->entry('crm', 'crm.communications.fields.flags', ['ru' => 'Флаги', 'en' => 'Flags', 'lt' => 'Pozymiai', 'pl' => 'Flagi']),
            $this->entry('crm', 'crm.communications.fields.recording', ['ru' => 'Запись', 'en' => 'Recording', 'lt' => 'Irasas', 'pl' => 'Nagranie']),
            $this->entry('crm', 'crm.communications.empty.select_channel', ['ru' => 'Выберите канал', 'en' => 'Select channel', 'lt' => 'Pasirinkite kanala', 'pl' => 'Wybierz kanal']),
            $this->entry('crm', 'crm.communications.empty.no_template', ['ru' => 'Без шаблона', 'en' => 'No template', 'lt' => 'Be sablono', 'pl' => 'Bez szablonu']),
            $this->entry('crm', 'crm.communications.empty.select_direction', ['ru' => 'Выберите направление', 'en' => 'Select direction', 'lt' => 'Pasirinkite krypti', 'pl' => 'Wybierz kierunek']),
            $this->entry('crm', 'crm.communications.directions.inbound', ['ru' => 'Входящее', 'en' => 'Inbound', 'lt' => 'Ieinantis', 'pl' => 'Przychodzace']),
            $this->entry('crm', 'crm.communications.directions.outbound', ['ru' => 'Исходящее', 'en' => 'Outbound', 'lt' => 'Iseinantis', 'pl' => 'Wychodzace']),
            $this->entry('crm', 'crm.communications.flags.client_replied', ['ru' => 'Клиент ответил', 'en' => 'Client replied', 'lt' => 'Klientas atsake', 'pl' => 'Klient odpowiedzial']),
            $this->entry('crm', 'crm.communications.flags.callback_at', ['ru' => 'Звонок :time', 'en' => 'Callback :time', 'lt' => 'Perskambinti :time', 'pl' => 'Callback :time']),
            $this->entry('crm', 'crm.communications.system_subjects.online_enrollment_request', ['ru' => 'Онлайн-заявка на обучение', 'en' => 'Online enrollment request', 'lt' => 'Internetine registracijos uzklausa', 'pl' => 'Zgloszenie online na kurs']),
            $this->entry('crm', 'crm.communications.system_subjects.consultation_call', ['ru' => 'Консультационный звонок', 'en' => 'Consultation call', 'lt' => 'Konsultacinis skambutis', 'pl' => 'Rozmowa konsultacyjna']),
            $this->entry('crm', 'crm.communications.system_bodies.consultation_call', ['ru' => 'Клиент ответил, запросил документы и практику по выходным.', 'en' => 'Client answered, asked for documents and weekend practice slots.', 'lt' => 'Klientas atsake, paprase dokumentu ir savaitgalio praktikos laiku.', 'pl' => 'Klient odpowiedzial, zapytal o dokumenty i weekendowe jazdy.']),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function crmTaskEntries(): array
    {
        return [
            $this->entry('crm', 'crm.tasks.title', ['ru' => 'Задачи', 'en' => 'Tasks', 'lt' => 'Uzduotys', 'pl' => 'Zadania']),
            $this->entry('crm', 'crm.tasks.fields.title', ['ru' => 'Задача', 'en' => 'Task', 'lt' => 'Uzduotis', 'pl' => 'Zadanie']),
            $this->entry('crm', 'crm.tasks.fields.description', ['ru' => 'Описание', 'en' => 'Description', 'lt' => 'Aprasymas', 'pl' => 'Opis']),
            $this->entry('crm', 'crm.tasks.fields.assigned_to', ['ru' => 'Ответственный', 'en' => 'Assigned to', 'lt' => 'Priskirta', 'pl' => 'Przypisane do']),
            $this->entry('crm', 'crm.tasks.fields.priority', ['ru' => 'Приоритет', 'en' => 'Priority', 'lt' => 'Prioritetas', 'pl' => 'Priorytet']),
            $this->entry('crm', 'crm.tasks.fields.status', ['ru' => 'Статус', 'en' => 'Status', 'lt' => 'Busena', 'pl' => 'Status']),
            $this->entry('crm', 'crm.tasks.fields.due_at', ['ru' => 'Срок', 'en' => 'Due at', 'lt' => 'Terminas', 'pl' => 'Termin']),
            $this->entry('crm', 'crm.tasks.fields.completed_at', ['ru' => 'Завершено', 'en' => 'Completed at', 'lt' => 'Baigta', 'pl' => 'Ukonczono']),
            $this->entry('crm', 'crm.tasks.statuses.open', ['ru' => 'Открыта', 'en' => 'Open', 'lt' => 'Atvira', 'pl' => 'Otwarta']),
            $this->entry('crm', 'crm.tasks.statuses.in_progress', ['ru' => 'В работе', 'en' => 'In progress', 'lt' => 'Vykdoma', 'pl' => 'W toku']),
            $this->entry('crm', 'crm.tasks.statuses.done', ['ru' => 'Выполнена', 'en' => 'Done', 'lt' => 'Atlikta', 'pl' => 'Gotowe']),
            $this->entry('crm', 'crm.tasks.statuses.cancelled', ['ru' => 'Отменена', 'en' => 'Cancelled', 'lt' => 'Atsaukta', 'pl' => 'Anulowana']),
            $this->entry('crm', 'crm.tasks.priorities.low', ['ru' => 'Низкий', 'en' => 'Low', 'lt' => 'Zemas', 'pl' => 'Niski']),
            $this->entry('crm', 'crm.tasks.priorities.normal', ['ru' => 'Обычный', 'en' => 'Normal', 'lt' => 'Normalus', 'pl' => 'Normalny']),
            $this->entry('crm', 'crm.tasks.priorities.high', ['ru' => 'Высокий', 'en' => 'High', 'lt' => 'Aukstas', 'pl' => 'Wysoki']),
            $this->entry('crm', 'crm.tasks.priorities.urgent', ['ru' => 'Срочный', 'en' => 'Urgent', 'lt' => 'Skubus', 'pl' => 'Pilny']),
            $this->entry('crm', 'crm.tasks.summary', ['ru' => ':tasks задач · :communications сообщ. · :comments замет.', 'en' => ':tasks tasks · :communications comms · :comments notes', 'lt' => ':tasks uzd. · :communications zin. · :comments past.', 'pl' => ':tasks zad. · :communications kom. · :comments not.']),
            $this->entry('crm', 'crm.tasks.system_titles.call_new_application', ['ru' => 'Позвонить по новой заявке', 'en' => 'Call new application', 'lt' => 'Paskambinti del naujos uzklausos', 'pl' => 'Zadzwon do nowego zgloszenia']),
            $this->entry('crm', 'crm.tasks.system_titles.call_back', ['ru' => 'Перезвонить: :name', 'en' => 'Call back: :name', 'lt' => 'Perskambinti: :name', 'pl' => 'Oddzwon: :name']),
            $this->entry('crm', 'crm.tasks.system_titles.follow_up', ['ru' => 'Контакт: :status', 'en' => 'Follow up: :status', 'lt' => 'Kontaktas: :status', 'pl' => 'Kontakt: :status']),
            $this->entry('crm', 'crm.tasks.system_notes.new_public_lead_reminder', ['ru' => 'Автоматическое напоминание по новой заявке с сайта.', 'en' => 'Automatic reminder for a new public website lead.', 'lt' => 'Automatinis priminimas apie nauja svetaines uzklausa.', 'pl' => 'Automatyczne przypomnienie o nowym leadzie ze strony.']),
            $this->entry('crm', 'crm.tasks.system_notes.callback_reminder', ['ru' => 'Автоматическое напоминание о звонке из коммуникации лида.', 'en' => 'Automatic callback reminder from lead communication.', 'lt' => 'Automatinis perskambinimo priminimas is komunikacijos.', 'pl' => 'Automatyczne przypomnienie callbacku z komunikacji.']),
            $this->entry('crm', 'crm.tasks.system_notes.status_move_reminder', ['ru' => 'Автоматическое напоминание после смены статуса.', 'en' => 'Automatic reminder after status move.', 'lt' => 'Automatinis priminimas pakeitus busena.', 'pl' => 'Automatyczne przypomnienie po zmianie statusu.']),
            $this->entry('crm', 'crm.tasks.system_notes.seeded_pipeline_reminder', ['ru' => 'Демо-напоминание для CRM воронки.', 'en' => 'Seeded reminder for CRM funnel demo.', 'lt' => 'Demo priminimas CRM eigai.', 'pl' => 'Demo przypomnienie dla lejka CRM.']),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function crmDictionaryEntries(): array
    {
        return [
            $this->entry('crm', 'crm.dictionaries.create_title', ['ru' => 'Создать запись словаря', 'en' => 'Create dictionary item', 'lt' => 'Sukurti zodyno irasa', 'pl' => 'Utworz wpis slownika']),
            $this->entry('crm', 'crm.dictionaries.edit_title', ['ru' => 'Редактировать запись словаря', 'en' => 'Edit dictionary item', 'lt' => 'Redaguoti zodyno irasa', 'pl' => 'Edytuj wpis slownika']),
            $this->entry('crm', 'crm.dictionaries.statuses.title', ['ru' => 'Статусы лидов', 'en' => 'Lead statuses', 'lt' => 'Uzklausu busenos', 'pl' => 'Statusy leadow']),
            $this->entry('crm', 'crm.dictionaries.statuses.description', ['ru' => 'Переводы и порядок системных статусов лидов.', 'en' => 'Translations and ordering for system lead statuses.', 'lt' => 'Sisteminiu uzklausu busenu vertimai ir tvarka.', 'pl' => 'Tlumaczenia i kolejnosc systemowych statusow leadow.']),
            $this->entry('crm', 'crm.dictionaries.sources.title', ['ru' => 'Источники лидов', 'en' => 'Lead sources', 'lt' => 'Uzklausu saltiniai', 'pl' => 'Zrodla leadow']),
            $this->entry('crm', 'crm.dictionaries.sources.description', ['ru' => 'Источники заявок с сайта, рекламы, звонков и рекомендаций.', 'en' => 'Lead sources from website, ads, calls, and referrals.', 'lt' => 'Uzklausu saltiniai is svetaines, reklamos, skambuciu ir rekomendaciju.', 'pl' => 'Zrodla leadow ze strony, reklam, rozmow i polecen.']),
            $this->entry('crm', 'crm.dictionaries.lost_reasons.title', ['ru' => 'Причины отказа', 'en' => 'Lost reasons', 'lt' => 'Praradimo priezastys', 'pl' => 'Powody utraty']),
            $this->entry('crm', 'crm.dictionaries.lost_reasons.description', ['ru' => 'Причины закрытия лида без продажи.', 'en' => 'Reasons for closing a lead without sale.', 'lt' => 'Priezastys uzdaryti uzklausa be pardavimo.', 'pl' => 'Powody zamkniecia leada bez sprzedazy.']),
            $this->entry('crm', 'crm.dictionaries.tags.title', ['ru' => 'Теги лидов', 'en' => 'Lead tags', 'lt' => 'Uzklausu zymos', 'pl' => 'Tagi leadow']),
            $this->entry('crm', 'crm.dictionaries.tags.description', ['ru' => 'Метки для сегментации и быстрых CRM действий.', 'en' => 'Tags for segmentation and quick CRM actions.', 'lt' => 'Zymos segmentavimui ir greitiems CRM veiksmams.', 'pl' => 'Tagi do segmentacji i szybkich akcji CRM.']),
            $this->entry('crm', 'crm.dictionaries.fields.key', ['ru' => 'Ключ', 'en' => 'Key', 'lt' => 'Raktas', 'pl' => 'Klucz']),
            $this->entry('crm', 'crm.dictionaries.fields.code', ['ru' => 'Код', 'en' => 'Code', 'lt' => 'Kodas', 'pl' => 'Kod']),
            $this->entry('crm', 'crm.dictionaries.fields.slug', ['ru' => 'Slug', 'en' => 'Slug', 'lt' => 'Slug', 'pl' => 'Slug']),
            $this->entry('crm', 'crm.dictionaries.fields.name', ['ru' => 'Название', 'en' => 'Name', 'lt' => 'Pavadinimas', 'pl' => 'Nazwa']),
            $this->entry('crm', 'crm.dictionaries.fields.name_translation', ['ru' => 'Название - :language', 'en' => 'Name - :language', 'lt' => 'Pavadinimas - :language', 'pl' => 'Nazwa - :language']),
            $this->entry('crm', 'crm.dictionaries.fields.name_translations', ['ru' => 'Переводы названия', 'en' => 'Name translations', 'lt' => 'Pavadinimo vertimai', 'pl' => 'Tlumaczenia nazwy']),
            $this->entry('crm', 'crm.dictionaries.fields.is_active', ['ru' => 'Активен', 'en' => 'Active', 'lt' => 'Aktyvus', 'pl' => 'Aktywny']),
            $this->entry('crm', 'crm.dictionaries.fields.sort_order', ['ru' => 'Порядок сортировки', 'en' => 'Sort order', 'lt' => 'Rusiavimo tvarka', 'pl' => 'Kolejnosc sortowania']),
            $this->entry('crm', 'crm.dictionaries.messages.saved', ['ru' => 'Запись словаря сохранена.', 'en' => 'Dictionary item saved.', 'lt' => 'Zodyno irasas issaugotas.', 'pl' => 'Wpis slownika zapisany.']),
            $this->entry('crm', 'crm.dictionaries.messages.deleted', ['ru' => 'Запись словаря удалена.', 'en' => 'Dictionary item deleted.', 'lt' => 'Zodyno irasas istrintas.', 'pl' => 'Wpis slownika usuniety.']),
            $this->entry('crm', 'crm.dictionaries.messages.delete_confirm', ['ru' => 'Удалить эту запись словаря?', 'en' => 'Delete this dictionary item?', 'lt' => 'Istrinti si zodyno irasa?', 'pl' => 'Usunac ten wpis slownika?']),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function crmActivityEntries(): array
    {
        return [
            $this->entry('crm', 'crm.activities.types.created', ['ru' => 'Создано', 'en' => 'Created', 'lt' => 'Sukurta', 'pl' => 'Utworzono']),
            $this->entry('crm', 'crm.activities.types.status_changed', ['ru' => 'Статус изменён', 'en' => 'Status changed', 'lt' => 'Busena pakeista', 'pl' => 'Status zmieniony']),
            $this->entry('crm', 'crm.activities.types.manager_assigned', ['ru' => 'Назначен менеджер', 'en' => 'Manager assigned', 'lt' => 'Vadybininkas priskirtas', 'pl' => 'Menedzer przypisany']),
            $this->entry('crm', 'crm.activities.types.note_added', ['ru' => 'Добавлена заметка', 'en' => 'Note added', 'lt' => 'Pastaba prideta', 'pl' => 'Notatka dodana']),
            $this->entry('crm', 'crm.activities.types.call_logged', ['ru' => 'Звонок зафиксирован', 'en' => 'Call logged', 'lt' => 'Skambutis uzfiksuotas', 'pl' => 'Rozmowa zapisana']),
            $this->entry('crm', 'crm.activities.types.email_logged', ['ru' => 'Email зафиксирован', 'en' => 'Email logged', 'lt' => 'Email uzfiksuotas', 'pl' => 'Email zapisany']),
            $this->entry('crm', 'crm.activities.types.messenger_logged', ['ru' => 'Сообщение зафиксировано', 'en' => 'Messenger logged', 'lt' => 'Zinute uzfiksuota', 'pl' => 'Komunikator zapisany']),
            $this->entry('crm', 'crm.activities.types.task_created', ['ru' => 'Задача создана', 'en' => 'Task created', 'lt' => 'Uzduotis sukurta', 'pl' => 'Zadanie utworzone']),
            $this->entry('crm', 'crm.activities.types.task_completed', ['ru' => 'Задача выполнена', 'en' => 'Task completed', 'lt' => 'Uzduotis atlikta', 'pl' => 'Zadanie ukonczone']),
            $this->entry('crm', 'crm.activities.types.marked_duplicate', ['ru' => 'Отмечено дублем', 'en' => 'Marked duplicate', 'lt' => 'Pazymeta kaip dublikatas', 'pl' => 'Oznaczono duplikat']),
            $this->entry('crm', 'crm.activities.types.marked_lost', ['ru' => 'Отмечено отказом', 'en' => 'Marked lost', 'lt' => 'Pazymeta kaip prarasta', 'pl' => 'Oznaczono jako utracone']),
            $this->entry('crm', 'crm.activities.types.marked_spam', ['ru' => 'Отмечено спамом', 'en' => 'Marked spam', 'lt' => 'Pazymeta kaip slamstas', 'pl' => 'Oznaczono spam']),
            $this->entry('crm', 'crm.activities.types.converted', ['ru' => 'Конвертировано', 'en' => 'Converted', 'lt' => 'Konvertuota', 'pl' => 'Skonwertowano']),
            $this->entry('crm', 'crm.activities.reasons.crm_card_status_update', ['ru' => 'Статус изменён из карточки CRM.', 'en' => 'CRM card status update.', 'lt' => 'Busena pakeista CRM korteleje.', 'pl' => 'Aktualizacja statusu z karty CRM.']),
            $this->entry('crm', 'crm.activities.reasons.public_application_received', ['ru' => 'Получена заявка с сайта.', 'en' => 'Public application received.', 'lt' => 'Gauta svetaines paraiska.', 'pl' => 'Otrzymano zgloszenie publiczne.']),
            $this->entry('crm', 'crm.activities.reasons.seeded_pipeline_state', ['ru' => 'Демо-состояние CRM воронки.', 'en' => 'Seeded CRM pipeline state.', 'lt' => 'Demo CRM eigos busena.', 'pl' => 'Demo stan lejka CRM.']),
            $this->entry('crm', 'crm.activities.messages.public_enrollment_created', ['ru' => 'Лид автоматически создан из формы записи на сайте.', 'en' => 'Lead created automatically from public enrollment form.', 'lt' => 'Uzklausa automatiskai sukurta is svetaines registracijos formos.', 'pl' => 'Lead automatycznie utworzony z formularza zapisow.']),
            $this->entry('crm', 'crm.activities.messages.seeded_pipeline_import', ['ru' => 'Лид импортирован в CRM воронку продаж.', 'en' => 'Lead imported into sales CRM pipeline.', 'lt' => 'Uzklausa importuota i CRM pardavimu eiga.', 'pl' => 'Lead zaimportowany do lejka CRM.']),
            $this->entry('crm', 'crm.status_history.title', ['ru' => 'История статусов', 'en' => 'Status history', 'lt' => 'Busenu istorija', 'pl' => 'Historia statusow']),
            $this->entry('crm', 'crm.status_history.fields.changed_at', ['ru' => 'Изменено', 'en' => 'Changed', 'lt' => 'Pakeista', 'pl' => 'Zmieniono']),
            $this->entry('crm', 'crm.status_history.fields.from_status', ['ru' => 'Из статуса', 'en' => 'From', 'lt' => 'Is busenos', 'pl' => 'Ze statusu']),
            $this->entry('crm', 'crm.status_history.fields.to_status', ['ru' => 'В статус', 'en' => 'To', 'lt' => 'I busena', 'pl' => 'Do statusu']),
            $this->entry('crm', 'crm.status_history.fields.reason', ['ru' => 'Причина', 'en' => 'Reason', 'lt' => 'Priezastis', 'pl' => 'Powod']),
            $this->entry('crm', 'crm.documents.title', ['ru' => 'Прикрепленные документы', 'en' => 'Attached documents', 'lt' => 'Prideti dokumentai', 'pl' => 'Zalaczone dokumenty']),
            $this->entry('crm', 'crm.documents.fields.document', ['ru' => 'Документ', 'en' => 'Document', 'lt' => 'Dokumentas', 'pl' => 'Dokument']),
            $this->entry('crm', 'crm.documents.fields.type', ['ru' => 'Тип', 'en' => 'Type', 'lt' => 'Tipas', 'pl' => 'Typ']),
            $this->entry('crm', 'crm.documents.fields.size', ['ru' => 'Размер', 'en' => 'Size', 'lt' => 'Dydis', 'pl' => 'Rozmiar']),
            $this->entry('crm', 'crm.documents.fields.uploaded_at', ['ru' => 'Загружено', 'en' => 'Uploaded', 'lt' => 'Ikeltas', 'pl' => 'Przeslano']),
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
            $this->entry('sources', 'crm.sources.facebook', ['ru' => 'Facebook', 'en' => 'Facebook', 'lt' => 'Facebook', 'pl' => 'Facebook']),
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
            $this->entry('website', 'website.navigation.main', ['ru' => 'Основная навигация', 'en' => 'Main navigation', 'lt' => 'Pagrindine navigacija', 'pl' => 'Glowna nawigacja']),
            $this->entry('website', 'website.nav.home', ['ru' => 'Главная', 'en' => 'Home', 'lt' => 'Pradzia', 'pl' => 'Strona glowna']),
            $this->entry('website', 'website.nav.programs', ['ru' => 'Программы', 'en' => 'Programs', 'lt' => 'Programos', 'pl' => 'Programy']),
            $this->entry('website', 'website.nav.instructors', ['ru' => 'Инструкторы', 'en' => 'Instructors', 'lt' => 'Instruktoriai', 'pl' => 'Instruktorzy']),
            $this->entry('website', 'website.nav.fleet', ['ru' => 'Автопарк', 'en' => 'Fleet', 'lt' => 'Transportas', 'pl' => 'Flota']),
            $this->entry('website', 'website.nav.reviews', ['ru' => 'Отзывы', 'en' => 'Reviews', 'lt' => 'Atsiliepimai', 'pl' => 'Opinie']),
            $this->entry('website', 'website.nav.blog', ['ru' => 'База знаний', 'en' => 'Knowledge base', 'lt' => 'Ziniu baze', 'pl' => 'Baza wiedzy']),
            $this->entry('website', 'website.nav.contacts', ['ru' => 'Контакты', 'en' => 'Contacts', 'lt' => 'Kontaktai', 'pl' => 'Kontakty']),
            $this->entry('website', 'website.nav.admin', ['ru' => 'Админ-панель', 'en' => 'Admin', 'lt' => 'Administravimas', 'pl' => 'Panel admina']),
            $this->entry('website', 'website.actions.apply', ['ru' => 'Записаться', 'en' => 'Apply', 'lt' => 'Registruotis', 'pl' => 'Zapisz sie']),
            $this->entry('website', 'website.actions.fast_contact', ['ru' => 'Быстрые способы связи', 'en' => 'Fast contact actions', 'lt' => 'Greiti kontaktai', 'pl' => 'Szybki kontakt']),
            $this->entry('website', 'website.actions.online_chat', ['ru' => 'Онлайн-чат', 'en' => 'Online chat', 'lt' => 'Internetinis pokalbis', 'pl' => 'Czat online']),
            $this->entry('website', 'website.actions.callback', ['ru' => 'Обратный звонок', 'en' => 'Callback', 'lt' => 'Perskambinimas', 'pl' => 'Oddzwonienie']),
            $this->entry('website', 'website.footer.description', ['ru' => 'Публичный сайт, онлайн-запись, карта филиалов, CRM заявок и рабочая админ-панель.', 'en' => 'Public website, online enrollment, branch map, CRM lead intake, and operational back office.', 'lt' => 'Svetaine, registracija internetu, filialu zemelapis, CRM uzklausos ir vidinis valdymas.', 'pl' => 'Strona publiczna, zapisy online, mapa oddzialow, CRM leadow i zaplecze operacyjne.']),
            $this->entry('website', 'website.messages.application_received', ['ru' => 'Заявка принята. Менеджер скоро свяжется с вами.', 'en' => 'Application received. A manager will contact you soon.', 'lt' => 'Paraiska gauta. Vadybininkas netrukus susisieks.', 'pl' => 'Zgloszenie przyjete. Menedzer skontaktuje sie wkrotce.']),
            $this->entry('website', 'website.seo.default_title', ['ru' => 'Автошкола DrivePro Academy', 'en' => 'DrivePro Academy driving school', 'lt' => 'DrivePro Academy vairavimo mokykla', 'pl' => 'Szkola jazdy DrivePro Academy']),
            $this->entry('website', 'website.seo.default_description', ['ru' => 'Программы обучения, инструкторы, группы и запись в автошколу.', 'en' => 'Training programs, instructors, groups, and driving school enrollment.', 'lt' => 'Mokymo programos, instruktoriai, grupes ir registracija.', 'pl' => 'Programy szkolen, instruktorzy, grupy i zapisy.']),
        ];
    }
}
