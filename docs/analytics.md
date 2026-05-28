# Reports, Analytics, Dashboard, and KPI Foundation

Project baseline: follow [`docs/project-specs.md`](project-specs.md) and [`AGENTS.md`](../AGENTS.md). Analytics work is Laravel + Orchid + Blade, uses Eloquent only, and keeps all visible admin text translatable.

This module is for one local driving school. It does not add tenants, subscriptions, reseller reporting, platform-owner dashboards, multi-company reporting, third-party telemetry, an external business intelligence platform, a data warehouse, or artificial intelligence forecasting.

## Purpose

Block 12 adds the foundation for local owner reporting and operational analytics:

- owner dashboard widgets,
- saved report definitions,
- report run history,
- report export records,
- key performance indicator metrics,
- key performance indicator targets,
- daily or period snapshots,
- analytics cache records,
- per-user dashboard preferences.

The first admin surface is a read-only owner dashboard in Orchid. It shows live counters, seeded widget definitions, saved reports, recent runs, and KPI snapshots.

Dashboard definitions, widget definitions, and per-user layout preferences are described in [`docs/analytics-dashboard.md`](analytics-dashboard.md). Core dashboard, report, export, KPI, cache, date-range, and filter Actions are described in [`docs/analytics-actions.md`](analytics-actions.md). Analytics Form Requests are described in [`docs/analytics-requests.md`](analytics-requests.md). Analytics validation Rules and translation keys are described in [`docs/analytics-validation.md`](analytics-validation.md).

## Data Sources

Dashboard and report data comes from existing local school modules:

- CRM leads,
- students and enrollments,
- groups and schedules,
- driving lessons,
- student documents,
- payments,
- exam admissions, sessions, and attempts,
- notification delivery records.

The foundation intentionally reuses existing tables and relationships. It does not create a separate warehouse or duplicate operational records.

## Reporting Model

Report definitions describe reusable local reports such as lead pipeline, enrollment health, lesson utilization, payment summary, and exam readiness.

Report runs store the filters, period, status, summary, row count, start time, finish time, and result payload. Core Actions record completed or failed runs and create CSV, JSON, and spreadsheet-placeholder export records; later work can attach generated files behind the same Action boundary.

## KPI Model

KPI metrics define what the school tracks. KPI targets define expected values by period and optional local scope such as branch, training program, or group. KPI snapshots store calculated values, target values, status, source payload, and calculation time.

Supported target directions are increase, decrease, and maintain. Snapshot status is derived as below target, on track, achieved, exceeded, or unknown.

## Analytics Cache

Analytics cache records store reusable dashboard or report payloads with tags, refresh time, and expiration time. Long-running or expensive future widgets should refresh this cache through Actions or scheduled jobs instead of recalculating inside Orchid screens.

## Admin Surface

The Orchid owner dashboard:

- prepares all data in an Action before rendering,
- eager loads report and KPI relationships used by table rows,
- displays metric counters through Orchid metrics,
- exposes only local driving-school analytics,
- is protected by analytics permissions.

Do not calculate analytics metrics in Blade views, table render loops, or repeated conditionals.

## Validation And Permissions

The foundation includes dedicated Form Requests and custom Rules for:

- report definitions,
- report runs,
- report exports,
- KPI metrics,
- KPI targets,
- dashboard preferences,
- analytics codes,
- active reports,
- active KPI metrics,
- date ranges,
- dashboard widget codes,
- report filter schemas,
- report export formats,
- KPI target values and uniqueness,
- dashboard widget configuration,
- analytics module availability.

Analytics permissions are part of the local superadmin permission set and are seeded with multilingual labels.

## Tests

Relevant verification:

- `AnalyticsBlockFoundationTest`
- `AnalyticsCoreActionsTest`
- `AnalyticsFormRequestsTest`
- `AnalyticsValidationRulesTest`
- `DrivingSchoolPlatformTest`
- `SystemLocalizationTest`
- `SuperadminRoleTest`

Future analytics work should add focused tests for generated export files, scheduled cache refreshes, query-count regressions, and larger date ranges.

## Next Work

- Add editable Orchid screens for managing report definitions, KPI metrics, and KPI targets.
- Add editable Orchid screens for managing dashboard definitions and widget layouts.
- Add authorized export file generation through Actions.
- Add scheduled KPI snapshot refreshes.
- Add instructor, vehicle, group capacity, and student progress reports.
- Add chart widgets backed by cached snapshots.
