# Analytics Report Data Model

This report foundation belongs to one local driving school company. It does not add tenants, subscription analytics, reseller dashboards, platform billing, platform-owner reporting, or multi-company reporting.

## Purpose

The report data model gives Block 12 stable storage for operational reports before editable Orchid report-builder screens and file generation are added. It supports:

- reusable report definitions,
- report run history,
- report export records,
- translated report labels,
- permission metadata for future report screens,
- local dashboard and owner reporting workflows.

## Tables

`report_definitions` stores reusable reports. Each record has a UUID, unique code, translated name and description, local report group, data source, filter schema, column schema, permissions, active/system flags, sort order, creator/updater links, soft deletes, and timestamps.

Supported report groups:

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
- system

Older report type, source model, default filter, column configuration, and schedule fields remain available so the first owner dashboard and report actions continue to work while the richer report model is introduced.

`report_runs` stores each report execution. A run belongs to a report definition and may belong to the user who ran it. It records status, filters, start and finish times, row count, error message, metadata, and timestamps.

Supported run statuses:

- pending
- running
- completed
- failed
- cancelled

`report_exports` stores export attempts and generated export metadata. An export belongs to a report run and keeps format, disk, path, filename, MIME type, byte size, exporting user, export time, metadata, and timestamps. Older definition, status, file name, row count, filter, expiration, and creator fields remain available for compatibility with the first export action.

Supported export formats:

- comma_separated_values
- spreadsheet_placeholder
- json

The spreadsheet format is intentionally a placeholder record type. It does not add spreadsheet generation yet.

## Model Behavior

Report definitions, runs, and exports use factories for tests and demo setup. Models generate UUIDs when missing, expose reusable relationships, and include scopes for active definitions, report groups, system definitions, report definitions, users, statuses, runs, formats, and recent exports.

Translated report names use `name_translations`. Descriptions use `description_translations`. Definitions expose helper methods for display name, display description, data source fallback, and required permissions.

Runs expose helpers for finished state, failed state, and duration. Exports expose helpers for display filename and human-readable size.

## Data Sources

Reports must reuse existing local school modules:

- CRM leads,
- students and enrollments,
- education groups,
- schedule and driving lessons,
- documents,
- finance and payments,
- exams,
- notifications,
- staff-related records.

Do not create a data warehouse, external business intelligence connector, third-party telemetry, artificial intelligence forecasting, tenant dimension, company dimension, reseller dimension, or platform-owner dimension.

## Validation And Actions

The existing analytics Actions and Form Requests continue to be the write boundary for report definitions, report runs, and report exports. Report definition validation accepts the new group, data source, filter schema, column schema, and permission metadata while preserving older report type fields for current dashboard compatibility.

Future export generation should stay behind Actions and Jobs. Orchid screens should prepare all data before rendering and must not query from table render loops or Blade views.

## Verification

Relevant focused checks:

```bash
php artisan test --filter=AnalyticsReportDataModelTest
php artisan test --filter=AnalyticsBlockFoundationTest
```

Run broader platform, localization, and permission checks when adding editable Orchid screens or new visible labels.
