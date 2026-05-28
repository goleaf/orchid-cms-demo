# Project Specs

This file is the shared baseline for the Markdown docs in this repository. Module docs should link here instead of repeating every global rule.

This project is a Laravel Orchid operating system for one local driving school company. It is not a SaaS product.

## Product Direction

- Public website for courses, prices, branches, reviews, FAQ, SEO pages, and lead forms.
- CRM for website leads, manual leads, statuses, sources, tags, tasks, notes, calls, and pipeline work.
- Student and enrollment management connected to CRM conversion.
- Training groups, schedules, instructors, vehicles, documents, payments, and analytics as local back-office modules.
- Multilingual UI and editable business content as a foundation.

## Module Documentation

- Public website: [`public-website.md`](public-website.md) and [`public-website-foundation.md`](public-website-foundation.md)
- CRM leads: [`crm-leads.md`](crm-leads.md) and [`crm-block-2.md`](crm-block-2.md)
- Students and enrollments: [`students.md`](students.md)
- Lead conversion: [`lead-to-student-conversion.md`](lead-to-student-conversion.md)
- Training groups and education structure: [`training-groups.md`](training-groups.md)
- Operations, schedule, instructors, and fleet: [`operations.md`](operations.md)
- Exams: [`exams.md`](exams.md)
- Documents: [`documents.md`](documents.md)
- Payments: [`payments.md`](payments.md)
- Communications and reminders: [`communications.md`](communications.md)
- Dashboard and analytics foundation: [`analytics.md`](analytics.md)
- Local Orchid workflow: [`orchid-local-documentation.md`](orchid-local-documentation.md)
- Codex automation: [`codex-automation.md`](codex-automation.md)

## Stack

- Laravel 12.x
- Orchid Platform 14.x
- Server-rendered Blade
- Eloquent-only query layer
- Form Requests, Actions, model scopes, policies, and tests for application behavior

## Non-Goals

Do not add these unless the user explicitly changes direction:

- SaaS tenants or tenant isolation
- subscription billing
- reseller flows
- platform-owner dashboards
- multi-company administration
- duplicate tables for concepts already mapped to current project tables

## Local Documentation Defaults

Codex sessions in this repository load compact self-learning memory and repository skill inventory by default. The `orchid-platform` skill is discovered locally and points to the mirrored official Orchid documentation.

The local docs are intentionally searchable instead of injected in full:

```bash
rg -n "Layout::table|TD::make|ModalToggle|permission|Screen" .agents/skills/orchid-platform/references/docs
rg -n "class Screen|class TD|function permission|function commandBar" vendor/orchid/platform/src vendor/orchid/platform/stubs
```

Use Context7 with `/orchidsoftware/platform` only when the local mirror or vendor source is incomplete or stale.

## Automation Defaults

Codex Stop automation updates `changelog.md`, generates a Conventional Commit message from the staged diff, commits all repository changes, and pushes when an upstream is configured.

Changelog entries must be plain human language for a project owner or operator. They must not include programming code, raw file paths, class names, method names, raw URLs, or markdown links.

## Reuse Decisions

- Public courses reuse `training_programs` through course-facing compatibility models.
- Website and CRM leads reuse `marketing_leads`.
- Testimonials reuse `student_reviews`.
- Students reuse `student_profiles`.
- Student enrollments reuse `enrollments`.
- Training groups reuse `training_groups` and group memberships use `training_group_memberships`.
- Superadmin permissions are centralized through `App\Support\Access\SuperadminPermissions`.

## Quality Rules

- No raw SQL in application code.
- No queries in Blade or repeated render loops.
- No aggregates inside loops when eager loading or precomputed aggregates can be used.
- No hardcoded visible UI text in Orchid, Blade, notifications, validations, documents, or seeded interface content.
- Use `tkey()`, translation keys, and existing localization helpers for visible labels.
- Keep controllers and Orchid screens thin.
- Put write behavior in Actions and validation in Form Requests.
- Protect admin behavior with permissions and tests.

## Verification Baseline

Use targeted tests for the touched module first, then full tests when shared behavior changes:

```bash
php artisan test --filter=DrivingSchoolPlatformTest
php artisan test --filter=SystemLocalizationTest
php artisan test
```
