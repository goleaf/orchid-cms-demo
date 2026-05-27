# Block 2: CRM Lead Management Foundation

This block implements CRM lead management for one local driving school company. It is not a SaaS module and does not include tenants, subscriptions, reseller flows, platform-owner dashboards, or multi-company isolation.

## Scope

The CRM receives website leads from the public enrollment and callback forms, lets managers process leads in Orchid, tracks communication, notes, tasks, statuses, duplicate links, UTM data, tags, and lost reasons, and prepares a lead for future conversion into a student.

Student records, payments, invoices, lessons, exams, payroll, external SMS or telephony integrations, WhatsApp integrations, and AI scoring remain outside this block.

## Public Website Intake

Public forms submit through validated Form Requests and call lead creation Actions:

- Enrollment form: `CreateEnrollmentLeadAction`
- Website application/contact forms: `CreateWebsiteLeadAction`
- Callback form: `CreateCallbackLeadAction`
- Duplicate detection: `DetectLeadDuplicateAction`
- Timeline and status history: `RecordLeadActivityAction` and related lead actions

Marketing fields are captured with the lead: UTM source, medium, campaign, term, content, referrer URL, landing page, form page, form name, locale, IP address, and user agent.

The public website and CRM share the same `marketing_leads` records. `Lead` is a compatibility model over the same table, so Block 2 does not create a duplicate `leads` or `website_leads` table.

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

### Actions

- `SaveMarketingLeadCrmAction`
- `UpdateMarketingLeadCrmAction`
- `AssignLeadManagerAction`
- `MoveLeadToStatusAction`
- `MarkLeadLostAction`
- `MarkLeadDuplicateAction`
- `PrepareLeadForEnrollmentAction`
- `DetectLeadDuplicateAction`
- `RecordLeadActivityAction`
- `AddLeadCommentAction`
- `AddLeadCommunicationAction`
- `CreateLeadTaskAction`
- `CompleteLeadTaskAction`
- `ExportMarketingLeadsCsvAction`
- `GetLeadPipelineAction`
- `SaveMarketingMessageTemplateAction`
- `DeleteMarketingMessageTemplateAction`
- `SaveLeadDictionaryAction`
- `DeleteLeadDictionaryAction`
- `ResolveLeadSourceAction`
- `ResolveLeadNotificationRecipientsAction`

### Form Requests

- `LeadCrmRequest`
- `LeadCommentRequest`
- `LeadCommunicationRequest`
- `LeadDuplicateRequest`
- `LeadLostRequest`
- `LeadPipelineMoveRequest`
- `LeadStatusActionRequest`
- `LeadTaskRequest`
- `LeadTaskCompletionRequest`
- `LeadDictionaryRequest`
- `LeadDictionaryDeleteRequest`
- `MessageTemplateRequest`
- `MessageTemplateDeleteRequest`
- public website intake requests: `StoreWebsiteLeadRequest`, `StoreCallbackLeadRequest`, `StoreContactLeadRequest`, and `StoreEnrollmentLeadRequest`

### Custom Rules

- `ActiveLeadSource`
- `ActiveMessageTemplateForChannel`
- `DifferentMarketingLead`
- `EditableLeadDictionaryRecordRule`

## Database Foundation

The canonical lead table is `marketing_leads`. The `Lead` model is a compatibility model that maps to this table so the public website and CRM do not create duplicate lead storage.

CRM dictionaries use:

- `lead_statuses`
- `lead_sources`
- `lead_lost_reasons`
- `lead_tags`

Lead timeline and work tables use:

- `marketing_lead_activities`
- `marketing_lead_tasks`
- `marketing_lead_communications` for call logs and other communication records
- `marketing_lead_comments` for internal notes
- `marketing_lead_status_histories` for status movement history
- `lead_tag_marketing_lead` for lead tags

The requested CRM concepts are mapped onto existing local-driving-school columns where they already exist: `status` and `source` store dictionary codes, `responsible_manager_id` stores the manager, `lost_reason_code` stores the lost reason, `training_program_id` stores the course, `budget_cents` stores money safely, and `converted_student_profile_id` prepares future student conversion.

## Factories And Seeders

Factories exist for CRM leads, dictionaries, tasks, activities, comments, communications, documents, status history, message templates, and campaigns. CRM dictionaries are seeded by `CrmDictionarySeeder` using factory-built payloads and stable `updateOrCreate` keys.

The application demo data in `DatabaseSeeder` seeds local driving school CRM examples, calls `CrmDictionarySeeder`, and uses `SuperadminPermissions::enabled()` for the local superadmin permission set.

## Permissions

CRM permissions include viewing, creating, updating, assigning, changing status, managing tasks, managing dictionaries, viewing marketing data, converting, and exporting leads. The superadmin role is seeded with all local CRM permissions.

Permission keys:

- `crm.leads.view`
- `crm.leads.create`
- `crm.leads.update`
- `crm.leads.delete`
- `crm.leads.assign`
- `crm.leads.change_status`
- `crm.leads.manage_tasks`
- `crm.leads.manage_dictionaries`
- `crm.leads.view_marketing`
- `crm.leads.convert`
- `crm.leads.export`

Website lead screens may also use `website.view_leads`, `website.update_leads`, and `website.view_marketing` for the public website management surface.

## Routes

Orchid routes are registered under:

- `platform.marketing.leads`
- `platform.marketing.leads.create`
- `platform.marketing.leads.edit`
- `platform.marketing.pipeline`
- `platform.crm.tasks`
- `platform.crm.dictionaries`
- `platform.crm.dictionaries.create`
- `platform.crm.dictionaries.edit`
- `platform.marketing.templates`
- `platform.marketing.templates.create`
- `platform.marketing.templates.edit`

Public lead intake routes are provided by the public website block and submit into the same CRM tables.

## Translation Keys

All visible CRM UI labels, validation errors, notifications, permissions, status labels, source labels, task labels, communication labels, and dictionary labels are stored as translation keys and seeded for `ru`, `en`, `lt`, and `pl` where applicable.

Important groups:

- `menu.crm.*`
- `crm.leads.*`
- `crm.pipeline.*`
- `crm.tasks.*`
- `crm.activities.*`
- `crm.communications.*`
- `crm.dictionaries.*`
- `crm.message_templates.*`
- `crm.validation.*`
- `permissions.crm.*`

## Conversion Boundary

`ReadyToEnroll` is the handoff status for Block 3. Block 2 stores the CRM fields needed for conversion, but it does not create the full student module or financial/lesson records.

## Verification

Run focused CRM verification first:

```bash
php artisan test --filter=DrivingSchoolPlatformTest
php artisan test --filter=CrmLocalizationTest
php artisan test --filter=CrmActionsRequestsRulesTest
php artisan test --filter=SuperadminRoleTest
```

Then run the full suite when focused checks pass:

```bash
php artisan test
```
