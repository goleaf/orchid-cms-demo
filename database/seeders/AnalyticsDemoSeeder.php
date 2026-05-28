<?php

namespace Database\Seeders;

use App\Enums\AnalyticsDashboardAudience;
use App\Enums\AnalyticsReportType;
use App\Enums\DashboardWidgetType;
use App\Enums\KpiDirection;
use App\Enums\KpiPeriod;
use App\Models\AnalyticsCache;
use App\Models\AnalyticsDashboard;
use App\Models\DashboardWidget;
use App\Models\KpiMetric;
use App\Models\KpiSnapshot;
use App\Models\KpiTarget;
use App\Models\ReportDefinition;
use App\Models\User;
use App\Models\UserDashboardPreference;
use Illuminate\Database\Seeder;

class AnalyticsDemoSeeder extends Seeder
{
    public function run(): void
    {
        $dashboard = $this->seedDashboards();

        $this->seedWidgets($dashboard);
        $this->seedReports();
        $metrics = $this->seedMetrics();
        $this->seedTargetsAndSnapshots($metrics);
        $this->seedCache();
        $this->seedPreferences($dashboard);
    }

    private function seedDashboards(): AnalyticsDashboard
    {
        return AnalyticsDashboard::query()->updateOrCreate(
            ['code' => 'owner_overview'],
            AnalyticsDashboard::factory()
                ->default()
                ->audience(AnalyticsDashboardAudience::Owner)
                ->make([
                    'code' => 'owner_overview',
                    'name_translations' => $this->translations('Панель владельца', 'Owner overview', 'Savininko apzvalga', 'Przeglad wlasciciela'),
                    'description_translations' => $this->translations('Локальная панель показателей автошколы.', 'Local driving school owner dashboard.', 'Vietinis vairavimo mokyklos savininko skydelis.', 'Lokalny panel wlasciciela szkoly jazdy.'),
                    'sort_order' => 10,
                ])
                ->only((new AnalyticsDashboard)->getFillable()),
        );
    }

    private function seedWidgets(AnalyticsDashboard $dashboard): void
    {
        foreach ($this->widgetDefinitions() as $index => $definition) {
            DashboardWidget::query()->updateOrCreate(
                ['code' => $definition['code']],
                DashboardWidget::factory()
                    ->forDashboard($dashboard)
                    ->system()
                    ->make([
                        ...$definition,
                        'analytics_dashboard_id' => $dashboard->id,
                        'sort_order' => ($index + 1) * 10,
                    ])
                    ->only((new DashboardWidget)->getFillable()),
            );
        }
    }

    private function seedReports(): void
    {
        foreach ($this->reportDefinitions() as $index => $definition) {
            ReportDefinition::query()->updateOrCreate(
                ['code' => $definition['code']],
                ReportDefinition::factory()
                    ->system()
                    ->make([
                        ...$definition,
                        'sort_order' => ($index + 1) * 10,
                    ])
                    ->only((new ReportDefinition)->getFillable()),
            );
        }
    }

    /**
     * @return array<string, KpiMetric>
     */
    private function seedMetrics(): array
    {
        $metrics = [];

        foreach ($this->metricDefinitions() as $index => $definition) {
            $metrics[$definition['code']] = KpiMetric::query()->updateOrCreate(
                ['code' => $definition['code']],
                KpiMetric::factory()
                    ->system()
                    ->make([
                        ...$definition,
                        'sort_order' => ($index + 1) * 10,
                    ])
                    ->only((new KpiMetric)->getFillable()),
            );
        }

        return $metrics;
    }

    /**
     * @param  array<string, KpiMetric>  $metrics
     */
    private function seedTargetsAndSnapshots(array $metrics): void
    {
        foreach ($metrics as $metric) {
            $target = KpiTarget::query()->firstOrCreate(
                [
                    'kpi_metric_id' => $metric->id,
                    'period' => KpiPeriod::Month->value,
                    'starts_on' => now()->startOfYear()->toDateString(),
                ],
                KpiTarget::factory()
                    ->forMetric($metric)
                    ->make([
                        'target_value' => $metric->code === 'paid_revenue_eur' ? 10000 : 25,
                        'warning_value' => $metric->code === 'paid_revenue_eur' ? 7500 : 15,
                        'direction' => KpiDirection::Increase,
                    ])
                    ->only((new KpiTarget)->getFillable()),
            );

            KpiSnapshot::query()->firstOrCreate(
                [
                    'kpi_metric_id' => $metric->id,
                    'period' => KpiPeriod::Day->value,
                    'snapshot_date' => now()->toDateString(),
                ],
                KpiSnapshot::factory()
                    ->forMetric($metric)
                    ->make([
                        'value' => 0,
                        'target_value' => $target->target_value,
                        'source_payload' => ['seeded' => true],
                    ])
                    ->only((new KpiSnapshot)->getFillable()),
            );
        }
    }

    private function seedCache(): void
    {
        AnalyticsCache::query()->updateOrCreate(
            ['key' => 'owner_dashboard.metrics'],
            AnalyticsCache::factory()
                ->make([
                    'key' => 'owner_dashboard.metrics',
                    'group' => 'dashboard',
                    'value' => ['seeded' => true],
                    'tags' => ['analytics', 'dashboard'],
                ])
                ->only((new AnalyticsCache)->getFillable()),
        );
    }

    private function seedPreferences(AnalyticsDashboard $dashboard): void
    {
        $user = User::query()->first();

        if ($user === null) {
            return;
        }

        UserDashboardPreference::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'dashboard' => $dashboard->code,
            ],
            UserDashboardPreference::factory()
                ->forDashboard($dashboard)
                ->make([
                    'user_id' => $user->id,
                    'analytics_dashboard_id' => $dashboard->id,
                    'dashboard' => $dashboard->code,
                    'layout' => [
                        'widgets' => collect($this->widgetDefinitions())
                            ->map(fn (array $definition, int $index): array => [
                                'code' => $definition['code'],
                                'width' => $definition['width'],
                                'height' => $definition['height'],
                                'sort_order' => ($index + 1) * 10,
                            ])
                            ->values()
                            ->all(),
                    ],
                    'visible_widget_codes' => array_column($this->widgetDefinitions(), 'code'),
                    'widget_order' => array_column($this->widgetDefinitions(), 'code'),
                ])
                ->only((new UserDashboardPreference)->getFillable()),
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function widgetDefinitions(): array
    {
        return [
            $this->counterWidget('open_leads', 'Открытые лиды', 'Open leads', 'Atviros uzklausos', 'Otwarte leady', 'open_leads', 3, 1),
            $this->counterWidget('active_students', 'Активные ученики', 'Active students', 'Aktyvus mokiniai', 'Aktywni uczniowie', 'active_students', 3, 1),
            $this->counterWidget('active_enrollments', 'Активные записи', 'Active enrollments', 'Aktyvios registracijos', 'Aktywne zapisy', 'active_enrollments', 3, 1),
            $this->counterWidget('lessons_today', 'Занятия сегодня', 'Lessons today', 'Pamokos siandien', 'Lekcje dzisiaj', 'lessons_today', 3, 1),
            $this->counterWidget('scheduled_exams', 'Запланированные экзамены', 'Scheduled exams', 'Suplanuoti egzaminai', 'Zaplanowane egzaminy', 'scheduled_exams', 3, 1),
            $this->counterWidget('paid_revenue', 'Оплаченная выручка', 'Paid revenue', 'Apmoketos pajamos', 'Oplacone przychody', 'paid_revenue_eur', 6, 1),
            $this->counterWidget('pending_documents', 'Ожидают документы', 'Pending documents', 'Laukiami dokumentai', 'Oczekujace dokumenty', 'pending_documents', 3, 1),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function counterWidget(
        string $code,
        string $ru,
        string $en,
        string $lt,
        string $pl,
        string $metricCode,
        int $width,
        int $height,
    ): array {
        $translations = $this->translations($ru, $en, $lt, $pl);

        return [
            'code' => $code,
            'title_translations' => $translations,
            'name_translations' => $translations,
            'description_translations' => $translations,
            'widget_type' => DashboardWidgetType::Counter->value,
            'metric_code' => $metricCode,
            'config' => ['metric' => $metricCode],
            'filters' => [],
            'width' => $width,
            'height' => $height,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function reportDefinitions(): array
    {
        return [
            [
                'code' => 'crm_lead_pipeline',
                'name_translations' => $this->translations('Воронка лидов CRM', 'CRM lead pipeline', 'CRM uzklausu eiga', 'Lejek leadow CRM'),
                'description_translations' => $this->translations('Отчет по лидам, статусам и конверсии.', 'Report for leads, statuses, and conversion.', 'Ataskaita apie uzklausas, busenas ir konversija.', 'Raport leadow, statusow i konwersji.'),
                'report_type' => AnalyticsReportType::Sales,
                'source_model' => 'App\\Models\\MarketingLead',
            ],
            [
                'code' => 'student_enrollment_health',
                'name_translations' => $this->translations('Состояние записей учеников', 'Student enrollment health', 'Mokiniu registraciju bukle', 'Stan zapisow uczniow'),
                'description_translations' => $this->translations('Контроль активных записей, оплат и документов.', 'Control active enrollments, payments, and documents.', 'Aktyviu registraciju, mokejimu ir dokumentu kontrole.', 'Kontrola aktywnych zapisow, platnosci i dokumentow.'),
                'report_type' => AnalyticsReportType::Education,
                'source_model' => 'App\\Models\\Enrollment',
            ],
            [
                'code' => 'schedule_lesson_utilization',
                'name_translations' => $this->translations('Загрузка занятий', 'Lesson utilization', 'Pamoku uzimtumas', 'Wykorzystanie lekcji'),
                'description_translations' => $this->translations('Использование расписания инструкторов и машин.', 'Usage of instructor and vehicle schedules.', 'Instruktoriu ir automobiliu grafiku naudojimas.', 'Wykorzystanie grafikow instruktorow i aut.'),
                'report_type' => AnalyticsReportType::Operational,
                'source_model' => 'App\\Models\\DrivingLesson',
            ],
            [
                'code' => 'finance_payment_summary',
                'name_translations' => $this->translations('Сводка оплат', 'Payment summary', 'Mokejimu suvestine', 'Podsumowanie platnosci'),
                'description_translations' => $this->translations('Финансовый отчет по платежам и статусам.', 'Finance report for payments and statuses.', 'Finansine mokejimu ir busenu ataskaita.', 'Raport finansowy platnosci i statusow.'),
                'report_type' => AnalyticsReportType::Finance,
                'source_model' => 'App\\Models\\Payment',
            ],
            [
                'code' => 'exam_readiness',
                'name_translations' => $this->translations('Готовность к экзаменам', 'Exam readiness', 'Pasiruosimas egzaminams', 'Gotowosc do egzaminow'),
                'description_translations' => $this->translations('Отчет по допускам, попыткам и результатам экзаменов.', 'Report for admissions, attempts, and exam results.', 'Ataskaita apie leidimus, bandymus ir egzamino rezultatus.', 'Raport dopuszczen, prob i wynikow egzaminow.'),
                'report_type' => AnalyticsReportType::Exams,
                'source_model' => 'App\\Models\\ExamAdmission',
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function metricDefinitions(): array
    {
        return [
            [
                'code' => 'open_leads',
                'name_translations' => $this->translations('Открытые лиды', 'Open leads', 'Atviros uzklausos', 'Otwarte leady'),
                'description_translations' => $this->translations('Количество лидов, которые еще в работе.', 'Number of leads still in progress.', 'Dar tvarkomu uzklausu skaicius.', 'Liczba leadow nadal w pracy.'),
                'category' => 'crm',
                'value_type' => 'number',
            ],
            [
                'code' => 'converted_leads',
                'name_translations' => $this->translations('Конвертированные лиды', 'Converted leads', 'Konvertuotos uzklausos', 'Skonwertowane leady'),
                'description_translations' => $this->translations('Лиды, переведенные в учеников.', 'Leads converted into students.', 'Uzklausos paverstos mokiniais.', 'Leady zamienione w uczniow.'),
                'category' => 'crm',
                'value_type' => 'number',
            ],
            [
                'code' => 'active_students',
                'name_translations' => $this->translations('Активные ученики', 'Active students', 'Aktyvus mokiniai', 'Aktywni uczniowie'),
                'description_translations' => $this->translations('Ученики, которые сейчас проходят обучение.', 'Students currently in training.', 'Mokiniai, kurie dabar mokosi.', 'Uczniowie obecnie w szkoleniu.'),
                'category' => 'education',
                'value_type' => 'number',
            ],
            [
                'code' => 'active_enrollments',
                'name_translations' => $this->translations('Активные записи', 'Active enrollments', 'Aktyvios registracijos', 'Aktywne zapisy'),
                'description_translations' => $this->translations('Записи без завершения или архива.', 'Enrollments that are not completed or archived.', 'Registracijos, kurios nebaigtos ir nearchyvuotos.', 'Zapisy niezakonczone ani niearchiwalne.'),
                'category' => 'education',
                'value_type' => 'number',
            ],
            [
                'code' => 'lessons_today',
                'name_translations' => $this->translations('Занятия сегодня', 'Lessons today', 'Pamokos siandien', 'Lekcje dzisiaj'),
                'description_translations' => $this->translations('Количество занятий в сегодняшнем расписании.', 'Number of lessons in today schedule.', 'Pamoku skaicius siandienos grafike.', 'Liczba lekcji w dzisiejszym grafiku.'),
                'category' => 'operations',
                'value_type' => 'number',
            ],
            [
                'code' => 'scheduled_exams',
                'name_translations' => $this->translations('Запланированные экзамены', 'Scheduled exams', 'Suplanuoti egzaminai', 'Zaplanowane egzaminy'),
                'description_translations' => $this->translations('Экзамены, ожидающие назначенной даты.', 'Exams waiting for the scheduled date.', 'Egzaminai, laukiantys suplanuotos datos.', 'Egzaminy oczekujace na wyznaczony termin.'),
                'category' => 'exams',
                'value_type' => 'number',
            ],
            [
                'code' => 'paid_revenue_eur',
                'name_translations' => $this->translations('Оплаченная выручка', 'Paid revenue', 'Apmoketos pajamos', 'Oplacone przychody'),
                'description_translations' => $this->translations('Подтвержденные оплаты в евро.', 'Confirmed payments in euros.', 'Patvirtinti mokejimai eurais.', 'Potwierdzone platnosci w euro.'),
                'category' => 'finance',
                'value_type' => 'money',
                'unit' => 'EUR',
            ],
            [
                'code' => 'pending_documents',
                'name_translations' => $this->translations('Ожидают документы', 'Pending documents', 'Laukiami dokumentai', 'Oczekujace dokumenty'),
                'description_translations' => $this->translations('Активные ученики с недостающими документами.', 'Active students with missing documents.', 'Aktyvus mokiniai su trukstamais dokumentais.', 'Aktywni uczniowie z brakujacymi dokumentami.'),
                'category' => 'operations',
                'value_type' => 'number',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    private function translations(string $ru, string $en, string $lt, string $pl): array
    {
        return [
            'ru' => $ru,
            'en' => $en,
            'lt' => $lt,
            'pl' => $pl,
        ];
    }
}
