# Core Analytics Actions

Block 12 analytics Actions provide the local calculation boundary for owner dashboards, reports, exports, KPI snapshots, cache refreshes, date ranges, and filter normalization.

This is for one driving school company. The Actions do not add tenants, subscriptions, reseller reporting, platform billing, external telemetry, a data warehouse, artificial intelligence forecasting, or third-party business intelligence tooling.

## Action Boundaries

Dashboard Actions:

- `CalculateDashboardSummaryAction` returns local counters for CRM leads, students, enrollments, lessons, exams, payments, documents, and notifications.
- `GenerateOwnerDashboardAction` combines the summary, owner dashboard definition, widget payloads, and a short-lived analytics cache record.
- `GenerateDashboardWidgetDataAction` resolves one widget and returns a structured payload with metric value, filters, module availability, and calculation time.

Report Actions:

- `RunReportAction` creates a report run, checks report permissions, calculates a table-safe summary, and records completion or failure.
- `ExportReportAsCommaSeparatedValuesAction`, `ExportReportAsJsonAction`, and `ExportReportAsSpreadsheetPlaceholderAction` create export metadata records and return generated payload content. They do not call external storage or BI tools.

KPI Actions:

- `CalculateKpiMetricAction` calculates known local school metrics from existing operational tables.
- `CompareKpiTargetAction` compares a metric value to the active target and returns below target, on track, achieved, exceeded, or unknown.
- `CalculateKpiSnapshotAction` calculates a metric, resolves a matching target, and stores the KPI snapshot.

Cache and filter Actions:

- `RefreshAnalyticsCacheAction` updates the legacy analytics cache and the newer cache-entry storage when available.
- `ClearAnalyticsCacheAction` clears cache-entry records by key or tags.
- `BuildAnalyticsDateRangeAction` builds day, week, month, quarter, year, or custom date ranges.
- `ResolveAnalyticsFiltersAction` normalizes period, branch, user, training program, group, instructor, manager, status, source, and report filters.

## Optional Modules

The Actions guard optional module reads with table checks before querying Eloquent models. If a table is not present, the related metric returns zero and the response lists the module as missing.

This allows a local installation to keep analytics Actions available while optional modules are being migrated, disabled, or tested independently.

## Permissions

When a user is provided, Actions check the local Orchid permissions before returning sensitive analytics data:

- dashboard summaries require dashboard view access,
- report runs require report run access plus any permissions stored on the report definition,
- exports require report export access,
- KPI calculations and snapshots require KPI management access.

Internal scheduled jobs may call the same Actions without a user when no interactive authorization context exists.

## Query Rules

Actions use Eloquent models, scopes, and finite status counts. They avoid raw SQL and avoid iterating whole tables to group records.

Dashboard and KPI metrics use indexed status, date, and relationship columns where the module provides them. Report grouping uses known enum/status values rather than cursor-based full-table scans.

## Verification

Focused verification:

```bash
php artisan test --filter=AnalyticsCoreActionsTest
php artisan test --filter=AnalyticsSnapshotsCacheTest
php artisan test --filter=AnalyticsKpiDataModelTest
php artisan test --filter=AnalyticsDashboardDataModelTest
```
