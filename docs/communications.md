# Communications, Reminders, and Notifications

Project baseline: follow [`project-specs.md`](project-specs.md) and [`AGENTS.md`](../AGENTS.md). Communication work is Laravel + Orchid + Blade, uses Eloquent only, and keeps all visible admin and validation text translatable.

This module is for one local driving school. It does not add tenants, subscription billing, reseller logic, platform-owner dashboards, external messaging providers, or multi-company isolation.

## Purpose

The communication foundation gives the school one place to manage internal notifications, reusable message templates, scheduled reminders, user preferences, student communication history, CRM lead communication history, and delivery logs.

CRM lead calls and notes continue to use the existing lead communication history. Student communication history is stored separately and can link back to the original lead, enrollment, reminder, template, and channel.

## Admin Surface

The Orchid administration area now exposes communication pages for:

- notification channels,
- message templates,
- scheduled reminders,
- delivery logs.

Protected pages require the communications permissions seeded for the local superadmin role. Destructive provider behavior is not present because external providers are placeholders only.

## Storage

The module uses these records:

- `notification_channels`: internal, email, phone, and future external channel definitions.
- `communication_templates`: multilingual subjects and bodies by type and channel.
- `communication_reminders`: scheduled follow-ups for leads, students, enrollments, assignees, channels, and templates.
- `notification_delivery_logs`: queue, sent, failed, skipped, and read history for internal and future external delivery.
- `user_notification_preferences`: per-user channel and event preferences.
- `student_communications`: student timeline entries for calls, emails, reminders, and future messages.
- Existing CRM lead communication records remain the CRM lead history.

## Channels

The seeded channels are:

- internal admin notifications through the database notification channel,
- email through the current Laravel mail configuration,
- phone as manual call history,
- SMS placeholder,
- WhatsApp placeholder,
- Telegram placeholder.

The placeholder channels intentionally do not send real messages. They can create skipped delivery logs so the workflow is traceable before a provider is selected.

## Data Flow

Actions handle the write paths:

- create or update channels, templates, and reminders,
- render templates with named variables,
- log student communication history,
- create internal admin notifications,
- record notification delivery attempts,
- mark placeholder external channels as skipped,
- complete scheduled reminders.

Screens and controllers should pass prepared data into views. Blade templates must not query communications, reminders, templates, or delivery logs directly.

## Query Notes

The schema includes indexes for:

- active notification channels by sort order,
- active templates by channel and type,
- reminder status, assignee, due date, lead, and student,
- student communication timelines by student, lead, channel, and communication time,
- delivery logs by status, channel, lead, student, recipient, and created time,
- user preferences by channel, event, and enabled state.

Use Eloquent scopes and Actions for queue-ready queries. Do not add raw SQL or query from Blade or table render loops.

## Verification

Focused verification:

```bash
php artisan test --filter=CommunicationModuleFoundationTest
```

Shared checks after communication admin changes:

```bash
php artisan test --filter=DrivingSchoolPlatformTest
php artisan test --filter=SystemLocalizationTest
php artisan test --filter=SuperadminRoleTest
```

Run the full suite when communication changes touch shared permissions, translations, student history, CRM history, or admin navigation.
