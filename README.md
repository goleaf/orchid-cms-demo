# Orchid CMS Demo

This repository is becoming a local driving-school operating system built with Laravel and Orchid. It is for one driving school company, not a SaaS platform.

Project rules live in [`AGENTS.md`](AGENTS.md) and [`docs/project-specs.md`](docs/project-specs.md).

## Stack

- Laravel 12.x
- Orchid Platform 14.x
- Server-rendered Blade
- Eloquent-only query layer
- Actions, Form Requests, policies, model scopes, factories, and feature tests

## Documentation

- Project specs: [`docs/project-specs.md`](docs/project-specs.md)
- Public website: [`docs/public-website.md`](docs/public-website.md)
- Public website foundation: [`docs/public-website-foundation.md`](docs/public-website-foundation.md)
- CRM leads: [`docs/crm-leads.md`](docs/crm-leads.md)
- CRM Block 2: [`docs/crm-block-2.md`](docs/crm-block-2.md)
- Students and enrollments: [`docs/students.md`](docs/students.md)
- Lead to student conversion: [`docs/lead-to-student-conversion.md`](docs/lead-to-student-conversion.md)
- Training groups and education structure: [`docs/training-groups.md`](docs/training-groups.md)
- Operations, schedule, instructors, and fleet: [`docs/operations.md`](docs/operations.md)
- Exams, admissions, and results: [`docs/exams.md`](docs/exams.md)
- Documents: [`docs/documents.md`](docs/documents.md)
- Payments and finance foundation: [`docs/payments.md`](docs/payments.md)
- Communications, reminders, and notifications: [`docs/communications.md`](docs/communications.md)
- Dashboard and analytics foundation: [`docs/analytics.md`](docs/analytics.md)
- Orchid local documentation workflow: [`docs/orchid-local-documentation.md`](docs/orchid-local-documentation.md)
- Codex automation: [`docs/codex-automation.md`](docs/codex-automation.md)
- Human changelog: [`changelog.md`](changelog.md)

## Local Orchid Docs

The `orchid-platform` skill is discovered locally and the compact skill inventory is loaded by default through Codex hooks. Full Orchid docs are mirrored locally and searched on demand:

```bash
rg -n "Layout::table|TD::make|ModalToggle|permission|Screen" .agents/skills/orchid-platform/references/docs
```

Refresh the mirror and skill inventory:

```bash
.agents/skills/orchid-platform/scripts/sync_orchid_docs.sh
python3 .agents/skills/codebase-self-learning/scripts/discover_skills.py --json
```

## Automated Changelog And Commits

After each Codex prompt, the Stop hook stages all changes, updates `changelog.md` in plain human language, generates a Conventional Commit message through `codex exec`, commits the staged changes, and pushes when an upstream is configured.

Details are in [`docs/codex-automation.md`](docs/codex-automation.md).

## Development

Install dependencies and prepare the app with the Laravel setup flow:

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run build
```

Run tests:

```bash
php artisan test
```

Use focused tests before the full suite when working in one module:

```bash
php artisan test --filter=DrivingSchoolPlatformTest
php artisan test --filter=SystemLocalizationTest
```

## System Design Corpus

The `nimin1/system-design-vibecoding` Markdown corpus is vendored under `resources/system-design-vibecoding` with its MIT license preserved.

Run the importer with:

```bash
php artisan db:seed --class=SystemDesignVibecodingSeeder
```

The seeder imports those Markdown files into `knowledge_articles` with source metadata, rewritten internal links, translated category labels, and public rendering through the existing `/blog` knowledge-base routes.
