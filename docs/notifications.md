# Notifications and Delivery Logs

This guide covers the notification-facing part of the communications module. The wider module guide is [`communications.md`](communications.md).

The product is a local driving-school operating system. Notification work must not introduce tenants, subscriptions, reseller behavior, platform billing, or multi-company dashboards.

## Current Foundation

Notifications use Laravel's database notification storage for internal admin messages and project-owned delivery logs for traceability. External channels are represented as channel records and placeholders, not provider integrations.

The foundation supports:

- internal admin notifications,
- user notification preferences,
- scheduled reminder delivery tracking,
- student notification history,
- CRM lead notification history through existing lead communication records,
- delivery logs for internal, email, phone, and future external channels,
- SMS, WhatsApp, and Telegram placeholders.

## Internal Notifications

Internal notifications are delivered through Laravel database notifications. Each internal send can also create a delivery log with the database notification identifier, sender, recipient, subject, body, and sent timestamp.

Use the internal notification Action instead of sending notifications directly from screens. Screens should stay thin and only call Actions after Form Request validation and permission checks.

## Delivery Logs

Delivery logs are append-friendly operational records. They track:

- recipient user, student, or lead,
- channel and optional template,
- reminder and student communication links,
- direction and status,
- recipient email, phone, or external identifier,
- provider name and provider message status,
- queue, schedule, sent, failed, and read timestamps,
- translated or user-entered subject and body snapshots.

Delivery logs do not replace CRM lead history or student communication history. They record delivery attempts and outcomes.

## Preferences

User preferences are stored by user, channel, and event. They are intended for reminder routing and future notification controls. Keep preference reads in model scopes or Actions; do not query them from Blade or table renderers.

## External Placeholders

SMS, WhatsApp, and Telegram are placeholder channels only. The placeholder adapter marks delivery logs as skipped with a provider status of `placeholder`.

Do not add production provider credentials, webhooks, HTTP clients, or message sending jobs until the school chooses a provider and the workflow is explicitly requested.

## Admin Permissions

The communications permission group includes access for channel management, template management, reminder management, delivery log viewing, preferences, student history, and lead history.

Local superadmin seeding enables these permissions. Other roles should receive only the specific communication permissions needed for their job.

## Verification

Run the focused notification and communication checks with:

```bash
php artisan test --filter=CommunicationModuleFoundationTest
```

For shared navigation, translation, and permission changes, also run:

```bash
php artisan test --filter=DrivingSchoolPlatformTest
php artisan test --filter=SystemLocalizationTest
php artisan test --filter=SuperadminRoleTest
```
