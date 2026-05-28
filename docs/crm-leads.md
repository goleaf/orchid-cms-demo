# CRM Leads

The CRM lead module manages requests for one local driving school company. It receives structured leads from the public website, lets managers process them in Orchid, tracks next actions and timeline history, and prepares qualified leads for the future Student module.

This module is not SaaS. It has no tenants, subscription billing, reseller logic, platform-owner dashboards, or multi-company isolation.

## Purpose

CRM leads are used for:

- public website application, callback, and contact requests
- manual manager-created leads
- lead status processing and pipeline visibility
- responsible manager assignment
- tasks, notes, activities, and call logs
- duplicate detection by normalized phone and email
- UTM and source attribution
- safe CSV export for managers and directors
- future handoff to student conversion in Block 3

The canonical storage is `marketing_leads`. `App\Models\Lead` is a compatibility model over the same table, so website requests and CRM screens use the same lead records.

## Lead Lifecycle

Default CRM statuses are seeded by `CrmStatusSeeder`.

- `new`: received and not processed
- `no_answer`: manager tried to call, no answer
- `contacted`: manager reached the lead
- `consultation`: consultation is planned or in progress
- `waiting_documents`: documents are required
- `waiting_payment`: payment is required
- `ready_to_enroll`: ready for future student conversion
- `enrolled`: converted or prepared as successful
- `lost`: refused or lost
- `duplicate`: duplicate of another lead
- `spam`: invalid request
- `archived`: closed and hidden from active work

Status transitions are validated by `ValidLeadStatusTransitionRule` and executed through `ChangeLeadStatusAction` or `MoveLeadToStatusAction`. Closed, lost, spam, duplicate, and converted leads are not treated as active work.

`PrepareLeadForStudentConversionAction` moves a convertible lead to the ready-to-enroll flow and returns a readiness result. It does not create Student records; that belongs to Block 3.

## Dictionaries

CRM dictionaries are translatable and managed from Orchid.

- Lead statuses: `lead_statuses`
- Lead sources: `lead_sources`
- Lost reasons: `lead_lost_reasons`
- Tags: `lead_tags`

Dictionary labels use `name_translations` through `HasTranslatedDictionaryName`, with fallback to `name`, then `code` or `slug`.

Default source codes include `website`, `callback`, `contact_form`, `phone`, `office`, advertising sources, referral, partner, and other.

Default lost reasons include price, schedule, location, competitor, no response, documents, payment, language, car type, duplicate, spam, and other.

Default tags include hot, VIP, needs call, ready to pay, needs documents, repeat request, urgent, individual schedule, gearbox preferences, evening or weekend training, and corporate client.

## Actions

All CRM write operations should go through Actions:

- `GenerateLeadNumberAction`
- `NormalizeLeadPhoneAction`
- `DetectLeadDuplicateAction`
- `CreateLeadAction`
- `CreateWebsiteLeadAction`
- `CreateCallbackLeadAction`
- `UpdateLeadAction`
- `ChangeLeadStatusAction`
- `AssignLeadManagerAction`
- `AddLeadNoteAction`
- `LogLeadCallAction`
- `CreateLeadTaskAction`
- `CompleteLeadTaskAction`
- `CancelLeadTaskAction`
- `MarkLeadLostAction`
- `MarkLeadDuplicateAction`
- `MarkLeadSpamAction`
- `ReopenLeadAction`
- `PrepareLeadForStudentConversionAction`
- `ExportLeadsCsvAction`
- `FilterLeadsAction`
- `GetLeadPipelineAction`
- dictionary create/update/delete Actions

Screens and controllers should remain thin and call these Actions.

## Form Requests

CRM and website lead writes are validated through Form Requests:

- lead create/update: `StoreLeadRequest`, `UpdateLeadRequest`, `LeadCrmRequest`
- lead workflow: status change, manager assignment, notes, calls, tasks, lost, duplicate, spam, reopen, export
- dictionaries: status, source, lost reason, tag store/update requests plus `LeadDictionaryRequest`
- public intake: `StoreWebsiteLeadRequest`, `StoreCallbackLeadRequest`, `StoreContactLeadRequest`, `StoreEnrollmentLeadRequest`

Validation messages use translation keys through `tkey()`.

## Rules

Custom CRM rules include:

- contact requirement: `PhoneOrEmailRequiredRule`
- lead state and conversion: `LeadCanBeUpdatedRule`, `LeadCanBeConvertedRule`
- duplicate guards: `LeadIsNotDuplicateOfItselfRule`, `LeadDuplicateOriginalRule`
- dictionaries: active status/source/lost reason/tag, dictionary code, translated name, system code protection, default status uniqueness, delete safety
- workflow values: lead priority, task status, task priority, call result, future follow-up date
- marketing access: `LeadMarketingAccessRule`

All custom rule failures return translation keys, not hardcoded visible text.

## Website Integration

Public website forms create CRM leads:

- application form: source `website`
- callback form: source `callback`
- contact form: source `contact_form` when available

The intake flow validates public data, normalizes phone, resolves course, branch, and training group context, captures UTM and request metadata, saves consent, detects duplicates without blocking creation, creates an activity, creates the first follow-up task, and redirects the visitor to the thank-you page.

Website marketing fields saved to CRM include UTM source, medium, campaign, content, term, referrer, landing page, form page, form name, locale, IP address, and user agent.

## Duplicate Detection

`DetectLeadDuplicateAction` checks non-deleted leads by normalized phone first, then case-insensitive email. Duplicate detection does not block lead creation. When a match is found, the new lead can store `duplicate_of_id` and record a duplicate activity.

## Tasks, Activities, Notes, And Calls

Lead work is task-driven:

- new website leads receive a high-priority first task
- manual leads receive a normal-priority first task
- tasks support open, in progress, done, and cancelled
- overdue tasks are visible in the task list and menu counters

Activities record creation, website intake, manual intake, status changes, manager assignment, notes, calls, tasks, duplicate/lost/spam marks, reopen, conversion preparation, archive, and updates.

Notes are stored as lead comments and timeline activities. Calls are stored as phone communications and call activities; there is no separate `lead_call_logs` table in this schema.

## Filters, Segments, And Pipeline

Lead filtering is centralized in `FilterLeadsAction`. It supports search, status, source, manager, course, category, branch, training group, tag, lost reason, priority, date ranges, UTM filters, form name, open/closed/converted flags, duplicates, overdue, and quick segments.

`GetLeadPipelineAction` groups leads by active status and limits records per column for the Orchid pipeline screen.

## CSV Export

Export is handled by `ExportLeadsCsvAction`.

Access rules:

- export requires `crm.leads.export`
- marketing fields require `crm.leads.view_marketing`
- soft-deleted leads are excluded by default

Base CSV columns include ID, UUID, lead number, name, phone, email, status, source, manager, course, branch, training group, priority, lead score, created at, last contacted at, next follow-up at, closed at, converted at, comment, and internal comment.

Marketing columns are included only with permission: UTM fields, referrer, landing page, form page, form name, locale, IP address, and user agent.

The filename format is `crm-leads-YYYY-MM-DD.csv`. CSV rows are streamed and written with safe CSV escaping.

## Permissions

Core CRM permissions:

- `crm.leads.view`
- `crm.leads.create`
- `crm.leads.update`
- `crm.leads.delete`
- `crm.leads.archive`
- `crm.leads.assign`
- `crm.leads.change_status`
- `crm.leads.override_status_transition`
- `crm.leads.manage_tasks`
- `crm.leads.manage_dictionaries`
- `crm.leads.manage_tags`
- `crm.leads.view_marketing`
- `crm.leads.convert`
- `crm.leads.export`
- `crm.pipeline.view`

Superadmin receives the local permission set through `SuperadminPermissions::enabled()` and `SuperadminRoleSeeder`.

## Orchid Screens

CRM screens:

- `LeadListScreen`
- `LeadEditScreen`
- `LeadPipelineScreen`
- `LeadTaskListScreen`
- `LeadStatusListScreen`
- `LeadSourceListScreen`
- `LeadLostReasonListScreen`
- `LeadTagListScreen`
- `LeadDictionaryListScreen`
- `LeadDictionaryEditScreen`

Marketing data fields are hidden unless the user has marketing access. Dictionary screens require dictionary permissions. Export controls require export permission.

## Factories

Factories exist for:

- `LeadFactory`
- `MarketingLeadFactory`
- `LeadStatusFactory`
- `LeadSourceFactory`
- `LeadLostReasonFactory`
- `LeadTagFactory`
- `LeadActivityFactory`
- `LeadTaskFactory`
- `MarketingLeadActivityFactory`
- `MarketingLeadTaskFactory`
- communication, comment, document, and status history factories

Call logs are represented by phone communication records, so there is no separate `LeadCallLogFactory`.

## Seeders

CRM seeders:

- `CrmStatusSeeder`
- `CrmSourceSeeder`
- `CrmLostReasonSeeder`
- `CrmTagSeeder`
- `CrmDictionarySeeder`
- `CrmTranslationSeeder`
- `CrmDemoLeadSeeder`
- `CrmSeeder`

Dictionary seeders are idempotent and use stable codes. Demo leads are created through factories and stable demo email keys.

## Translation Keys

Visible CRM UI and validation text uses translation keys:

- `menu.crm.*`
- `crm.leads.*`
- `crm.pipeline.*`
- `crm.tasks.*`
- `crm.calls.*`
- `crm.activities.*`
- `crm.dictionaries.*`
- `crm.validation.*`
- `crm.leads.validation.*`
- `permissions.crm.*`
- `validation.attributes.lead.*`
- `validation.attributes.lead_task.*`
- `validation.attributes.lead_status.*`

Translation values are seeded for `ru`, `en`, `lt`, and `pl`.

## Validation Errors

Important translated validation keys include phone or email required, invalid status transition, lead cannot be updated or converted, duplicate original requirements, inactive dictionaries, invalid priorities/statuses/call results, future follow-up requirements, dictionary code format, missing default translation, marketing access denied, and export not allowed.

## Reporting Foundation

`MarketingLead` provides simple reporting helpers for future analytics:

- count by status
- count by source
- count by manager
- count by lost reason
- count by day
- conversion-ready count
- overdue follow-up count

This is not a dashboard or full analytics module yet.

## Verification

Recommended CRM verification:

```bash
php artisan test --filter=CrmLeadBusinessActionsTest
php artisan test --filter=CrmTasksActivitiesCallsTest
php artisan test --filter=CrmOrchidAdminUiTest
php artisan test --filter=CrmLeadCsvExportTest
php artisan test --filter=CrmFinalHardeningTest
php artisan test
```

Safe dictionary seeder checks:

```bash
php artisan db:seed --class=CrmStatusSeeder
php artisan db:seed --class=CrmSourceSeeder
php artisan db:seed --class=CrmLostReasonSeeder
php artisan db:seed --class=CrmTagSeeder
php artisan db:seed --class=CrmTranslationSeeder
```

## Known TODOs

- Implement full lead-to-student conversion in Block 3.
- Add full analytics dashboards later; current reporting helpers are only a foundation.
- External SMS, WhatsApp, telephony, and AI scoring are intentionally out of scope.
- Human review is still recommended for approximate `ru`, `en`, `lt`, and `pl` translation wording.
