# Orchid Local Documentation Workflow

This repository has a local Orchid Platform skill and documentation mirror for first-pass admin code quality.

## Primary Sources

1. Local documentation mirror:
   `.agents/skills/orchid-platform/references/docs`
2. Installed package source and stubs:
   `vendor/orchid/platform/src`
   `vendor/orchid/platform/stubs`
3. Context7 fallback:
   `/orchidsoftware/platform`

Use the local mirror first, then vendor source for exact signatures. Context7 is only a fallback or a cross-check when the local copy is stale or incomplete.

## Refresh Docs

```bash
.agents/skills/orchid-platform/scripts/sync_orchid_docs.sh
python3 .agents/skills/codebase-self-learning/scripts/discover_skills.py --json
```

The sync script mirrors the official English Orchid documentation Markdown from `orchidsoftware/orchid.software` into the local skill references and records the source commit in `.agents/skills/orchid-platform/references/SOURCE.md`.

## Search Before Editing

```bash
rg -n "Layout::table|TD::make|ModalToggle|permission|Screen" .agents/skills/orchid-platform/references/docs
rg -n "class Screen|class TD|function permission|function commandBar" vendor/orchid/platform/src vendor/orchid/platform/stubs
rg -n "Layout::|TD::|Toast::|Route::screen|tkey\\(" app/Orchid routes/platform.php tests
```

## Quality Gate

- Confirm `composer show orchid/platform` before using version-sensitive APIs.
- Keep visible Orchid UI text translatable with `tkey()` or existing localization helpers.
- Keep business logic in Actions, Form Requests, model scopes, services, or policies, not directly in screens.
- Prepare data in `Screen::query()` with eager loading and pagination where needed.
- Protect admin screens with `permission()` and verify seeded permissions.
- Run focused tests first, then `php artisan test` when the change touches shared admin behavior.
