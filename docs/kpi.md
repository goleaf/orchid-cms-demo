# Key Performance Indicator Data Model

This KPI foundation belongs to one local driving school company. It does not add tenants, subscription analytics, reseller dashboards, platform billing, platform-owner reporting, or multi-company reporting.

## Purpose

The KPI data model gives Block 12 stable storage for owner dashboard metrics, operational targets, and calculated snapshots before editable KPI administration screens and scheduled refresh jobs are added. It supports:

- reusable KPI metric definitions,
- period targets for the local school,
- optional branch and staff ownership,
- calculated KPI snapshots,
- translated metric labels,
- compatibility with the first analytics dashboard foundation.

## Tables

`kpi_metrics` stores metric definitions. Each metric has a UUID, unique code, translated name and description, metric group, unit, calculation type, active/system flags, sort order, creator/updater links, soft deletes, and timestamps.

Supported metric groups:

- sales
- finance
- students
- education
- lessons
- driving
- documents
- exams
- notifications
- staff

Supported units:

- count
- percent
- money
- hours
- days
- ratio

Older category, value type, calculation, source, and settings fields remain available so the first analytics dashboard and demo seed data continue to work while the richer KPI model is introduced.

`kpi_targets` stores expected values for a metric and period. A target belongs to a KPI metric and may be scoped to a branch and user. It stores period type, period start and end, target value, warning threshold, success threshold, creator/updater links, soft deletes, and timestamps.

`kpi_snapshots` stores calculated values for a period. A snapshot belongs to a KPI metric and may be scoped to a branch and user. It stores period type, period start and end, value, target value, status, calculation time, metadata, and timestamps.

Supported period types:

- day
- week
- month
- quarter
- year
- custom

Supported snapshot statuses:

- below_target
- on_track
- achieved
- exceeded
- unknown

## Model Behavior

KPI metrics, targets, and snapshots use factories for tests and seed data. Models generate UUIDs when missing, expose relationships, and include scopes for active metrics, metric groups, system metrics, metrics, branches, users, period types, and latest snapshots.

Translated metric names use `name_translations`. Descriptions use `description_translations`. Metrics expose helpers for display name, display description, group fallback, and calculation type fallback.

Targets expose helpers for period type, period start, period end, warning threshold, and success threshold. Snapshots expose helpers for period type, period start, and successful status.

## Data Boundaries

KPIs must reuse existing local school modules:

- CRM leads,
- students and enrollments,
- education groups,
- schedule and driving lessons,
- documents,
- finance and payments,
- exams,
- notifications,
- staff records.

Do not create a warehouse, external analytics service, telemetry pipeline, tenant dimension, company dimension, reseller dimension, subscription dimension, or platform-owner dimension.

## Verification

Relevant focused checks:

```bash
php artisan test --filter=AnalyticsKpiDataModelTest
php artisan test --filter=AnalyticsBlockFoundationTest
```

Run broader platform and localization checks when editable Orchid KPI screens or new visible labels are added.
