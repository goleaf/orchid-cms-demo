# Lead To Student Conversion

Project baseline: follow [`docs/project-specs.md`](project-specs.md) and [`AGENTS.md`](../AGENTS.md). Conversion work is Laravel + Orchid + Blade, uses Eloquent only, and keeps all visible admin/public text translatable.

Lead-to-student conversion connects the previous modules into one operational flow:

Public website form -> CRM Lead -> Lead processing -> Student -> StudentEnrollment -> optional TrainingGroup assignment.

The flow is for one local driving school. It does not add SaaS tenants, subscription billing, reseller logic, platform-owner dashboards, or multi-company isolation.

## Storage Model

Conversion reuses existing tables:

- CRM leads live in `marketing_leads`.
- Students live in `student_profiles`.
- Enrollments live in `enrollments`.
- Student timeline records live in `student_activities`.
- Onboarding tasks live in `student_tasks`.

Converted leads store:

- `converted_at`
- `converted_student_profile_id`
- `converted_enrollment_id`
- `closed_at`
- successful lead status, usually `enrolled`

Students link back through `source_lead_id`. Enrollments link back through `lead_id`.

## Conversion Flow

1. Validate the lead.
2. Find possible matching students.
3. Prepare initial student and enrollment data.
4. Convert into a new student or link to an existing student.
5. Create `StudentEnrollment`.
6. Assign the enrollment to a TrainingGroup when a group is selected and valid.
7. Mark the CRM lead as converted.
8. Create CRM and student activities.
9. Create onboarding tasks and placeholders when selected.
10. Redirect staff to the student card.

`ConvertLeadToStudentAction` wraps the critical conversion in a database transaction. If a critical step fails, for example a full or closed group rejects the enrollment, the student, enrollment, lead conversion fields, tasks, and placeholders roll back.

## Actions

Conversion Actions:

- `ValidateLeadForStudentConversionAction`
- `FindStudentMatchesForLeadAction`
- `PrepareLeadConversionDataAction`
- `ConvertLeadToStudentAction`
- `LinkLeadToExistingStudentAction`
- `MarkLeadAsConvertedAction`
- `BuildLeadConversionWarningsAction`

Student and enrollment Actions used by conversion:

- `CreateStudentAction`
- `UpdateStudentAction`
- `CreateStudentEnrollmentAction`
- `AddStudentToTrainingGroupAction`
- `CreateStudentOnboardingTasksAction`
- `PrepareStudentDocumentsPlaceholderAction`
- `PrepareStudentPaymentPlaceholderAction`
- `CreatePortalAccessPlaceholderAction`

Lead Actions used by conversion:

- `RecordLeadActivityAction`
- `MarkLeadAsConvertedAction`

## Validation

`ValidateLeadForStudentConversionAction` blocks conversion when:

- the lead is soft-deleted
- the lead is already converted
- the lead is spam
- the lead is lost without override permission
- the lead is duplicate without override permission
- the lead has no phone or email
- the lead has no usable name or contact
- no course/category is present or provided
- a public website lead has no consent and no override
- the current status is outside the allowed conversion statuses and no override is present

Default convertible statuses are:

- `contacted`
- `waiting_documents`
- `waiting_payment`
- `ready_to_enroll`

Validation and Form Request rules use translated failures:

- `LeadCanConvertToStudentRule`
- `LeadNotAlreadyConvertedRule`
- `ExistingStudentCanBeUsedForConversionRule`
- `EnrollmentNotDuplicateForStudentRule`
- `EnrollmentCanJoinGroupRule`

## Duplicate Student Detection

`FindStudentMatchesForLeadAction` and `FindMatchingStudentsAction` search possible existing students by:

- normalized phone
- email
- personal code when available
- full name as a weaker match

Matches are warnings for staff. They do not automatically block conversion unless a duplicate enrollment would be created for the selected existing student.

## New Student Conversion

When no existing student is selected, `ConvertLeadToStudentAction` calls `CreateStudentAction`.

Inherited fields include:

- full name and name parts
- phone and normalized phone
- email
- preferred messenger
- city
- locale
- consent state and consent timestamp
- source lead
- manager
- source label
- lead comment and internal comment

## Existing Student Conversion

When an existing student is selected, conversion uses the existing `student_profiles` row. `LinkLeadToExistingStudentAction` delegates to `ConvertLeadToStudentAction`, creates a new enrollment, marks the lead as converted, and records activities.

`ExistingStudentCanBeUsedForConversionRule` protects archived or invalid student choices.

`EnrollmentNotDuplicateForStudentRule` prevents creating another active enrollment for the same selected course/group.

## Enrollment Creation

`CreateStudentEnrollmentAction` creates the enrollment and uses `AddStudentToTrainingGroupAction` if a training group is selected.

Transferred lead fields include:

- `training_program_id`
- `course_category_id`
- `branch_id`
- `training_group_id`
- desired start date as start date when appropriate
- preferred time
- preferred training language
- preferred gearbox
- budget as price when present
- manager

## Training Group Integration

When TrainingGroup exists and a group is selected:

- `AddStudentToTrainingGroupAction` validates that the group is not full and accepts enrollment.
- It validates that the enrollment course matches the group course.
- It sets `training_group_id` on the enrollment.
- It fills missing course, category, branch, and instructor fields from the group.
- It increments `training_groups.places_taken`.
- It records `group_assigned` or `group_changed` student activity.

If the group changes later, the old group capacity is decremented and the new group capacity is incremented.

The group foundation includes dedicated membership records. Group assignment keeps the enrollment's group fields, group capacity, membership history, and group activity timeline synchronized through the group assignment Action.

## CRM Integration

After successful conversion, `MarkLeadAsConvertedAction` updates the lead:

- `converted_at`
- `converted_student_profile_id`
- `converted_enrollment_id`
- `closed_at`
- status `enrolled`

It records a CRM activity of type `converted`.

`LeadEditScreen` shows:

- Convert to Student button when the lead is not converted and the user has both `crm.leads.convert` and `students.convert_from_lead`
- converted student link when converted
- converted enrollment link when converted
- converted timestamp

Converted leads cannot be converted again.

## Student CRM Source Block

`StudentEditScreen` shows CRM source data only with `students.view_crm_source`.

Visible source fields:

- source lead
- source status
- source
- source manager
- lead created at
- lead converted at
- source lead link

Marketing fields are visible only with `students.view_marketing` or `crm.leads.view_marketing`.

Visible marketing fields:

- UTM source
- UTM medium
- UTM campaign
- landing page
- form page
- form name

## Onboarding After Conversion

When enabled, conversion creates onboarding tasks:

- verify personal data
- request documents
- prepare contract
- check payment
- assign group
- create portal access

When enabled, conversion also prepares document and payment placeholders. It does not implement full document uploads, payments, invoices, or student cabinet.

## Orchid Screen

`LeadConvertToStudentScreen` is the conversion screen.

It shows:

- lead check
- blocking errors and warnings
- possible matching students
- choice to create a new student or use an existing student
- editable student data
- editable enrollment data
- final confirmation options

The screen validates through `ConvertLeadToStudentRequest` and calls `ConvertLeadToStudentAction`.

## Permissions

Conversion requires both permissions:

- `crm.leads.convert`
- `students.convert_from_lead`

Student source data requires:

- `students.view_crm_source`

Marketing attribution requires one of:

- `students.view_marketing`
- `crm.leads.view_marketing`

## Translation Keys

Conversion UI and validation use keys under:

- `students.conversion.*`
- `students.conversion.validation.*`
- `students.conversion.warnings.*`
- `students.actions.*`
- `crm.leads.fields.*`
- `crm.activities.types.converted`

Validation errors are translated through `tkey()`.

## Verification

Primary tests:

- `StudentCrmIntegrationTest`
- `StudentFinalHardeningTest`
- `StudentActionsRequestsRulesTest`
- `StudentOrchidAdminUiTest`
- `StudentOnboardingWorkflowTest`
- `StudentFiltersExportTest`
- `StudentDatabaseFoundationTest`

The full suite can be run with:

```bash
php artisan test
```

## Known TODOs

- Implement full documents module.
- Implement full payment and invoice module.
- Implement full student cabinet and account lifecycle.
- Add analytics after operational workflows are stable.
