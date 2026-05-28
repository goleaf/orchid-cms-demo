# Analytics Dashboard Data Model

This dashboard foundation belongs to one local driving school company. It does not add tenants, subscriptions, reseller reporting, platform billing, platform-owner dashboards, or multi-company analytics.

## Purpose

The dashboard model gives Block 12 a stable place to define owner and staff dashboards before editable Orchid screens are added. It supports:

- dashboard definitions for local audiences,
- dashboard widgets attached to a dashboard,
- user layout and filter preferences,
- translated dashboard and widget labels,
- soft deletion for dashboard definitions and widgets.

## Tables

`analytics_dashboards` stores dashboard definitions. Each record has a UUID, unique code, translated name and description, local audience, active/default flags, sort order, creator/updater links, soft deletes, and timestamps.

Supported audiences:

- owner
- director
- manager
- administrator
- instructor
- finance
- marketing
- system

`dashboard_widgets` stores widget definitions. Widgets belong to an analytics dashboard and keep a unique code, widget type, translated title and description, configuration, filters, grid width and height, active flag, sort order, creator/updater links, soft deletes, and timestamps. Older metric fields remain available for compatibility with the first owner dashboard screen.

Supported widget types:

- counter
- chart
- table
- funnel
- progress
- ranking
- alert
- calendar_summary

`user_dashboard_preferences` stores per-user dashboard layout and filters. It links a user to a dashboard and keeps JSON layout, filters, default flag, and timestamps. Legacy visible-widget and widget-order preferences remain compatible with the first analytics action.

## Model Behavior

Dashboard, widget, and preference records use factories for tests and seed data. Dashboard and widget models generate UUIDs when missing, expose relationships, and include reusable scopes for active, default, audience, dashboard, widget type, and ordered queries.

Translated dashboard names use `name_translations`. Translated widget titles use `title_translations` with the older name translations as a fallback so existing dashboard rows still display cleanly.

## Seed Data

The analytics demo seeder creates a default owner overview dashboard, links the seeded owner widgets to it, and stores an initial user preference when a user already exists. The seed data remains local to the driving school and uses existing operational modules as data sources.

## Verification

Relevant focused checks:

```bash
php artisan test --filter=AnalyticsDashboardDataModelTest
php artisan test --filter=AnalyticsBlockFoundationTest
```

Run the broader platform and localization checks when dashboard screens, permissions, or translated labels change.
