# Analytics Form Requests

Block 12 analytics Form Requests validate owner dashboard, report, KPI, cache, and user preference inputs before Actions or Orchid screens use them.

This is local reporting for one driving school company. Requests must not add tenant, subscription, reseller, platform-owner, multi-company, external telemetry, or business intelligence platform concepts.

## Request Classes

Dashboard requests:

- `StoreAnalyticsDashboardRequest`
- `UpdateAnalyticsDashboardRequest`
- `StoreDashboardWidgetRequest`
- `UpdateDashboardWidgetRequest`
- `UpdateUserDashboardPreferenceRequest`

Report requests:

- `StoreReportDefinitionRequest`
- `UpdateReportDefinitionRequest`
- `RunReportRequest`
- `ExportReportRequest`

KPI and cache requests:

- `StoreKpiMetricRequest`
- `UpdateKpiMetricRequest`
- `StoreKpiTargetRequest`
- `UpdateKpiTargetRequest`
- `RefreshAnalyticsCacheRequest`

Legacy-compatible base requests remain available where existing screens already depend on them.

## Validation Boundaries

Requests use analytics validation Rules for active report definitions, export formats, report export permission, report filters, report columns, active KPI metrics, KPI periods, KPI target values, KPI target uniqueness, dashboard widget configuration, date ranges, cache keys, module availability, and analytics permissions.

Shared request validation supports:

- period type, period start, and period end filters,
- branch, user, instructor, manager, training program, and training group filters,
- report status, source, group, and column filters,
- local report export formats,
- dashboard widget configuration and filters,
- cache key, tags, refresh lifetime, and optional module refresh checks.

## Authorization

Requests check local Orchid permissions through the authenticated user:

- dashboard setup and user dashboard preferences use dashboard preference management access,
- report definitions use report management access,
- report runs use report run access,
- report exports use report export access,
- KPI metrics use KPI management access,
- KPI targets use KPI target management access,
- cache refresh validation uses analytics cache access.

Rules also validate sensitive action permissions where the validated payload needs an explicit permission check.

## Translations

Request messages call analytics validation translation keys. Field labels use `validation.attributes.analytics.*` keys seeded for Russian, English, Lithuanian, and Polish.

Do not return hardcoded Russian or English validation text from analytics requests or rules.

## Testing

Focused verification:

```bash
php artisan test --filter=AnalyticsFormRequestsTest
php artisan test --filter=AnalyticsValidationRulesTest
```
