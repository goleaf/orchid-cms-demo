---
name: driving-school-design
description: Use when redesigning, polishing, reviewing, or implementing the public website or Orchid admin UI for this local driving-school Laravel app. Applies project-specific Blade, Tailwind, Orchid, localization, browser QA, and visual-design rules.
---

# Driving School Design

Use this skill for any visual design, redesign, UI polish, design review, responsive fix, or visual QA task in this repository.

## Product Direction

- This is a local driving-school operating system, not SaaS.
- Build server-rendered Laravel Blade for the public site and Orchid Platform for admin.
- Do not introduce React, Vue, Inertia, tenant UI, subscription UI, platform-owner pages, or reseller flows unless the user explicitly changes direction.
- All visible UI text must be translatable through `tkey()`, Laravel translation keys, or existing localization helpers.

## Design Standard

- Public website: practical, local-service trust, clear course discovery, strong contact/application flows, real driving-school imagery, readable pricing, visible phone/contact paths.
- Admin/Orchid: dense, calm operational UI. Prioritize scanning, repeated use, predictable navigation, and fast decision-making over marketing-style decoration.
- Avoid generic dashboard-card mosaics, ornamental gradients, one-color palettes, huge rounded cards, vague stock imagery, and decorative blobs.
- Prefer real visual assets. Use existing assets first; generate or source bitmap assets only when the page needs a stronger real-world visual.
- Keep cards at 8px radius or less unless the surrounding design already proves another radius.
- Text must fit at mobile and desktop sizes. Do not use viewport-width font scaling.

## Local Stack

- Laravel Blade views live under `resources/views/site`.
- Site components live under `resources/views/components/site`.
- Main CSS is `resources/css/app.css`.
- Vite entry points are configured in `vite.config.js`.
- Tailwind CSS v4 is installed through `@tailwindcss/vite`.
- Orchid admin implementation must also load the `orchid-platform` skill before editing screens, layouts, tables, fields, menus, permissions, or platform routes.

## MCP Workflow

Use the connected MCP tools according to the task:

- `playwright`: for real browser navigation, screenshots, responsive checks, forms, and user-flow verification.
- `chrome-devtools`: for DOM/CSS inspection, responsive emulation, accessibility and Lighthouse-style checks when browser-level diagnostics matter.
- `context7`: for current Tailwind, Laravel, Vite, and package documentation when API or configuration details may have changed.
- `laravel-boost`: for Laravel app info, routes, logs, schema, docs, and safe app-aware inspection.

For design implementation:

1. Inspect the current rendered page and source before writing UI.
2. Treat screenshots, references, and browser captures as design evidence, not as final project style.
3. Translate the result into Blade components, Tailwind v4 CSS, and existing project conventions.
4. Reuse project components and assets before adding new ones.
5. Verify the final UI in a real browser with desktop and mobile screenshots.

## Implementation Rules

- Inspect current Blade/CSS before changing design.
- Keep controllers and screens thin; do not add queries to Blade.
- Preserve existing routes, actions, form requests, policies, and localization flow.
- Use `@forelse` for rendered lists with empty states.
- Use icons only when they improve scanning; do not add an icon package unless the project already uses it or the user approves it.
- Keep styling system-wide where possible; avoid one-off inline styles.
- When changing shared public layout, run a build and at least one focused browser check.

## Verification

Default checks for design work:

```bash
npm run build
php artisan test --filter=PublicWebsiteFrontendTest
```

For Orchid UI changes, also run the relevant platform/admin tests and verify Orchid docs/vendor signatures through the `orchid-platform` workflow.
