# Communications, Reminders, and Notifications

Project baseline: follow [`docs/project-specs.md`](project-specs.md) and [`AGENTS.md`](../AGENTS.md). Communication work is Laravel + Orchid + Blade, uses Eloquent only, and keeps all visible admin/public text translatable.

This module is for one local driving school. It does not add tenants, subscription billing, reseller logic, platform-owner dashboards, external messaging providers, or multi-company isolation.

## Purpose

The communication foundation prepares shared records for student follow-ups, CRM follow-ups, reminders, templates, user preferences, and delivery history.

Current committed behavior is schema-level foundation. It supports later admin screens, Actions, jobs, and provider integrations without adding them prematurely.

CRM already has lead communication records for calls and lead history. This module prepares the broader cross-module communication layer for students and internal notifications.

## Storage

The committed communication foundation creates:

- `notification_channels`: available internal or external channels with provider/settings placeholders.
- `communication_templates`: reusable multilingual message templates by type and channel.
- `user_notification_preferences`: per-user channel and event preferences.
- `communication_reminders`: scheduled reminders linked to leads, students, enrollments, assignees, channels, and templates.
- `student_communications`: student communication timeline entries linked to students, enrollments, leads, templates, and reminders.
- `notification_delivery_logs`: outbound or inbound delivery history for users, students, leads, communications, reminders, and templates.

Existing CRM communication records remain under the CRM lead workflow until a later consolidation step is designed.

## Scope Boundaries

In scope now:

- local schema for reminders and delivery logs,
- multilingual names, subjects, and bodies where the schema stores user-visible content,
- relationships to leads, students, enrollments, users, channels, and templates,
- indexes for due reminder queues and delivery history lookup.

Out of scope now:

- SMS, email, WhatsApp, telephony, or push provider integration,
- queued sending jobs,
- provider callback handling,
- communication admin screens,
- automated notification rules,
- AI-generated messages.

## Data Flow

Future Actions should create reminders and student communications from CRM, student, document, payment, exam, and schedule workflows.

Future Jobs should read due reminders by status and due time, resolve channel preferences, render templates, create delivery logs, and update delivery status.

Public website lead intake can continue to create CRM communication records and internal notifications. A later change can map those events into this shared layer after the model and admin workflow are implemented.

## Query Notes

The schema already includes indexes for:

- active notification channels by sort order,
- active templates by channel/type,
- notification preferences by channel, event, and enabled flag,
- reminders by status, assignee, due time, lead, and student,
- student communications by student, lead, channel, and communication time,
- delivery logs by status, channel, lead, student, recipient, and created time.

Use Eloquent scopes and Actions for queue-ready queries. Do not add raw SQL or query from Blade.

## Tests

Relevant existing verification:

- `ExamBlockFoundationTest` for the committed schema foundation.
- CRM lead communication tests for the existing lead-level communication workflow.

Future communication work should add focused tests for template validation, reminder scheduling, delivery log writes, notification preferences, authorization, and translation keys.

## TODOs

- Add Eloquent models, factories, and seeders for the shared communication foundation.
- Add admin screens for channels, templates, reminders, and delivery logs.
- Add Actions for scheduling, completing, cancelling, and sending reminders.
- Add queued jobs for due reminders and provider delivery.
- Add safe provider integrations only after local school workflows are proven.
