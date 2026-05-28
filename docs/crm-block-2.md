# Block 2: CRM Lead Management Foundation

Project baseline: follow [`docs/project-specs.md`](project-specs.md) and [`AGENTS.md`](../AGENTS.md). This block is Laravel + Orchid + Blade, uses Eloquent only, and must keep all visible admin/public text translatable.

This block implements CRM lead management for one local driving school company. It is not a SaaS module and does not include tenants, subscriptions, reseller flows, platform-owner dashboards, or multi-company isolation.

## Scope

The CRM receives website leads from the public enrollment and callback forms, lets managers process leads in Orchid, tracks communication, notes, tasks, statuses, duplicate links, UTM data, tags, and lost reasons, and prepares a lead for conversion into a student.

Student records are handled by the student and conversion modules. Payments, invoices, lessons, exams, payroll, external SMS or telephony integrations, WhatsApp integrations, and AI scoring remain outside this block.

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

CRM write operations use Actions and Form Requests. Custom validation Rules handle lead state, status transitions, active dictionaries, contact requirements, marketing data access, channel-compatible message templates, and preventing a lead from being marked as its own duplicate.

Seeded dictionaries are built through model factories while preserving update-or-create behavior for stable system records. Tests use factories for CRM records and seeded dictionaries for localized labels.

### Actions

- `GenerateLeadNumberAction`
- `NormalizeLeadPhoneAction`
- `CreateLeadAction`
- `CreateWebsiteLeadAction`
- `CreateCallbackLeadAction`
- `UpdateLeadAction`
- `ChangeLeadStatusAction`
- `SaveMarketingLeadCrmAction`
- `UpdateMarketingLeadCrmAction`
- `AssignLeadManagerAction`
- `MoveLeadToStatusAction`
- `AddLeadNoteAction`
- `MarkLeadLostAction`
- `MarkLeadDuplicateAction`
- `MarkLeadSpamAction`
- `ReopenLeadAction`
- `LogLeadCallAction`
- `PrepareLeadForEnrollmentAction`
- `PrepareLeadForStudentConversionAction`
- `DetectLeadDuplicateAction`
- `RecordLeadActivityAction`
- `AddLeadCommentAction`
- `AddLeadCommunicationAction`
- `CreateLeadTaskAction`
- `CompleteLeadTaskAction`
- `CancelLeadTaskAction`
- `ExportLeadsCsvAction`
- `ExportMarketingLeadsCsvAction`
- `GetLeadPipelineAction`
- `CreateOrUpdateLeadStatusAction`
- `CreateOrUpdateLeadSourceAction`
- `CreateOrUpdateLeadLostReasonAction`
- `CreateOrUpdateLeadTagAction`
- `SaveMarketingMessageTemplateAction`
- `DeleteMarketingMessageTemplateAction`
- `SaveLeadDictionaryAction`
- `DeleteLeadDictionaryAction`
- `ResolveLeadSourceAction`
- `ResolveLeadNotificationRecipientsAction`

### Form Requests

- `StoreLeadRequest`
- `UpdateLeadRequest`
- `ChangeLeadStatusRequest`
- `AssignLeadManagerRequest`
- `AddLeadNoteRequest`
- `LogLeadCallRequest`
- `StoreLeadTaskRequest`
- `CompleteLeadTaskRequest`
- `CancelLeadTaskRequest`
- `MarkLeadLostRequest`
- `MarkLeadDuplicateRequest`
- `MarkLeadSpamRequest`
- `ReopenLeadRequest`
- `ExportLeadsRequest`
- `StoreLeadStatusRequest`
- `UpdateLeadStatusRequest`
- `StoreLeadSourceRequest`
- `UpdateLeadSourceRequest`
- `StoreLeadLostReasonRequest`
- `UpdateLeadLostReasonRequest`
- `StoreLeadTagRequest`
- `UpdateLeadTagRequest`
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

- `PhoneOrEmailRequiredRule`
- `ValidLeadStatusTransitionRule`
- `LeadCanBeUpdatedRule`
- `LeadCanBeConvertedRule`
- `LeadIsNotDuplicateOfItselfRule`
- `LeadDuplicateOriginalRule`
- `ActiveLeadStatusRule`
- `ActiveLeadSourceRule`
- `ActiveLeadLostReasonRule`
- `ActiveLeadTagRule`
- `ValidLeadPriorityRule`
- `ValidLeadTaskStatusRule`
- `ValidLeadTaskPriorityRule`
- `ValidLeadCallResultRule`
- `FutureFollowUpDateRule`
- `TranslatedDictionaryNameRequiredRule`
- `DictionaryCodeRule`
- `LeadMarketingAccessRule`
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

The requested CRM concepts are mapped onto existing local-driving-school columns where they already exist: `status` and `source` store dictionary codes, `responsible_manager_id` stores the manager, `lost_reason_code` stores the lost reason, `training_program_id` stores the course, `budget_cents` stores money safely, and conversion fields link successful leads to students and enrollments.

### CRM Table Compatibility Map

Block 2 intentionally reuses the existing marketing lead schema instead of creating duplicate lead tables:

| CRM concept | Physical table / column strategy |
| --- | --- |
| `leads` | `marketing_leads`, exposed through `App\Models\Lead` |
| `lead_statuses` | `lead_statuses`, related by `marketing_leads.status = lead_statuses.code` |
| `lead_sources` | `lead_sources`, related by `marketing_leads.source = lead_sources.code` |
| `lead_lost_reasons` | `lead_lost_reasons`, related by `marketing_leads.lost_reason_code = lead_lost_reasons.code` |
| `lead_tags` | `lead_tags` |
| `lead_lead_tag` | `lead_tag_marketing_lead`, keyed by `marketing_lead_id` and `lead_tag_id` |
| `lead_activities` | `marketing_lead_activities`, exposed through `App\Models\LeadActivity` |
| `lead_tasks` | `marketing_lead_tasks`, exposed through `App\Models\LeadTask` |
| `lead_call_logs` | not a separate table; phone call logs are `marketing_lead_communications` rows with `channel = phone` |

The compatibility models pin the `marketing_lead_id` foreign key for lead-owned relationships so Eloquent does not infer a non-existent `lead_id` column.

### Model Relationships

`Lead` exposes the CRM relationships required by the module:

- dictionary context: `status()`, `source()`, `lostReason()`
- ownership and audit: `manager()`, `createdBy()`, `creator()`, `updatedBy()`, `updater()`
- duplicate management: `duplicateOf()`, `duplicates()`
- tags and timeline: `tags()`, `activities()`, `tasks()`, `callLogs()`
- business context: `course()`, `courseCategory()`, `branch()`, `trainingGroup()`
- future conversion: `convertedStudent()`, `convertedEnrollment()`

Dictionary models expose `leads()`. `LeadActivity` exposes `lead()` and `user()`. `LeadTask` exposes `lead()`, `assignedTo()`, and `createdBy()`.

### Lead Scopes And Helpers

`Lead` inherits the CRM scopes from `MarketingLead`: `open`, `closed`, `new`, `assignedTo`, `unassigned`, `overdueFollowUp`, `dueToday`, `duplicates`, `spam`, `lost`, `converted`, `notConverted`, `byStatus`, `bySource`, `byManager`, `byCourse`, `byBranch`, `byTrainingGroup`, and `search`.

Display and state helpers include `display_name`, `display_contact`, `is_closed`, `is_converted`, `is_duplicate`, `is_spam`, `is_lost`, `is_overdue`, and `can_be_converted`. Compatibility accessors expose requested naming such as `course_id`, `manager_id`, `comment`, `preferred_messenger`, `preferred_training_language`, `referrer`, `budget`, and `converted_student_id`.

## Factories And Seeders

Factories exist for CRM leads, statuses, sources, lost reasons, tags, tasks, activities, comments, communications, documents, status history, message templates, and campaigns. Call logs are stored as phone `MarketingLeadCommunication` records, so there is no separate `LeadCallLog` table or factory.

CRM dictionaries are seeded by split idempotent seeders: `CrmStatusSeeder`, `CrmSourceSeeder`, `CrmLostReasonSeeder`, and `CrmTagSeeder`. `CrmDictionarySeeder` remains as a compatibility wrapper over those seeders, while `CrmSeeder` also runs CRM translations. Demo CRM leads are created by `CrmDemoLeadSeeder` through factories and stable demo email keys.

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

`ReadyToEnroll` is the handoff status for the conversion workflow. Block 2 stores the CRM fields needed for conversion, while student records and enrollments are created by the student conversion module. Financial and lesson records are still outside this block.

## Verification

Run focused CRM verification first:

```bash
php artisan test --filter=DrivingSchoolPlatformTest
php artisan test --filter=CrmLocalizationTest
php artisan test --filter=CrmActionsRequestsRulesTest
php artisan test --filter=CrmFactoriesSeedersTest
php artisan test --filter=SuperadminRoleTest
```

Then run the full suite when focused checks pass:

```bash
php artisan test
```
