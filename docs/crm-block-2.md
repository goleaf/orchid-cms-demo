# Block 2: CRM Lead Management Foundation

This block implements CRM lead management for one local driving school company. It is not a SaaS module and does not include tenants, subscriptions, reseller flows, platform-owner dashboards, or multi-company isolation.

## Scope

The CRM receives website leads from the public enrollment and callback forms, lets managers process leads in Orchid, tracks communication, notes, tasks, statuses, duplicate links, UTM data, tags, and lost reasons, and prepares a lead for future conversion into a student.

Student records, payments, invoices, lessons, exams, payroll, external SMS or telephony integrations, WhatsApp integrations, and AI scoring remain outside this block.

## Public Website Intake

Public forms submit through validated Form Requests and call lead creation Actions:

- Enrollment form: `CreateEnrollmentLeadAction`
- Callback form: `CreateCallbackLeadAction`
- Duplicate detection: `DetectLeadDuplicateAction`
- Timeline and status history: `RecordLeadActivityAction` and related lead actions

Marketing fields are captured with the lead: UTM source, medium, campaign, term, content, referrer URL, landing page, form page, form name, locale, IP address, and user agent.

## Orchid CRM Screens

- Lead list with filters, quick segments, duplicate indicators, tag display, and CSV export
- Lead create/edit CRM card
- Pipeline view
- Lead task list
- Dictionary management for statuses, sources, lost reasons, and tags
- Message template management

Managers can add notes, log communication and calls, create and complete tasks, mark leads lost, duplicate, or spam, and prepare a lead for future enrollment.

## Architecture

CRM write operations use Actions and Form Requests. Custom validation Rules handle active lead sources, channel-compatible message templates, and preventing a lead from being marked as its own duplicate.

Seeded dictionaries are built through model factories while preserving update-or-create behavior for stable system records. Tests use factories for CRM records and seeded dictionaries for localized labels.

## Permissions

CRM permissions include viewing, creating, updating, assigning, changing status, managing tasks, managing dictionaries, viewing marketing data, converting, and exporting leads. The superadmin role is seeded with all local CRM permissions.

## Conversion Boundary

`ReadyToEnroll` is the handoff status for Block 3. Block 2 stores the CRM fields needed for conversion, but it does not create the full student module or financial/lesson records.

## Verification

Run focused CRM verification first:

```bash
php artisan test --filter=DrivingSchoolPlatformTest
php artisan test --filter=CrmLocalizationTest
php artisan test --filter=SuperadminRoleTest
```

Then run the full suite when focused checks pass:

```bash
php artisan test
```
