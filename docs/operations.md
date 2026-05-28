# Operations, Schedule, Instructors, and Fleet

Project baseline: follow [`docs/project-specs.md`](project-specs.md) and [`AGENTS.md`](../AGENTS.md). Operations work is Laravel + Orchid + Blade, uses Eloquent only, and keeps all visible admin/public text translatable.

This module is for one local driving school. It does not add tenants, subscription billing, reseller logic, platform-owner dashboards, or multi-company isolation.

## Purpose

The operations foundation connects daily school work around branches, instructors, vehicles, groups, and driving lessons.

Current committed behavior is a listing and directory foundation:

- Orchid lists for instructors, vehicles, lessons, branches, and groups.
- Public directories for instructors and vehicles.
- Dashboard-ready lesson and vehicle context.
- Relationships needed by groups, enrollments, exams, documents, and payments.

Full dispatcher calendar editing, instructor availability planning, vehicle maintenance workflows, and lesson attendance are future modules.

## Storage

Operations reuses the local school tables:

- `branches`: training offices and locations.
- `instructors`: instructor profile, branch, categories, languages, rating, status, and public profile fields.
- `vehicles`: school vehicles, branch, assigned instructor, registration, category, transmission, status, service dates, and public catalog fields.
- `driving_lessons`: scheduled, completed, cancelled, or missed lessons tied to branch, enrollment, instructor, and vehicle.
- `training_groups`: group capacity, membership, schedule pattern, and public visibility handled by the education module.
- `enrollments`: student program records used by schedule and lesson lists.

Students continue to use `student_profiles`, and courses continue to use `training_programs`.

## Admin Screens

Current Orchid routes:

- `platform.operations.branches`
- `platform.operations.instructors`
- `platform.operations.groups`
- `platform.schedule.lessons`
- `platform.fleet.vehicles`

Current permissions:

- `platform.operations.branches`
- `platform.operations.instructors`
- `platform.operations.groups`
- `platform.schedule.lessons`
- `platform.fleet.vehicles`

The committed screens prepare paginated data in `query()`, eager load displayed relationships, and use translated labels through `tkey()` and localized enum labels.

## Public Directories

Public website routes currently expose:

- `site.instructors`
- `site.fleet`

The instructor directory shows active instructors with branch, vehicles, review count, rating, categories, languages, and public profile fields.

The fleet directory shows vehicles with branch and instructor context, category, transmission, registration, status, and public catalog fields.

## Data Flow

Controllers call Actions to prepare public data:

- `GetInstructorDirectoryAction`
- `GetFleetDirectoryAction`

Orchid list screens read through model scopes:

- `Instructor::forAdminList()`
- `Instructor::forPublicDirectory()`
- `Vehicle::forFleetList()`
- `DrivingLesson::forScheduleList()`
- `DrivingLesson::upcoming()`

Schedule and dashboard views eager load branch, instructor, vehicle, enrollment, student, and program context before rendering.

## Query Notes

The current committed lists use:

- explicit `select()` scopes for list payload control,
- `with()` for displayed relationships,
- `withCount()` for instructor group, lesson, vehicle, or review counts,
- `simplePaginate()` for admin and public lists,
- `limit()` for dashboard upcoming lessons.

Do not move relationship lookups into Blade or table render loops.

## Tests

Relevant existing verification:

- `DrivingSchoolPlatformTest`
- `EducationGroupBlockTest`
- `LandingPageTest`

Future schedule, fleet, and instructor CRUD should add focused Feature tests for permissions, validation, translations, eager loading, and status behavior.

## TODOs

- Add instructor create/edit workflow and availability planning.
- Add vehicle create/edit workflow and maintenance/inspection reminders.
- Add lesson create/edit workflow and recurring schedule generation from group patterns.
- Add attendance and completion tracking for theory and practical lessons.
- Add richer operational dashboards after reporting snapshots exist.
