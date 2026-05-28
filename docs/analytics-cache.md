# Analytics Snapshots And Cache

This analytics storage belongs to one local driving school company. It does not add tenants, subscription analytics, reseller dashboards, platform billing, external telemetry, or multi-company reporting.

## Purpose

Analytics snapshots and cache entries give Block 12 a stable place to store precomputed dashboard and report payloads before scheduled refresh jobs and editable reporting screens are added.

Use this layer for:

- owner dashboard snapshots,
- sales, finance, education, driving, exam, document, and notification summaries,
- reusable dashboard payloads,
- report payloads that are expensive enough to cache,
- short-lived computed data that should not be recalculated inside Orchid screens or Blade views.

## Tables

`analytics_snapshots` stores calculated period payloads. Each row has a UUID, snapshot type, period type, period start and end, optional branch and user scope, JSON data, calculation time, metadata, and timestamps.

Supported snapshot types:

- owner_dashboard
- sales_summary
- finance_summary
- education_summary
- driving_summary
- exam_summary
- document_summary
- notification_summary

`analytics_cache_entries` stores reusable cached payloads by cache key. Each row has a unique cache key, JSON data, optional tags, expiration time, calculation time, and timestamps.

The earlier `analytics_cache` table remains for compatibility with the first owner dashboard foundation. New action-based cache work should use `analytics_cache_entries`.

## Actions

`StoreAnalyticsSnapshotAction` writes a new snapshot for a type, period, optional branch, optional user, payload, and metadata.

`PutAnalyticsCacheEntryAction` stores or replaces a cache entry by key.

`GetAnalyticsCacheEntryAction` returns only fresh cache entries. Expired entries are ignored by the query.

`ClearAnalyticsCacheAction` clears all entries, entries matching a key, or entries matching one of the supplied tags.

## Validation

`AnalyticsCacheKeyRule` accepts stable lowercase analytics keys made from letters, numbers, dots, underscores, dashes, and colons.

`AnalyticsDateRangeRule` continues to validate that a period start is not after its end and that the configured range is not too large.

## Data Boundaries

Snapshots and cache entries should contain operational summaries, not private raw records. Do not store credentials, secrets, full student/customer details, third-party telemetry, tenant dimensions, company dimensions, reseller dimensions, subscription dimensions, or platform-owner dimensions.

Source data must come from local school modules such as CRM leads, students, enrollments, groups, schedule, driving lessons, documents, finance, exams, notifications, and staff records.

## Verification

Focused checks:

```bash
php artisan test --filter=AnalyticsSnapshotsCacheTest
php artisan test --filter=AnalyticsBlockFoundationTest
```
