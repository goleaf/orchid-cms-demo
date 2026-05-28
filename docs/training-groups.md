# Training Groups and Basic Education Structure

Project baseline: follow [`docs/project-specs.md`](project-specs.md) and [`AGENTS.md`](../AGENTS.md). Education group work is Laravel + Orchid + Blade, uses Eloquent only, and keeps all visible admin/public text translatable.

Block 4 adds the education group foundation for the local driving school. It reuses existing course, student, enrollment, branch, and CRM lead storage instead of creating duplicate business tables.

## Purpose

Training groups connect public website group selection, CRM lead conversion, student enrollments, and the future education process. A group can expose public capacity, accept enrollments, keep a membership list, hold basic schedule patterns, and record a group activity timeline.

## Models

- `TrainingGroup`: existing `training_groups` model extended with `status_id`, `enrollment_closes_on`, `learning_notes`, `schedule_notes`, memberships, schedule patterns, and activities.
- `TrainingGroupStatus`: dictionary for group lifecycle and enrollment rules.
- `TrainingGroupMembership`: links `StudentEnrollment` and `Student` to a group while preserving membership history.
- `LearningProgram`: compatibility model over `training_programs`.
- `LearningProgramModule`: compatibility model over `course_modules`.
- `LearningTopic`: reusable program topics for future lesson and attendance modules.
- `TrainingGroupSchedulePattern`: recurring day/time pattern for future calendar generation.
- `TrainingGroupActivity`: timeline for membership, schedule, and status events.

## Data Reuse

- Courses and learning programs use `training_programs`.
- Learning modules use `course_modules`.
- Students use `student_profiles` through `Student`.
- Enrollments use `enrollments` through `StudentEnrollment`.
- CRM leads use `marketing_leads`.
- No SaaS tenant, subscription, reseller, or multi-company tables are part of this block.

## Actions

- `CreateOrUpdateTrainingGroupAction`
- `SaveTrainingGroupAction`
- `AddStudentToTrainingGroupAction`
- `RemoveStudentFromTrainingGroupAction`
- `CreateOrUpdateTrainingGroupStatusAction`
- `DeleteTrainingGroupStatusAction`
- `CreateOrUpdateLearningProgramAction`
- `CreateOrUpdateLearningProgramModuleAction`
- `CreateOrUpdateLearningTopicAction`
- `CreateOrUpdateTrainingGroupSchedulePatternAction`
- `RecordTrainingGroupActivityAction`

All group membership changes go through Actions so capacity, enrollment links, memberships, and activity records stay synchronized.

## Form Requests

- `TrainingGroupRequest`
- `Education\TrainingGroupStatusRequest`
- `Education\TrainingGroupMembershipRequest`
- `Education\LearningTopicRequest`
- `Education\TrainingGroupSchedulePatternRequest`

## Rules

- `ActiveTrainingGroupStatusRule`
- `TrainingGroupCanAcceptEnrollmentRule`
- `TrainingGroupEnrollmentMatchesProgramRule`
- `TrainingGroupMembershipNotDuplicateRule`
- `ValidTrainingGroupStatusRule`
- `ValidLearningTopicTypeRule`
- `ValidScheduleDayRule`
- `ValidSchedulePatternTimeRule`

Validation messages use translation keys under `education.validation.*`.

## Factories

- `TrainingGroupStatusFactory`
- `TrainingGroupMembershipFactory`
- `LearningProgramFactory`
- `LearningProgramModuleFactory`
- `LearningTopicFactory`
- `TrainingGroupSchedulePatternFactory`
- `TrainingGroupActivityFactory`
- Existing `TrainingGroupFactory` now supports the added group fields.

## Seeders

- `TrainingGroupStatusSeeder`: idempotent group status dictionary.
- `EducationTranslationSeeder`: idempotent education translation keys for `ru`, `en`, `lt`, and `pl`.
- `EducationGroupSeeder`: optional factory-backed demo group/program structure.
- `EducationSeeder`: wrapper for education translations and group statuses.

## Permissions

- `education.groups.view`
- `education.groups.create`
- `education.groups.update`
- `education.manage_statuses`
- `education.manage_memberships`
- `education.manage_schedule_patterns`
- `education.manage_topics`
- `education.view_activities`

Superadmin receives these permissions through `SuperadminPermissions`.

## Orchid Screens

- `GroupListScreen`: reused for operations and education group lists.
- `GroupEditScreen`: group details, capacity fields, status dictionary, memberships, schedule patterns, and activity timeline.
- `TrainingGroupStatusListScreen`: dictionary management.
- `LearningTopicListScreen`: program topic management.
- `TrainingGroupSchedulePatternListScreen`: recurring pattern management.

Routes:

- `platform.education.groups`
- `platform.education.groups.create`
- `platform.education.groups.edit`
- `platform.education.group-statuses`
- `platform.education.learning-topics`
- `platform.education.schedule-patterns`

## Public and CRM Flow

1. Public website can show visible groups.
2. Lead can select a group.
3. Lead conversion creates `StudentEnrollment`.
4. `AddStudentToTrainingGroupAction` links the enrollment to `TrainingGroupMembership`.
5. Group capacity updates through `places_taken`.
6. `TrainingGroupActivity` records the group timeline.

The same group assignment flow is used from student enrollment creation and lead conversion, so group capacity, active membership records, enrollment group fields, and student/group activity entries stay in sync.

## Translations

Education UI labels, permission labels, dictionary labels, activity labels, and validation errors are seeded by `EducationTranslationSeeder`.

## Tests

`EducationGroupBlockTest` verifies:

- Database foundation and table reuse.
- Relationships, helpers, and scopes.
- Idempotent status and translation seeders.
- Group membership Action capacity behavior.
- Validation rules and translated errors.
- Orchid routes and permission gates.
- Required Actions, Requests, Rules, factories, seeders, and screens.

## TODOs

- Full lesson calendar generation from schedule patterns.
- Attendance tracking.
- Theory lesson events.
- Practical driving lesson events.
- Exam scheduling.
- Document, contract, payment, payroll, and student cabinet modules.
- External SMS, WhatsApp, telephony, and AI integrations.
