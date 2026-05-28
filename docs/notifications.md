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

## Database Model Foundation

Block 11 adds a normalized notification data model beside the earlier operational communication tables. It keeps the local driving-school scope and links records to existing users, student profiles, and CRM leads.

Core records:

- `notification_channels` keeps the stable channel code, translated name and description, active flag, internal/email flags, and future SMS, WhatsApp, Telegram, and push placeholder flags.
- `notification_templates` stores reusable translated template definitions by group and optional channel.
- `notification_template_versions` stores draft, published, and archived subject/body snapshots with a variable schema.
- `notification_template_variables` stores template variable metadata such as required fields and translated labels.
- `notification_messages` stores prepared message snapshots, priority, status, schedule time, send time, failure time, and creator.
- `notification_recipients` stores user, student, lead, email, phone, locale, and recipient status for each message.
- `notification_deliveries` stores channel-specific delivery attempts, placeholder provider identifiers, attempt numbers, delivery timestamps, and error text.
- `notification_preferences` stores enabled/disabled channel preferences for users, students, and leads.
- `reminder_rules` stores reusable reminder definitions with trigger type, target type, template, offset, and active flag.
- `reminder_schedules` stores scheduled reminders for a target and optional generated message.
- `communication_threads` and `communication_messages` store student and lead conversation history across inbound, outbound, and internal messages.
- `communication_attachments` stores metadata for files attached to communication messages.
- `notification_activities` stores the audit timeline for message creation, sending, delivery, failure, and read events.

The normalized tables do not replace the existing legacy communication screens. They provide the database foundation for future screens, Actions, reminders, and channel adapters.

## Actions, Requests, And Rules

Block 11 now has a workflow layer for preparing and tracking notifications without putting business logic in screens or Blade views.

Notification Actions:

- `CreateNotificationMessageAction` creates a message snapshot, recipients, and the first activity record.
- `CreateMessageFromTemplateAction`, `ResolveNotificationTemplateVariablesAction`, and `RenderNotificationTemplateAction` resolve target data and render published template versions.
- `ScheduleNotificationMessageAction` marks a message for later delivery.
- `SendInternalNotificationAction` delivers Laravel database notifications to users and logs delivery attempts.
- `SendEmailNotificationAction` sends through Laravel notification mail routing and logs delivery attempts.
- `SendSmsPlaceholderNotificationAction` and `SendWhatsAppPlaceholderNotificationAction` queue placeholder delivery attempts only.
- `MarkNotificationDeliveredAction`, `MarkNotificationFailedAction`, and `RetryNotificationDeliveryAction` update delivery state and audit history.
- `CreateCommunicationThreadAction` and `AddCommunicationMessageAction` create student or lead communication history records.
- `CreateReminderRuleAction`, `ScheduleReminderAction`, `ProcessDueRemindersAction`, and `CancelReminderScheduleAction` manage scheduled reminders.
- `UpdateNotificationPreferenceAction` saves user, student, or lead channel preferences.

Notification Form Requests live under the notification request namespace and validate templates, prepared messages, send requests, reminder rules, reminder schedules, communication messages, and preferences. Validation messages use `notifications.validation.*` translation keys.

Custom Rules cover active channels, published templates, safe template content, required recipients, valid targets, send/retry state, reminder triggers, schedule dates, preference ownership, priority values, and communication direction values.

Supported message priorities are `low`, `normal`, `high`, and `urgent`. Supported message statuses are `draft`, `scheduled`, `queued`, `sent`, `delivered`, `failed`, `cancelled`, and `archived`.

## Factories And Seeders

Block 11 includes repeatable factory states and seeders for notification setup data. The seeders are idempotent and keyed by stable codes, so they can be run repeatedly during local setup and automated tests.

Factory states cover:

- channels for internal, email, SMS placeholder, WhatsApp placeholder, Telegram placeholder, push placeholder, active, inactive, and translated records,
- templates for appointment reminders, payment reminders, rejected documents, lessons, exams, lead follow-up, student welcome, generated contracts, active, system, and translated records,
- messages for draft, scheduled, queued, sent, delivered, failed, cancelled, urgent, high, normal, and template-backed records,
- deliveries for queued, sent, delivered, failed, retryable, and placeholder attempts,
- reminder rules for lesson tomorrow, lesson one hour before, payment due, document missing, exam reminder, lead follow-up, and active records.

Default seeders create:

- channels: internal, email, SMS placeholder, WhatsApp placeholder, Telegram placeholder, and push placeholder,
- templates: student welcome, lead follow-up, lesson reminder, driving lesson reminder, payment due, missing document, rejected document, exam reminder, and generated contract,
- template variables and a published first version for each default template,
- reminder rules for lessons, payments, documents, exams, and lead follow-up,
- notification translation keys in Russian, English, Lithuanian, and Polish.

The demo notification seeder only runs in local, demo, and test environments. It creates sample messages, preferences, reminder schedules, communication history, an attachment record, and activity records without sending real external messages.

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

The normalized notification Actions also queue placeholder delivery records for SMS and WhatsApp. They do not call production providers, store credentials, open webhooks, or send external messages. Telegram remains a channel placeholder for future adapters.

Do not add production provider credentials, webhooks, HTTP clients, or message sending jobs until the school chooses a provider and the workflow is explicitly requested.

## Admin Permissions

The communications permission group includes access for channel management, template management, reminder management, delivery log viewing, preferences, student history, and lead history.

Local superadmin seeding enables these permissions. Other roles should receive only the specific communication permissions needed for their job.

## Verification

Run the focused notification and communication checks with:

```bash
php artisan test --filter=NotificationFactoriesSeedersTest
php artisan test --filter=NotificationActionsRulesTest
php artisan test --filter=NotificationDatabaseModelsTest
php artisan test --filter=CommunicationModuleFoundationTest
```

For shared navigation, translation, and permission changes, also run:

```bash
php artisan test --filter=DrivingSchoolPlatformTest
php artisan test --filter=SystemLocalizationTest
php artisan test --filter=SuperadminRoleTest
```
