---
name: orchid-platform
description: Use when working on Laravel Orchid Platform screens, layouts, menus, permissions, filters, fields, tables, modals, CRUD resources, Orchid routes, or when local Orchid documentation is needed before code changes.
---

# Orchid Platform

Use this skill before editing any Orchid admin code in this repository.

## Default Connection

The repository hook layer loads compact skill inventory by default:

- `SessionStart` includes repository skills.
- `UserPromptSubmit` includes repository skills on every prompt.
- Full Orchid docs stay in `references/docs` and are searched only when needed.

If this skill does not appear in prompt context, refresh discovery:

```bash
python3 .agents/skills/codebase-self-learning/scripts/discover_skills.py --json
```

## Local-First Workflow

1. Confirm the installed package version:
   ```bash
   composer show orchid/platform
   ```
2. Search the local Orchid documentation mirror before relying on memory:
   ```bash
   rg -n "Screen|Layout::table|ModalToggle|permission|TD::make" .agents/skills/orchid-platform/references/docs
   ```
3. Inspect the installed vendor source when method signatures or behavior matter:
   ```bash
   rg -n "function (query|commandBar|layout|permission)|class TD|class Screen|class ModalToggle" vendor/orchid/platform/src vendor/orchid/platform/stubs
   ```
4. Use Context7 only as a fallback or cross-check, with library id `/orchidsoftware/platform`.
5. Check existing app patterns in `app/Orchid`, `routes/platform.php`, Actions, Form Requests, and tests before adding new structure.

## Repository Rules

- This is a local driving-school system, not SaaS. Do not add tenant, subscription, reseller, or platform-owner logic.
- Visible UI labels in Orchid screens, menus, table columns, buttons, modal text, alerts, validations, and notifications must be translatable through `tkey()`, translation keys, or existing localization helpers.
- Keep business logic out of Orchid screens and layouts. Use `App\Actions`, Form Requests, model scopes, services, policies, and tests.
- Do not write raw SQL or query in Blade/views/render loops. Use Eloquent, eager loading, `withCount`, `withExists`, pagination, and scoped queries.
- Prefer existing route names, permission names, screen namespaces, and admin menu structure.

## Orchid Implementation Checklist

- `Screen::query()` returns fully prepared data with eager-loaded relations and no view-time query needs.
- `permission()` is present on protected screens and matches seeded permissions.
- `commandBar()` actions use icons, confirmations for destructive work, and call screen methods that delegate to Actions/Form Requests where practical.
- Tables use `Layout::table()` or dedicated `Table` layouts; columns use `TD::make()` with intentional sorting, filtering, visibility, and no accidental extra queries in render callbacks.
- Forms use `Layout::rows()`, `Layout::columns()`, tabs, or modals according to existing app patterns; validation lives in Form Requests.
- Routes stay in `routes/platform.php` as named `Route::screen(...)` entries consistent with existing prefixes.
- Tests cover the HTTP/admin behavior, authorization, and any query-sensitive data flow touched by the change.

## Documentation Map

Local docs are synced from the official Orchid website source:

- `references/docs/screens.md` - screen lifecycle, routing, actions.
- `references/docs/field.md` and `references/docs/rows.md` - fields and row forms.
- `references/docs/table.md` and `references/docs/cell-types.md` - table layouts and columns.
- `references/docs/modals.md` and `references/docs/listener.md` - modal and async UI flows.
- `references/docs/menu.md` and `references/docs/permissions.md` - navigation and access.
- `references/docs/filters.md` - Eloquent filters, sorting, and filtering.
- `references/docs/packages/crud.md` - Orchid CRUD package reference.
- `references/SOURCE.md` - sync source, commit, and update command.

Refresh the local mirror with:

```bash
.agents/skills/orchid-platform/scripts/sync_orchid_docs.sh
```

After refreshing, run repository skill discovery:

```bash
python3 .agents/skills/codebase-self-learning/scripts/discover_skills.py --json
```
