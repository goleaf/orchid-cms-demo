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

Report runs store the filters, period, status, summary, row count, start time, finish time, and result payload. Report exports store export format, status, file name, row count, filters, and expiration metadata. The current foundation records exports; later work can attach generated files behind the same Action boundary.

## KPI Model

KPI metrics define what the school tracks. KPI targets define expected values by period and optional local scope such as branch, training program, or group. KPI snapshots store calculated values, target values, status, source payload, and calculation time.

Supported target directions are increase, decrease, and maintain. Snapshot status is derived as on track, warning, off track, or neutral.

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
- dashboard widget codes.

Analytics permissions are part of the local superadmin permission set and are seeded with multilingual labels.

## Tests

Relevant verification:

- `AnalyticsBlockFoundationTest`
- `DrivingSchoolPlatformTest`
- `SystemLocalizationTest`
- `SuperadminRoleTest`

Future analytics work should add focused tests for generated export files, scheduled cache refreshes, query-count regressions, and larger date ranges.

## Next Work

- Add editable Orchid screens for managing report definitions, KPI metrics, and KPI targets.
- Add authorized export file generation through Actions.
- Add scheduled KPI snapshot refreshes.
- Add instructor, vehicle, group capacity, and student progress reports.
- Add chart widgets backed by cached snapshots.
