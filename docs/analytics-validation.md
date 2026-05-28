# Analytics Validation Rules

Project baseline: follow [`docs/analytics.md`](analytics.md) and [`AGENTS.md`](../AGENTS.md). Analytics validation is for one local driving school company. It must not add tenant, subscription, reseller, platform-owner, multi-company, or external telemetry concepts.

## Purpose

The analytics validation layer protects owner dashboard, report, KPI, snapshot, and cache inputs before Actions persist records or generate payloads. Rules use Laravel validation classes and return translated messages through `analytics.validation.*` keys.

## Covered Rules

- Active report and KPI checks ensure selected records exist and are active.
- Report filter, filter value, and column checks keep report input inside the report definition or shared local analytics fields.
- Report export checks allow only completed runs, active reports, approved local export formats, and users with export access.
- KPI period, target value, and target uniqueness checks protect period-based target entry.
- Dashboard widget configuration checks reject malformed widget settings and SaaS-style scope keys.
- Analytics date range, cache key, permission, and module availability checks validate shared dashboard/report inputs.

## Translation Keys

Every validation failure must use one of the analytics validation translation keys seeded for Russian, English, Lithuanian, and Polish operators. Do not return hardcoded Russian or English text from a Rule.

Required keys include:

- `analytics.validation.report_not_active`
- `analytics.validation.invalid_filter`
- `analytics.validation.export_not_allowed`
- `analytics.validation.invalid_format`
- `analytics.validation.kpi_not_active`
- `analytics.validation.invalid_period`
- `analytics.validation.invalid_target_value`
- `analytics.validation.duplicate_kpi_target`
- `analytics.validation.invalid_widget_config`
- `analytics.validation.invalid_date_range`
- `analytics.validation.permission_denied`
- `analytics.validation.invalid_cache_key`
- `analytics.validation.module_not_available`
- `analytics.validation.column_not_allowed`
- `analytics.validation.filter_value_not_allowed`

## Local Module Availability

Module availability is checked against local operational tables such as leads, students, enrollments, groups, driving lessons, documents, payments, exams, notifications, reports, KPI records, dashboards, snapshots, and cache records. Missing optional module tables fail validation without crashing the request.

## Testing

Focused verification:

```bash
php artisan test --filter=AnalyticsValidationRulesTest
```

The tests cover valid data, invalid data, and translated messages for every analytics validation Rule.
