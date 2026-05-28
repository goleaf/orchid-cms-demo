# AGENTS.md - Laravel Orchid Driving School OS

## Project Contract

This repository is a local operating system for one driving school company. It is not a SaaS platform.

- Framework: Laravel 12.x on PHP 8.2+
- Admin panel: Orchid Platform 14.x
- Frontend: server-rendered Laravel Blade
- Query layer: Eloquent models, relationships, scopes, and Actions
- Product scope: public website, CRM leads, students, groups, schedule, instructors, vehicles, documents, payments, analytics, and multilingual content
- Highest admin role: local driving-school superadmin, not a platform owner

Never add tenants, tenant isolation, subscription billing, reseller logic, platform-owner dashboards, or multi-company behavior unless the user explicitly changes the product direction.

## Default Documentation Connection

The local docs system is connected by default for Codex sessions started in this repository:

- Session and prompt hooks load compact project memory and repository skill inventory.
- The `orchid-platform` skill is discovered from `.agents/skills/orchid-platform`.
- Official Orchid docs are mirrored locally in `.agents/skills/orchid-platform/references/docs`.
- Context7 fallback library id is `/orchidsoftware/platform`.

Default connection means the skill inventory is available automatically. It does not dump the full Orchid documentation into every prompt. Before editing Orchid code, search the local docs and vendor source:

```bash
rg -n "Layout::table|TD::make|ModalToggle|permission|Screen" .agents/skills/orchid-platform/references/docs
rg -n "class Screen|class TD|function permission|function commandBar" vendor/orchid/platform/src vendor/orchid/platform/stubs
```

Refresh the local docs and skill inventory with:

```bash
.agents/skills/orchid-platform/scripts/sync_orchid_docs.sh
python3 .agents/skills/codebase-self-learning/scripts/discover_skills.py --json
```

## Automatic Changelog And Commits

The Stop hook must automatically update `changelog.md`, generate a Conventional Commit message through `codex exec`, commit all staged/current changes, and push to the upstream when one is configured.

`changelog.md` entries must be written in plain human language. Do not include programming code, class names, method names, file names, raw URLs, markdown links, or package/internal tool names unless the change is only about project automation.

Disable only for a deliberate local run:

```bash
CODEX_AUTO_PUSH_DISABLED=1 bash .codex/hooks/auto-commit-push.sh
```

## Hard Rules

- Never write raw SQL strings in application code.
- Never use `DB::select()`, `DB::statement()`, or `DB::raw()` outside a model-owned internal scope.
- Never query inside Blade views, table render loops, or repeated conditionals.
- Never call `count()`, `sum()`, or other aggregates inside a loop when it can be eager loaded or precomputed.
- Never use `Model::all()` without a tight scope, limit, or pagination.
- Never duplicate business logic across controllers or Orchid screens; extract to Actions, Services, model scopes, or policies.
- Never put business logic in Blade templates or directly in Orchid layouts.
- Never hardcode visible UI text in Orchid screens, menus, tables, buttons, modals, notifications, validations, public pages, documents, or seeded interface content.
- Never store secrets, API keys, tokens, credentials, cookies, private student/customer data, or full private user prompts in code, docs, or memory.

## Architecture Rules

- Controllers and Orchid screens stay thin.
- Write operations go through Form Requests and Actions.
- Authorization belongs in policies, permissions, or request authorization, not scattered inline checks.
- Shared access is centralized through existing permission helpers such as `App\Support\Access\SuperadminPermissions`.
- Public website courses reuse `training_programs`; do not create duplicate course tables.
- CRM leads reuse `marketing_leads`; `App\Models\Lead` is a compatibility model.
- Students reuse `student_profiles`; `App\Models\Student` is a compatibility model.
- Enrollments reuse `enrollments`; `App\Models\StudentEnrollment` is a compatibility model.
- Training groups reuse `training_groups`; group membership history uses `training_group_memberships`.

## Eloquent And Performance

- Use `with()`, `loadMissing()`, `withCount()`, `withExists()`, and scoped aggregates instead of view-time queries.
- Define reusable filtering in model scopes or Action query builders.
- Use explicit `select()` in high-traffic scopes where payload matters.
- Use pagination or cursor/lazy iteration for large result sets.
- Add indexes in migrations for frequently filtered, sorted, or related columns.
- Use factories for seeders and tests unless updating stable system dictionaries with idempotent records.

## Orchid Rules

- Before Orchid implementation, inspect local Orchid docs, vendor signatures, and existing `app/Orchid` patterns.
- Protected screens must expose `permission()` and match seeded permissions.
- Screen `query()` methods prepare all data needed by layouts.
- Tables use `Layout::table()` or dedicated table layouts with intentional `TD::make()` sorting, filtering, and visibility.
- Destructive actions use confirmation.
- User feedback uses Orchid Toast/Alert/Notification patterns already present in the project.
- Routes stay in `routes/platform.php` as named `Route::screen(...)` entries consistent with existing prefixes and breadcrumbs.

## Blade Rules

- Blade receives prepared data from controllers or view models.
- Use components/partials for reusable UI.
- Use `@forelse` for rendered lists with empty states.
- Use CSRF and method spoofing for write forms.
- Do not query models or relationships from Blade.

## Localization Rules

- All visible UI labels must be translatable through `tkey()`, Laravel translation keys, or existing localization helpers.
- Business content that users manage should support multilingual values when needed.
- Superadmin must be able to manage languages and translations from Orchid.
- Dictionary labels should prefer translated fields, then fallback to stable names or codes.

## Verification

Use focused tests first, then full tests when the change touches shared admin behavior:

```bash
php artisan test --filter=DrivingSchoolPlatformTest
php artisan test --filter=SystemLocalizationTest
php artisan test
```

For Orchid work, also verify local docs and package version:

```bash
composer show orchid/platform
python3 .agents/skills/codebase-self-learning/scripts/discover_skills.py --json --no-write
```

## Response Format For Code Tasks

When writing, reviewing, or refactoring code, structure the response as:

1. PROBLEM - what is wrong or what is being built.
2. SOLUTION - what was implemented and where.
3. QUERY DELTA - before/after query impact when query-related.
4. REUSABLE SNIPPET - extracted scope, Action, component, trait, or command when applicable.
5. BLADE USAGE - controller to view data flow when Blade-related.
6. ORCHID INTEGRATION - screen, layout, route, permission, or menu integration when applicable.
7. TESTS - focused and full verification run.
8. CAVEATS - remaining risks, version notes, indexes, cache invalidation, or docs/MCP checks.
