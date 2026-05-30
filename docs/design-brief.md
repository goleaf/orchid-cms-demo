# Design Brief

This brief defines the visual and interaction direction for the local driving-school operating system. It applies to the public website and the Orchid admin UI.

Project baseline: follow [`docs/project-specs.md`](project-specs.md) and [`AGENTS.md`](../AGENTS.md). This product is for one local driving school. It is not a SaaS platform and must not introduce tenant switching, subscription billing, reseller flows, platform-owner dashboards, or multi-company administration.

## Product Tone

The product should feel practical, local, trustworthy, and operationally clear.

Public pages should help visitors quickly understand courses, prices, branches, instructors, vehicles, reviews, and application options. Admin pages should help school staff scan, filter, decide, and act repeatedly without marketing-style decoration.

## Public Website Direction

Use the public website for:

- course discovery
- price comparison
- branch and contact discovery
- instructor and vehicle trust signals
- application and callback flows
- reviews and knowledge-base content

The first viewport should show the driving-school offer clearly, expose the primary application action, and leave a visible hint of the next section on common desktop and mobile viewports.

Prefer real or generated driving-school imagery over abstract decoration. Existing local assets should be reused before adding new media.

## Admin UI Direction

Use Orchid admin screens for dense local operations:

- CRM lead processing
- student and enrollment work
- group and schedule review
- exam, document, finance, notification, analytics, and security workflows
- user, permission, language, and translation management

Admin UI should prioritize compact tables, predictable filters, explicit permissions, confirmation for destructive actions, and localized feedback. Avoid hero-like admin pages, decorative card mosaics, and visual treatment that slows down repeated work.

## Layout Rules

- Keep cards at 8px radius or less unless an existing local component requires otherwise.
- Do not place cards inside cards.
- Keep page sections as full-width bands or unframed layouts with constrained inner content.
- Avoid decorative gradient blobs, orbs, and one-color palettes.
- Do not scale text by viewport width alone.
- Ensure long translated labels fit without clipping on desktop and mobile.
- Keep key mobile actions reachable without making the whole page horizontally scroll.

## Navigation Rules

Public navigation should prioritize:

1. courses
2. prices
3. branches
4. application
5. contacts
6. secondary trust and content links
7. language switcher
8. admin entry

When all links cannot fit, keep primary links visible and move secondary links into a compact translated menu. Mobile navigation should keep the full link set accessible.

## Localization Rules

All visible UI labels must come through `tkey()`, Laravel translation keys, or existing localization helpers.

Do not hardcode Russian, English, Lithuanian, Polish, or any other visible UI text directly into Blade, Orchid screens, notifications, validations, seeded interface content, or documents generated for users.

## Data Flow Rules

Blade views receive prepared data from controllers or Actions. They must not query models, count relationships, or calculate aggregates inside templates.

Orchid screen `query()` methods prepare all table and layout data. Write behavior belongs in Actions and validation belongs in Form Requests.

## Verification

For public website design changes:

```bash
npm run build
npm run validate:styles
php artisan test --filter=PublicWebsiteFrontendTest
```

Also verify the changed page in a browser at desktop and mobile widths.

For Orchid UI changes:

```bash
composer show orchid/platform
python3 .agents/skills/codebase-self-learning/scripts/discover_skills.py --json --no-write
```

Search local Orchid docs and vendor signatures before editing screens, layouts, menus, tables, fields, permissions, or platform routes.

## Current Design State

The homepage uses a real driving-school hero image, compact translated navigation, primary application actions, and metric cards. Desktop navigation keeps primary public links visible and groups secondary links under a translated menu. Mobile navigation uses the full menu, and the hero avoids page-wide horizontal overflow.

The security and user-management documentation now appears in the main documentation indexes so operational staff-account work is discoverable alongside public website, CRM, students, education, exams, notifications, analytics, and automation docs.
