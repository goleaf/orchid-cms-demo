<?php

namespace Database\Seeders;

use App\Models\Language;

class AnalyticsTranslationSeeder extends SystemTranslationSeeder
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
            $this->entry('menu', 'menu.analytics', ['ru' => 'Аналитика', 'en' => 'Analytics', 'lt' => 'Analitika', 'pl' => 'Analityka']),
            $this->entry('menu', 'menu.analytics.dashboard', ['ru' => 'Панель владельца', 'en' => 'Owner dashboard', 'lt' => 'Savininko skydelis', 'pl' => 'Panel wlasciciela']),

            $this->entry('permissions', 'permissions.groups.analytics', ['ru' => 'Аналитика', 'en' => 'Analytics', 'lt' => 'Analitika', 'pl' => 'Analityka']),
            $this->entry('permissions', 'permissions.analytics.dashboard.view', ['ru' => 'Просмотр панели владельца', 'en' => 'View owner dashboard', 'lt' => 'Perziureti savininko skydeli', 'pl' => 'Podglad panelu wlasciciela']),
            $this->entry('permissions', 'permissions.analytics.reports.manage', ['ru' => 'Управление отчетами', 'en' => 'Manage reports', 'lt' => 'Tvarkyti ataskaitas', 'pl' => 'Zarzadzanie raportami']),
            $this->entry('permissions', 'permissions.analytics.reports.run', ['ru' => 'Запуск отчетов', 'en' => 'Run reports', 'lt' => 'Vykdyti ataskaitas', 'pl' => 'Uruchamianie raportow']),
            $this->entry('permissions', 'permissions.analytics.reports.export', ['ru' => 'Экспорт отчетов', 'en' => 'Export reports', 'lt' => 'Eksportuoti ataskaitas', 'pl' => 'Eksport raportow']),
            $this->entry('permissions', 'permissions.analytics.kpis.manage', ['ru' => 'Управление KPI', 'en' => 'Manage KPIs', 'lt' => 'Tvarkyti KPI', 'pl' => 'Zarzadzanie KPI']),
            $this->entry('permissions', 'permissions.analytics.kpi_targets.manage', ['ru' => 'Управление целями KPI', 'en' => 'Manage KPI targets', 'lt' => 'Tvarkyti KPI tikslus', 'pl' => 'Zarzadzanie celami KPI']),
            $this->entry('permissions', 'permissions.analytics.preferences.manage', ['ru' => 'Управление настройками панели', 'en' => 'Manage dashboard preferences', 'lt' => 'Tvarkyti skydelio nustatymus', 'pl' => 'Zarzadzanie preferencjami panelu']),
            $this->entry('permissions', 'permissions.analytics.cache.view', ['ru' => 'Просмотр кэша аналитики', 'en' => 'View analytics cache', 'lt' => 'Perziureti analitikos talpykla', 'pl' => 'Podglad pamieci analityki']),

            $this->entry('analytics', 'analytics.dashboard.title', ['ru' => 'Панель владельца', 'en' => 'Owner dashboard', 'lt' => 'Savininko skydelis', 'pl' => 'Panel wlasciciela']),
            $this->entry('analytics', 'analytics.dashboard.description', ['ru' => 'Ключевые показатели автошколы, отчеты и снимки KPI.', 'en' => 'Driving school KPIs, reports, and operational snapshots.', 'lt' => 'Vairavimo mokyklos KPI, ataskaitos ir operaciju suvestines.', 'pl' => 'KPI szkoly jazdy, raporty i migawki operacyjne.']),
            $this->entry('analytics', 'analytics.tables.widgets', ['ru' => 'Виджеты панели', 'en' => 'Dashboard widgets', 'lt' => 'Skydelio valdikliai', 'pl' => 'Widżety panelu']),
            $this->entry('analytics', 'analytics.tables.reports', ['ru' => 'Определения отчетов', 'en' => 'Report definitions', 'lt' => 'Ataskaitu apibrezimai', 'pl' => 'Definicje raportow']),
            $this->entry('analytics', 'analytics.tables.runs', ['ru' => 'Последние запуски', 'en' => 'Recent report runs', 'lt' => 'Naujausi paleidimai', 'pl' => 'Ostatnie uruchomienia']),
            $this->entry('analytics', 'analytics.tables.snapshots', ['ru' => 'Снимки KPI', 'en' => 'KPI snapshots', 'lt' => 'KPI momentines busenos', 'pl' => 'Migawki KPI']),

            $this->entry('analytics', 'analytics.metrics.open_leads', ['ru' => 'Открытые лиды', 'en' => 'Open leads', 'lt' => 'Atviros uzklausos', 'pl' => 'Otwarte leady']),
            $this->entry('analytics', 'analytics.metrics.converted_leads', ['ru' => 'Конвертированные лиды', 'en' => 'Converted leads', 'lt' => 'Konvertuotos uzklausos', 'pl' => 'Skonwertowane leady']),
            $this->entry('analytics', 'analytics.metrics.active_students', ['ru' => 'Активные ученики', 'en' => 'Active students', 'lt' => 'Aktyvus mokiniai', 'pl' => 'Aktywni uczniowie']),
            $this->entry('analytics', 'analytics.metrics.active_enrollments', ['ru' => 'Активные записи', 'en' => 'Active enrollments', 'lt' => 'Aktyvios registracijos', 'pl' => 'Aktywne zapisy']),
            $this->entry('analytics', 'analytics.metrics.lessons_today', ['ru' => 'Уроки сегодня', 'en' => 'Lessons today', 'lt' => 'Pamokos siandien', 'pl' => 'Lekcje dzisiaj']),
            $this->entry('analytics', 'analytics.metrics.scheduled_exams', ['ru' => 'Запланированные экзамены', 'en' => 'Scheduled exams', 'lt' => 'Suplanuoti egzaminai', 'pl' => 'Zaplanowane egzaminy']),
            $this->entry('analytics', 'analytics.metrics.paid_revenue', ['ru' => 'Оплаченная выручка', 'en' => 'Paid revenue', 'lt' => 'Apmoketos pajamos', 'pl' => 'Oplacone przychody']),
            $this->entry('analytics', 'analytics.metrics.pending_documents', ['ru' => 'Документы в ожидании', 'en' => 'Pending documents', 'lt' => 'Laukiami dokumentai', 'pl' => 'Oczekujace dokumenty']),
            $this->entry('analytics', 'analytics.metrics.queued_notifications', ['ru' => 'Уведомления в очереди', 'en' => 'Queued notifications', 'lt' => 'Pranesimai eileje', 'pl' => 'Powiadomienia w kolejce']),

            $this->entry('analytics', 'analytics.columns.code', ['ru' => 'Код', 'en' => 'Code', 'lt' => 'Kodas', 'pl' => 'Kod']),
            $this->entry('analytics', 'analytics.columns.name', ['ru' => 'Название', 'en' => 'Name', 'lt' => 'Pavadinimas', 'pl' => 'Nazwa']),
            $this->entry('analytics', 'analytics.columns.type', ['ru' => 'Тип', 'en' => 'Type', 'lt' => 'Tipas', 'pl' => 'Typ']),
            $this->entry('analytics', 'analytics.columns.metric', ['ru' => 'Метрика', 'en' => 'Metric', 'lt' => 'Metrika', 'pl' => 'Metryka']),
            $this->entry('analytics', 'analytics.columns.report', ['ru' => 'Отчет', 'en' => 'Report', 'lt' => 'Ataskaita', 'pl' => 'Raport']),
            $this->entry('analytics', 'analytics.columns.status', ['ru' => 'Статус', 'en' => 'Status', 'lt' => 'Busena', 'pl' => 'Stan']),
            $this->entry('analytics', 'analytics.columns.period', ['ru' => 'Период', 'en' => 'Period', 'lt' => 'Laikotarpis', 'pl' => 'Okres']),
            $this->entry('analytics', 'analytics.columns.value', ['ru' => 'Значение', 'en' => 'Value', 'lt' => 'Reiksme', 'pl' => 'Wartosc']),
            $this->entry('analytics', 'analytics.columns.target', ['ru' => 'Цель', 'en' => 'Target', 'lt' => 'Tikslas', 'pl' => 'Cel']),
            $this->entry('analytics', 'analytics.columns.snapshot_date', ['ru' => 'Дата снимка', 'en' => 'Snapshot date', 'lt' => 'Momentines busenos data', 'pl' => 'Data migawki']),
            $this->entry('analytics', 'analytics.columns.row_count', ['ru' => 'Строки', 'en' => 'Rows', 'lt' => 'Eilutes', 'pl' => 'Wiersze']),
            $this->entry('analytics', 'analytics.columns.runs', ['ru' => 'Запуски', 'en' => 'Runs', 'lt' => 'Paleidimai', 'pl' => 'Uruchomienia']),
            $this->entry('analytics', 'analytics.columns.exports', ['ru' => 'Экспорты', 'en' => 'Exports', 'lt' => 'Eksportai', 'pl' => 'Eksporty']),
            $this->entry('analytics', 'analytics.columns.started_at', ['ru' => 'Запущен', 'en' => 'Started at', 'lt' => 'Pradeta', 'pl' => 'Rozpoczeto']),
            $this->entry('analytics', 'analytics.columns.finished_at', ['ru' => 'Завершен', 'en' => 'Finished at', 'lt' => 'Baigta', 'pl' => 'Zakonczono']),

            $this->entry('analytics', 'analytics.validation.invalid_code', ['ru' => 'Код аналитики некорректен.', 'en' => 'Analytics code is invalid.', 'lt' => 'Analitikos kodas neteisingas.', 'pl' => 'Kod analityki jest nieprawidlowy.']),
            $this->entry('analytics', 'analytics.validation.invalid_cache_key', ['ru' => 'Ключ кэша аналитики некорректен.', 'en' => 'Analytics cache key is invalid.', 'lt' => 'Analitikos talpyklos raktas neteisingas.', 'pl' => 'Klucz pamieci analityki jest nieprawidlowy.']),
            $this->entry('analytics', 'analytics.validation.report_not_active', ['ru' => 'Выбранный отчет недоступен.', 'en' => 'The selected report is unavailable.', 'lt' => 'Pasirinkta ataskaita nepasiekiama.', 'pl' => 'Wybrany raport jest niedostepny.']),
            $this->entry('analytics', 'analytics.validation.invalid_filter', ['ru' => 'Фильтр отчета некорректен.', 'en' => 'Report filter is invalid.', 'lt' => 'Ataskaitos filtras neteisingas.', 'pl' => 'Filtr raportu jest nieprawidlowy.']),
            $this->entry('analytics', 'analytics.validation.export_not_allowed', ['ru' => 'Экспорт отчета недоступен.', 'en' => 'Report export is not allowed.', 'lt' => 'Ataskaitos eksportas negalimas.', 'pl' => 'Eksport raportu jest niedozwolony.']),
            $this->entry('analytics', 'analytics.validation.invalid_format', ['ru' => 'Формат экспорта некорректен.', 'en' => 'Export format is invalid.', 'lt' => 'Eksporto formatas neteisingas.', 'pl' => 'Format eksportu jest nieprawidlowy.']),
            $this->entry('analytics', 'analytics.validation.kpi_not_active', ['ru' => 'Выбранная KPI-метрика недоступна.', 'en' => 'The selected KPI metric is unavailable.', 'lt' => 'Pasirinkta KPI metrika nepasiekiama.', 'pl' => 'Wybrana metryka KPI jest niedostepna.']),
            $this->entry('analytics', 'analytics.validation.invalid_period', ['ru' => 'Период KPI некорректен.', 'en' => 'KPI period is invalid.', 'lt' => 'KPI laikotarpis neteisingas.', 'pl' => 'Okres KPI jest nieprawidlowy.']),
            $this->entry('analytics', 'analytics.validation.invalid_target_value', ['ru' => 'Значение цели KPI некорректно.', 'en' => 'KPI target value is invalid.', 'lt' => 'KPI tikslo reiksme neteisinga.', 'pl' => 'Wartosc celu KPI jest nieprawidlowa.']),
            $this->entry('analytics', 'analytics.validation.duplicate_kpi_target', ['ru' => 'Цель KPI для этого периода уже существует.', 'en' => 'A KPI target for this period already exists.', 'lt' => 'Sio laikotarpio KPI tikslas jau yra.', 'pl' => 'Cel KPI dla tego okresu juz istnieje.']),
            $this->entry('analytics', 'analytics.validation.invalid_widget_config', ['ru' => 'Настройки виджета некорректны.', 'en' => 'Dashboard widget configuration is invalid.', 'lt' => 'Skydelio valdiklio nustatymai neteisingi.', 'pl' => 'Konfiguracja widzetu panelu jest nieprawidlowa.']),
            $this->entry('analytics', 'analytics.validation.module_not_available', ['ru' => 'Модуль аналитики недоступен.', 'en' => 'Analytics module is not available.', 'lt' => 'Analitikos modulis nepasiekiamas.', 'pl' => 'Modul analityki jest niedostepny.']),
            $this->entry('analytics', 'analytics.validation.column_not_allowed', ['ru' => 'Колонка отчета недоступна.', 'en' => 'Report column is not allowed.', 'lt' => 'Ataskaitos stulpelis negalimas.', 'pl' => 'Kolumna raportu jest niedozwolona.']),
            $this->entry('analytics', 'analytics.validation.filter_value_not_allowed', ['ru' => 'Значение фильтра отчета недоступно.', 'en' => 'Report filter value is not allowed.', 'lt' => 'Ataskaitos filtro reiksme negalima.', 'pl' => 'Wartosc filtra raportu jest niedozwolona.']),
            $this->entry('analytics', 'analytics.validation.code_required', ['ru' => 'Укажите код.', 'en' => 'Enter the code.', 'lt' => 'Iveskite koda.', 'pl' => 'Podaj kod.']),
            $this->entry('analytics', 'analytics.validation.name_required', ['ru' => 'Укажите название.', 'en' => 'Enter the name.', 'lt' => 'Iveskite pavadinima.', 'pl' => 'Podaj nazwe.']),
            $this->entry('analytics', 'analytics.validation.report_type_required', ['ru' => 'Выберите тип отчета.', 'en' => 'Select report type.', 'lt' => 'Pasirinkite ataskaitos tipa.', 'pl' => 'Wybierz typ raportu.']),
            $this->entry('analytics', 'analytics.validation.report_required', ['ru' => 'Выберите отчет.', 'en' => 'Select a report.', 'lt' => 'Pasirinkite ataskaita.', 'pl' => 'Wybierz raport.']),
            $this->entry('analytics', 'analytics.validation.metric_required', ['ru' => 'Выберите KPI.', 'en' => 'Select a KPI metric.', 'lt' => 'Pasirinkite KPI metrika.', 'pl' => 'Wybierz metryke KPI.']),
            $this->entry('analytics', 'analytics.validation.target_required', ['ru' => 'Укажите цель.', 'en' => 'Enter the target.', 'lt' => 'Iveskite tiksla.', 'pl' => 'Podaj cel.']),
            $this->entry('analytics', 'analytics.validation.invalid_date_range', ['ru' => 'Диапазон дат некорректен.', 'en' => 'Date range is invalid.', 'lt' => 'Datu intervalas neteisingas.', 'pl' => 'Zakres dat jest nieprawidlowy.']),
            $this->entry('analytics', 'analytics.validation.date_range_too_large', ['ru' => 'Диапазон дат слишком большой.', 'en' => 'Date range is too large.', 'lt' => 'Datu intervalas per didelis.', 'pl' => 'Zakres dat jest za duzy.']),
            $this->entry('analytics', 'analytics.validation.permission_denied', ['ru' => 'Недостаточно прав для аналитики.', 'en' => 'You do not have permission for this analytics action.', 'lt' => 'Neturite teises atlikti sio analitikos veiksmo.', 'pl' => 'Nie masz uprawnien do tej akcji analitycznej.']),
            $this->entry('analytics', 'analytics.validation.inactive_report', ['ru' => 'Выбранный отчет недоступен.', 'en' => 'The selected report is unavailable.', 'lt' => 'Pasirinkta ataskaita nepasiekiama.', 'pl' => 'Wybrany raport jest niedostepny.']),
            $this->entry('analytics', 'analytics.validation.inactive_metric', ['ru' => 'Выбранная KPI-метрика недоступна.', 'en' => 'The selected KPI metric is unavailable.', 'lt' => 'Pasirinkta KPI metrika nepasiekiama.', 'pl' => 'Wybrana metryka KPI jest niedostepna.']),
            $this->entry('analytics', 'analytics.validation.invalid_dashboard', ['ru' => 'Выбранная панель недоступна.', 'en' => 'The selected dashboard is unavailable.', 'lt' => 'Pasirinktas skydelis nepasiekiamas.', 'pl' => 'Wybrany panel jest niedostepny.']),
            $this->entry('analytics', 'analytics.validation.invalid_widget', ['ru' => 'Выбранный виджет недоступен.', 'en' => 'The selected widget is unavailable.', 'lt' => 'Pasirinktas valdiklis nepasiekiamas.', 'pl' => 'Wybrany widzet jest niedostepny.']),

            $this->entry('validation', 'validation.attributes.analytics.dashboard', ['ru' => 'панель аналитики', 'en' => 'analytics dashboard', 'lt' => 'analitikos skydelis', 'pl' => 'panel analityki']),
            $this->entry('validation', 'validation.attributes.analytics.dashboard_code', ['ru' => 'код панели', 'en' => 'dashboard code', 'lt' => 'skydelio kodas', 'pl' => 'kod panelu']),
            $this->entry('validation', 'validation.attributes.analytics.dashboard_name', ['ru' => 'название панели', 'en' => 'dashboard name', 'lt' => 'skydelio pavadinimas', 'pl' => 'nazwa panelu']),
            $this->entry('validation', 'validation.attributes.analytics.dashboard_description', ['ru' => 'описание панели', 'en' => 'dashboard description', 'lt' => 'skydelio aprasymas', 'pl' => 'opis panelu']),
            $this->entry('validation', 'validation.attributes.analytics.dashboard_audience', ['ru' => 'аудитория панели', 'en' => 'dashboard audience', 'lt' => 'skydelio auditorija', 'pl' => 'odbiorcy panelu']),
            $this->entry('validation', 'validation.attributes.analytics.widget_code', ['ru' => 'код виджета', 'en' => 'widget code', 'lt' => 'valdiklio kodas', 'pl' => 'kod widzetu']),
            $this->entry('validation', 'validation.attributes.analytics.widget_type', ['ru' => 'тип виджета', 'en' => 'widget type', 'lt' => 'valdiklio tipas', 'pl' => 'typ widzetu']),
            $this->entry('validation', 'validation.attributes.analytics.widget_title', ['ru' => 'заголовок виджета', 'en' => 'widget title', 'lt' => 'valdiklio antraste', 'pl' => 'tytul widzetu']),
            $this->entry('validation', 'validation.attributes.analytics.widget_description', ['ru' => 'описание виджета', 'en' => 'widget description', 'lt' => 'valdiklio aprasymas', 'pl' => 'opis widzetu']),
            $this->entry('validation', 'validation.attributes.analytics.widget_config', ['ru' => 'настройки виджета', 'en' => 'widget configuration', 'lt' => 'valdiklio nustatymai', 'pl' => 'konfiguracja widzetu']),
            $this->entry('validation', 'validation.attributes.analytics.filters', ['ru' => 'фильтры', 'en' => 'filters', 'lt' => 'filtrai', 'pl' => 'filtry']),
            $this->entry('validation', 'validation.attributes.analytics.width', ['ru' => 'ширина', 'en' => 'width', 'lt' => 'plotis', 'pl' => 'szerokosc']),
            $this->entry('validation', 'validation.attributes.analytics.height', ['ru' => 'высота', 'en' => 'height', 'lt' => 'aukstis', 'pl' => 'wysokosc']),
            $this->entry('validation', 'validation.attributes.analytics.sort_order', ['ru' => 'порядок сортировки', 'en' => 'sort order', 'lt' => 'rikiavimo tvarka', 'pl' => 'kolejnosc sortowania']),
            $this->entry('validation', 'validation.attributes.analytics.is_active', ['ru' => 'активность', 'en' => 'active flag', 'lt' => 'aktyvumo pozymis', 'pl' => 'aktywnosc']),
            $this->entry('validation', 'validation.attributes.analytics.is_default', ['ru' => 'по умолчанию', 'en' => 'default flag', 'lt' => 'numatytasis pozymis', 'pl' => 'domyslnosc']),
            $this->entry('validation', 'validation.attributes.analytics.report', ['ru' => 'отчет', 'en' => 'report', 'lt' => 'ataskaita', 'pl' => 'raport']),
            $this->entry('validation', 'validation.attributes.analytics.report_code', ['ru' => 'код отчета', 'en' => 'report code', 'lt' => 'ataskaitos kodas', 'pl' => 'kod raportu']),
            $this->entry('validation', 'validation.attributes.analytics.report_name', ['ru' => 'название отчета', 'en' => 'report name', 'lt' => 'ataskaitos pavadinimas', 'pl' => 'nazwa raportu']),
            $this->entry('validation', 'validation.attributes.analytics.report_description', ['ru' => 'описание отчета', 'en' => 'report description', 'lt' => 'ataskaitos aprasymas', 'pl' => 'opis raportu']),
            $this->entry('validation', 'validation.attributes.analytics.report_group', ['ru' => 'группа отчета', 'en' => 'report group', 'lt' => 'ataskaitos grupe', 'pl' => 'grupa raportu']),
            $this->entry('validation', 'validation.attributes.analytics.data_source', ['ru' => 'источник данных', 'en' => 'data source', 'lt' => 'duomenu saltinis', 'pl' => 'zrodlo danych']),
            $this->entry('validation', 'validation.attributes.analytics.permissions', ['ru' => 'права доступа', 'en' => 'permissions', 'lt' => 'teises', 'pl' => 'uprawnienia']),
            $this->entry('validation', 'validation.attributes.analytics.report_type', ['ru' => 'тип отчета', 'en' => 'report type', 'lt' => 'ataskaitos tipas', 'pl' => 'typ raportu']),
            $this->entry('validation', 'validation.attributes.analytics.schedule', ['ru' => 'расписание', 'en' => 'schedule', 'lt' => 'tvarkarastis', 'pl' => 'harmonogram']),
            $this->entry('validation', 'validation.attributes.analytics.report_run', ['ru' => 'запуск отчета', 'en' => 'report run', 'lt' => 'ataskaitos paleidimas', 'pl' => 'uruchomienie raportu']),
            $this->entry('validation', 'validation.attributes.analytics.export_format', ['ru' => 'формат экспорта', 'en' => 'export format', 'lt' => 'eksporto formatas', 'pl' => 'format eksportu']),
            $this->entry('validation', 'validation.attributes.analytics.period_type', ['ru' => 'тип периода', 'en' => 'period type', 'lt' => 'laikotarpio tipas', 'pl' => 'typ okresu']),
            $this->entry('validation', 'validation.attributes.analytics.period_start', ['ru' => 'начало периода', 'en' => 'period start', 'lt' => 'laikotarpio pradzia', 'pl' => 'poczatek okresu']),
            $this->entry('validation', 'validation.attributes.analytics.period_end', ['ru' => 'конец периода', 'en' => 'period end', 'lt' => 'laikotarpio pabaiga', 'pl' => 'koniec okresu']),
            $this->entry('validation', 'validation.attributes.analytics.branch', ['ru' => 'филиал', 'en' => 'branch', 'lt' => 'filialas', 'pl' => 'oddzial']),
            $this->entry('validation', 'validation.attributes.analytics.user', ['ru' => 'пользователь', 'en' => 'user', 'lt' => 'vartotojas', 'pl' => 'uzytkownik']),
            $this->entry('validation', 'validation.attributes.analytics.training_program', ['ru' => 'программа обучения', 'en' => 'training program', 'lt' => 'mokymo programa', 'pl' => 'program szkolenia']),
            $this->entry('validation', 'validation.attributes.analytics.training_group', ['ru' => 'учебная группа', 'en' => 'training group', 'lt' => 'mokymo grupe', 'pl' => 'grupa szkoleniowa']),
            $this->entry('validation', 'validation.attributes.analytics.instructor', ['ru' => 'инструктор', 'en' => 'instructor', 'lt' => 'instruktorius', 'pl' => 'instruktor']),
            $this->entry('validation', 'validation.attributes.analytics.manager', ['ru' => 'менеджер', 'en' => 'manager', 'lt' => 'vadybininkas', 'pl' => 'menedzer']),
            $this->entry('validation', 'validation.attributes.analytics.status', ['ru' => 'статус', 'en' => 'status', 'lt' => 'busena', 'pl' => 'stan']),
            $this->entry('validation', 'validation.attributes.analytics.source', ['ru' => 'источник', 'en' => 'source', 'lt' => 'saltinis', 'pl' => 'zrodlo']),
            $this->entry('validation', 'validation.attributes.analytics.columns', ['ru' => 'колонки', 'en' => 'columns', 'lt' => 'stulpeliai', 'pl' => 'kolumny']),
            $this->entry('validation', 'validation.attributes.analytics.kpi_metric', ['ru' => 'KPI-метрика', 'en' => 'KPI metric', 'lt' => 'KPI metrika', 'pl' => 'metryka KPI']),
            $this->entry('validation', 'validation.attributes.analytics.kpi_code', ['ru' => 'код KPI', 'en' => 'KPI code', 'lt' => 'KPI kodas', 'pl' => 'kod KPI']),
            $this->entry('validation', 'validation.attributes.analytics.kpi_name', ['ru' => 'название KPI', 'en' => 'KPI name', 'lt' => 'KPI pavadinimas', 'pl' => 'nazwa KPI']),
            $this->entry('validation', 'validation.attributes.analytics.kpi_description', ['ru' => 'описание KPI', 'en' => 'KPI description', 'lt' => 'KPI aprasymas', 'pl' => 'opis KPI']),
            $this->entry('validation', 'validation.attributes.analytics.kpi_group', ['ru' => 'группа KPI', 'en' => 'KPI group', 'lt' => 'KPI grupe', 'pl' => 'grupa KPI']),
            $this->entry('validation', 'validation.attributes.analytics.kpi_unit', ['ru' => 'единица KPI', 'en' => 'KPI unit', 'lt' => 'KPI vienetas', 'pl' => 'jednostka KPI']),
            $this->entry('validation', 'validation.attributes.analytics.calculation_type', ['ru' => 'тип расчета', 'en' => 'calculation type', 'lt' => 'skaiciavimo tipas', 'pl' => 'typ obliczenia']),
            $this->entry('validation', 'validation.attributes.analytics.target_value', ['ru' => 'целевое значение', 'en' => 'target value', 'lt' => 'tiksline reiksme', 'pl' => 'wartosc celu']),
            $this->entry('validation', 'validation.attributes.analytics.warning_threshold', ['ru' => 'порог предупреждения', 'en' => 'warning threshold', 'lt' => 'ispejimo slenkstis', 'pl' => 'prog ostrzezenia']),
            $this->entry('validation', 'validation.attributes.analytics.success_threshold', ['ru' => 'порог успеха', 'en' => 'success threshold', 'lt' => 'sekmes slenkstis', 'pl' => 'prog sukcesu']),
            $this->entry('validation', 'validation.attributes.analytics.cache_key', ['ru' => 'ключ кэша', 'en' => 'cache key', 'lt' => 'talpyklos raktas', 'pl' => 'klucz pamieci']),
            $this->entry('validation', 'validation.attributes.analytics.cache_data', ['ru' => 'данные кэша', 'en' => 'cache data', 'lt' => 'talpyklos duomenys', 'pl' => 'dane pamieci']),
            $this->entry('validation', 'validation.attributes.analytics.cache_tags', ['ru' => 'теги кэша', 'en' => 'cache tags', 'lt' => 'talpyklos zymos', 'pl' => 'tagi pamieci']),
            $this->entry('validation', 'validation.attributes.analytics.cache_ttl', ['ru' => 'срок кэша', 'en' => 'cache lifetime', 'lt' => 'talpyklos galiojimas', 'pl' => 'czas pamieci']),
            $this->entry('validation', 'validation.attributes.analytics.expires_at', ['ru' => 'время истечения', 'en' => 'expiration time', 'lt' => 'galiojimo pabaiga', 'pl' => 'czas wygasniecia']),
            $this->entry('validation', 'validation.attributes.analytics.module', ['ru' => 'модуль', 'en' => 'module', 'lt' => 'modulis', 'pl' => 'modul']),
            $this->entry('validation', 'validation.attributes.analytics.layout', ['ru' => 'макет панели', 'en' => 'dashboard layout', 'lt' => 'skydelio isdestymas', 'pl' => 'uklad panelu']),
            $this->entry('validation', 'validation.attributes.analytics.refresh_interval', ['ru' => 'интервал обновления', 'en' => 'refresh interval', 'lt' => 'atnaujinimo intervalas', 'pl' => 'interwal odswiezania']),
            $this->entry('validation', 'validation.attributes.analytics.timezone', ['ru' => 'часовой пояс', 'en' => 'timezone', 'lt' => 'laiko zona', 'pl' => 'strefa czasowa']),
        ];
    }
}
