# Dashboard and Analytics Foundation

Project baseline: follow [`docs/project-specs.md`](project-specs.md) and [`AGENTS.md`](../AGENTS.md). Analytics work is Laravel + Orchid + Blade, uses Eloquent only, and keeps all visible admin/public text translatable.

This module is for one local driving school. It does not add tenants, subscription billing, reseller logic, platform-owner dashboards, or multi-company reporting.

## Purpose

The current analytics foundation is the local dashboard and lightweight operational counters. It gives staff a quick view of school activity without building a full reporting warehouse yet.

Current dashboard data includes:

- active student count,
- active enrollment count,
- today's scheduled lessons,
- scheduled exams,
- active groups,
- open CRM leads,
- paid revenue total,
- upcoming lesson list with student, program, branch, instructor, and vehicle context.

Full historical analytics, chart widgets, cohort reporting, revenue forecasting, instructor utilization, and exportable management reports are future work.

## Data Sources

Dashboard metrics are derived from existing local school tables:

- `student_profiles`
- `enrollments`
- `driving_lessons`
- `exams`
- `training_groups`
- `marketing_leads`
- `payments`

The upcoming lesson list uses lesson records with eager loaded branch, instructor, vehicle, enrollment, student, and program context.

## Admin Surface

The main platform dashboard reads prepared data from the dashboard Action. The Action is responsible for query shape, caching, limits, and eager loading.

Do not calculate dashboard metrics directly in Blade.

## Query Notes

The current dashboard behavior:

- caches the main counters briefly,
- limits upcoming lessons,
- eager loads all displayed relationships,
- uses model scopes where they exist,
- keeps the dashboard local to one driving school.

Future analytics that touches large data should use scheduled cache refreshes, model scopes, and pre-aggregated tables or snapshots instead of repeated live aggregates in widgets.

## Tests

Relevant existing verification:

- `DrivingSchoolPlatformTest`
- `EducationGroupBlockTest`
- `ExamBlockFoundationTest`

Future analytics work should add tests for metric permissions, cache invalidation, date boundaries, locale-safe labels, and query counts.

## TODOs

- Add dashboard widgets backed by cached model aggregates.
- Add historical snapshots for revenue, leads, enrollments, lessons, and exams.
- Add instructor and vehicle utilization reports.
- Add group capacity and student progress reporting.
- Add CSV exports only through authorized Actions.
