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
            $this->entry('analytics', 'analytics.columns.status', ['ru' => 'Статус', 'en' => 'Status', 'lt' => 'Busena', 'pl' => 'Status']),
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
            $this->entry('analytics', 'analytics.validation.code_required', ['ru' => 'Укажите код.', 'en' => 'Enter the code.', 'lt' => 'Iveskite koda.', 'pl' => 'Podaj kod.']),
            $this->entry('analytics', 'analytics.validation.name_required', ['ru' => 'Укажите название.', 'en' => 'Enter the name.', 'lt' => 'Iveskite pavadinima.', 'pl' => 'Podaj nazwe.']),
            $this->entry('analytics', 'analytics.validation.report_type_required', ['ru' => 'Выберите тип отчета.', 'en' => 'Select report type.', 'lt' => 'Pasirinkite ataskaitos tipa.', 'pl' => 'Wybierz typ raportu.']),
            $this->entry('analytics', 'analytics.validation.report_required', ['ru' => 'Выберите отчет.', 'en' => 'Select a report.', 'lt' => 'Pasirinkite ataskaita.', 'pl' => 'Wybierz raport.']),
            $this->entry('analytics', 'analytics.validation.metric_required', ['ru' => 'Выберите KPI.', 'en' => 'Select a KPI metric.', 'lt' => 'Pasirinkite KPI metrika.', 'pl' => 'Wybierz metryke KPI.']),
            $this->entry('analytics', 'analytics.validation.target_required', ['ru' => 'Укажите цель.', 'en' => 'Enter the target.', 'lt' => 'Iveskite tiksla.', 'pl' => 'Podaj cel.']),
            $this->entry('analytics', 'analytics.validation.invalid_date_range', ['ru' => 'Диапазон дат некорректен.', 'en' => 'Date range is invalid.', 'lt' => 'Datu intervalas neteisingas.', 'pl' => 'Zakres dat jest nieprawidlowy.']),
            $this->entry('analytics', 'analytics.validation.date_range_too_large', ['ru' => 'Диапазон дат слишком большой.', 'en' => 'Date range is too large.', 'lt' => 'Datu intervalas per didelis.', 'pl' => 'Zakres dat jest za duzy.']),
            $this->entry('analytics', 'analytics.validation.inactive_report', ['ru' => 'Выбранный отчет недоступен.', 'en' => 'The selected report is unavailable.', 'lt' => 'Pasirinkta ataskaita nepasiekiama.', 'pl' => 'Wybrany raport jest niedostepny.']),
            $this->entry('analytics', 'analytics.validation.inactive_metric', ['ru' => 'Выбранная KPI-метрика недоступна.', 'en' => 'The selected KPI metric is unavailable.', 'lt' => 'Pasirinkta KPI metrika nepasiekiama.', 'pl' => 'Wybrana metryka KPI jest niedostepna.']),
            $this->entry('analytics', 'analytics.validation.invalid_dashboard', ['ru' => 'Выбранная панель недоступна.', 'en' => 'The selected dashboard is unavailable.', 'lt' => 'Pasirinktas skydelis nepasiekiamas.', 'pl' => 'Wybrany panel jest niedostepny.']),
            $this->entry('analytics', 'analytics.validation.invalid_widget', ['ru' => 'Выбранный виджет недоступен.', 'en' => 'The selected widget is unavailable.', 'lt' => 'Pasirinktas valdiklis nepasiekiamas.', 'pl' => 'Wybrany widzet jest niedostepny.']),
        ];
    }
}
