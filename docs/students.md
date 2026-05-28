# Students And Enrollments

Project baseline: follow [`docs/project-specs.md`](project-specs.md) and [`AGENTS.md`](../AGENTS.md). Student work is Laravel + Orchid + Blade, uses Eloquent only, and keeps all visible admin/public text translatable.

Block 3 manages students and enrollments for one local driving school. It is not a SaaS module and does not add tenants, subscription billing, reseller behavior, platform-owner dashboards, or multi-company isolation.

## Architecture

The canonical storage is the existing school tables:

- `student_profiles`: student records
- `enrollments`: student enrollment records
- `student_statuses`: translatable student status dictionary
- `enrollment_statuses`: translatable enrollment status dictionary
- `student_activities`: student and enrollment timeline
- `student_tasks`: onboarding and follow-up tasks

`App\Models\Student` maps to `student_profiles`. `App\Models\StudentEnrollment` maps to `enrollments`. These compatibility models keep Block 3 aligned with the existing school schema and avoid duplicate `students` or `student_enrollments` tables.

Students link back to CRM through `source_lead_id`. Enrollments can link to CRM through `lead_id`. Converted CRM leads store `converted_student_profile_id`, `converted_enrollment_id`, and `converted_at`.

## Student Lifecycle

Student statuses are seeded by `StudentStatusSeeder`.

- `active`: normal active student
- `inactive`: not active, but not archived
- `blocked`: blocked from normal actions
- `archived`: closed or hidden from regular workflows

Only one student status is default. The default is `active`.

Status changes are handled by `ChangeStudentStatusAction` and validated by `ValidStudentStatusTransitionRule`. Archived students are protected by `StudentCanBeUpdatedRule` and `UpdateStudentAction` unless an explicit override is allowed.

## Enrollment Lifecycle

Enrollment statuses are seeded by `EnrollmentStatusSeeder`.

- `draft`
- `waiting_documents`
- `waiting_payment`
- `waiting_start`
- `active`
- `theory`
- `practice`
- `ready_internal_exam`
- `ready_state_exam`
- `completed`
- `paused`
- `cancelled`
- `expelled`
- `archived`

The default enrollment status is `waiting_documents`.

Enrollment changes are handled by `CreateStudentEnrollmentAction`, `UpdateStudentEnrollmentAction`, and `ChangeEnrollmentStatusAction`. Completed and cancelled enrollments are protected by `StudentEnrollmentCanBeUpdatedRule` and `UpdateStudentEnrollmentAction` unless an override is explicitly allowed.

## Actions

Student identity and data:

- `GenerateStudentNumberAction`
- `NormalizeStudentPhoneAction`
- `FindMatchingStudentsAction`
- `CreateStudentAction`
- `UpdateStudentAction`
- `ArchiveStudentAction`
- `ChangeStudentStatusAction`
- `AssignStudentManagerAction`
- `AddStudentNoteAction`

Enrollment workflow:

- `GenerateEnrollmentNumberAction`
- `CreateStudentEnrollmentAction`
- `UpdateStudentEnrollmentAction`
- `ChangeEnrollmentStatusAction`
- `AssignEnrollmentGroupAction`
- `AddStudentToTrainingGroupAction`

Tasks and onboarding:

- `CreateStudentTaskAction`
- `CompleteStudentTaskAction`
- `CancelStudentTaskAction`
- `CreateStudentOnboardingTasksAction`
- `PrepareStudentDocumentsPlaceholderAction`
- `PrepareStudentPaymentPlaceholderAction`
- `CreatePortalAccessPlaceholderAction`

Dictionary management:

- `CreateOrUpdateStudentStatusAction`
- `DeleteStudentStatusAction`
- `CreateOrUpdateEnrollmentStatusAction`
- `DeleteEnrollmentStatusAction`

Export and filters:

- `FilterStudentsAction`
- `ExportStudentsCsvAction`

Screens, controllers, jobs, and commands should call Actions instead of containing business logic directly.

## Form Requests

Student Requests:

- `StoreStudentRequest`
- `UpdateStudentRequest`
- `StudentRequest`
- `ArchiveStudentRequest`
- `ChangeStudentStatusRequest`
- `AssignStudentManagerRequest`
- `AddStudentNoteRequest`

Enrollment Requests:

- `StoreStudentEnrollmentRequest`
- `UpdateStudentEnrollmentRequest`
- `StudentEnrollmentRequest`
- `ChangeEnrollmentStatusRequest`
- `AssignEnrollmentGroupRequest`

Task and placeholder Requests:

- `StoreStudentTaskRequest`
- `CompleteStudentTaskRequest`
- `CancelStudentTaskRequest`
- `CreatePortalAccessRequest`

Conversion Request:

- `ConvertLeadToStudentRequest`

All validation messages and attributes are translated through `tkey()` and seeded translation keys.

## Rules

Student rules:

- `StudentPhoneOrEmailRequiredRule`
- `UniqueStudentContactRule`
- `StudentCanBeArchivedRule`
- `StudentCanBeUpdatedRule`
- `ValidStudentStatusTransitionRule`
- `ActiveStudentStatusRule`
- `StudentNumberFormatRule`

Enrollment rules:

- `ValidEnrollmentStatusTransitionRule`
- `StudentEnrollmentCanBeUpdatedRule`
- `EnrollmentCanJoinGroupRule`
- `ActiveEnrollmentStatusRule`
- `EnrollmentNumberFormatRule`
- `ValidTrainingLanguageRule`
- `ValidGearboxTypeRule`
- `ValidTrainingFormatRule`

Task rules:

- `ValidStudentTaskStatusRule`
- `ValidStudentTaskPriorityRule`
- `TranslatedStudentTaskTitleRequiredRule`

Conversion rules:

- `LeadCanConvertToStudentRule`
- `LeadNotAlreadyConvertedRule`
- `ExistingStudentCanBeUsedForConversionRule`
- `EnrollmentNotDuplicateForStudentRule`

Dictionary rules:

- `StudentStatusCodeRule`
- `EnrollmentStatusCodeRule`
- `TranslatedDictionaryNameRequiredRule`
- `DictionaryItemCanBeDeletedRule`
- `OnlyOneDefaultStudentStatusRule`
- `OnlyOneDefaultEnrollmentStatusRule`
- `SystemDictionaryCodeProtectedRule`

Rule failures return translation keys, not hardcoded visible text.

## Factories

Factories exist for Block 3 models:

- `StudentStatusFactory`
- `EnrollmentStatusFactory`
- `StudentFactory`
- `StudentEnrollmentFactory`
- `StudentActivityFactory`
- `StudentTaskFactory`

Related module factories are reused where needed:

- `LeadFactory`
- `MarketingLeadFactory`
- `CourseFactory`
- `BranchFactory`
- `TrainingGroupFactory`
- `UserFactory`

## Seeders

Student seeders:

- `StudentStatusSeeder`
- `EnrollmentStatusSeeder`
- `StudentDictionarySeeder`
- `StudentTranslationSeeder`

Dictionary seeders are idempotent and use stable codes. Demo records should be created through factories. There is no active `StudentDemoSeeder` in this block.

## Permissions

Student permissions:

- `students.view`
- `students.create`
- `students.update`
- `students.archive`
- `students.delete`
- `students.change_status`
- `students.override_status_transition`
- `students.convert_from_lead`
- `students.manage_enrollments`
- `students.enrollments.change_status`
- `students.enrollments.override_status_transition`
- `students.manage_tasks`
- `students.view_crm_source`
- `students.view_marketing`
- `students.manage_statuses`
- `students.export`

CRM source data requires `students.view_crm_source`. Marketing data requires `students.view_marketing` or `crm.leads.view_marketing`. Superadmin receives the local permission set through `SuperadminPermissions::enabled()` and `SuperadminRoleSeeder`.

## Orchid Screens

Student screens:

- `StudentListScreen`
- `StudentEditScreen`
- `StudentEnrollmentEditScreen`
- `StudentTaskListScreen`
- `StudentStatusListScreen`
- `EnrollmentStatusListScreen`
- `LeadConvertToStudentScreen`

Screens are expected to stay thin: they build queries, render fields, authorize access, validate with Form Requests, and call Actions.

## Onboarding Tasks

`CreateStudentOnboardingTasksAction` creates these default tasks:

- `verify_personal_data`
- `request_documents`
- `prepare_contract`
- `check_payment`
- `assign_group`
- `create_portal_access`

The action is idempotent by default and does not duplicate open onboarding tasks unless explicitly changed later.

## Activities

Student activities record:

- student creation and updates
- lead conversion
- enrollment creation and updates
- status changes
- manager assignment
- group assignment or changes
- notes
- task creation and completion
- portal access placeholder creation
- document placeholder creation
- payment placeholder creation

Activities use translated type labels in the UI.

## Placeholders

Block 3 intentionally does not implement full documents, payments, or student cabinet.

Document placeholder data lives in `documents_summary`, for example:

```json
{
  "identity_document": "missing",
  "medical_certificate": "missing",
  "photo": "missing",
  "contract": "not_created"
}
```

Payment placeholder data lives in `payment_summary`, for example:

```json
{
  "payment_status": "pending",
  "expected_price": null,
  "currency": "EUR"
}
```

Portal placeholder data uses `portal_access_created_at`. Full cabinet account creation belongs to a later module.

## Filters And Export

`FilterStudentsAction` supports search, status, enrollment status, manager, administrator, course, category, branch, training group, created date range, active/archived/blocked flags, active enrollment flags, waiting documents/payment/start flags, without-group flag, and quick segments.

`ExportStudentsCsvAction` streams CSV safely.

Access rules:

- export requires `students.export`
- CRM source columns require `students.view_crm_source`
- marketing columns require `students.view_marketing` or `crm.leads.view_marketing`
- soft-deleted students are excluded by default

## Translation Keys

Visible UI uses translation keys through `tkey()`. Main groups:

- `menu.students.*`
- `students.fields.*`
- `students.enrollments.fields.*`
- `students.statuses.*`
- `students.enrollments.statuses.*`
- `students.actions.*`
- `students.messages.*`
- `students.validation.*`
- `students.conversion.*`
- `students.tasks.*`
- `students.activities.types.*`
- `permissions.students.*`
- `validation.attributes.student.*`
- `validation.attributes.student_enrollment.*`
- `validation.attributes.student_task.*`

Student and enrollment dictionary names use `name_translations` and fall back safely.

## Known TODOs

- Implement full documents module.
- Implement full payments and invoices module.
- Implement full student cabinet and user account lifecycle.
- Integrate dedicated group membership records if a group membership table is introduced.
- Add richer analytics after the operational workflow is stable.
