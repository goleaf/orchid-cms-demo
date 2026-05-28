# Exams, Admissions, and Results

Project baseline: follow [`docs/project-specs.md`](project-specs.md) and [`AGENTS.md`](../AGENTS.md). Exam work is Laravel + Orchid + Blade, uses Eloquent only, and keeps all visible admin/public text translatable.

Block 10 adds the exam foundation for one local driving school. It does not add tenants, subscription billing, reseller behavior, platform-owner dashboards, official registry integration, state API sync, an online theory question bank, a full LMS testing engine, AI scoring, or a payment provider.

## Purpose

The exam module connects student readiness, documents, payments, lessons, groups, and exam outcomes. It gives the school a structured place to track internal theory exams, internal practical exams, state exam placeholders, admissions, sessions, attempts, results, retakes, and activity history.

## Storage

- `exam_types`: translatable exam type dictionary for internal theory, internal practical, official theory placeholders, and official practical placeholders.
- `exam_statuses`: translatable session lifecycle dictionary for draft, scheduled, open, in-progress, completed, cancelled, and archived sessions.
- `exam_attempt_statuses`: translatable attempt lifecycle dictionary for planned, allowed, blocked, in-progress, passed, failed, no-show, cancelled, and archived attempts.
- `exam_result_statuses`: translatable result lifecycle dictionary for pending, passed, failed, needs-retake, and cancelled outcomes.
- `exam_admission_rules`: reusable readiness rules by exam type, course, and course category.
- `exam_admissions`: readiness record for an enrollment and exam type.
- `exam_admission_checklist_items`: readiness checklist items for documents, payment, and training hours.
- `exam_sessions`: planned or completed internal sessions and official/state placeholders with normalized type and status links.
- `exam_participants`: students registered into an exam session with admission and block-state tracking.
- `exam_attempts`: scheduled attempts, results, failures, passes, no-shows, cancellations, and retakes with normalized attempt status links.
- `exam_results`: scored or manually decided attempt outcomes with examiner comments and mistake summaries.
- `exam_retakes`: retake planning links from failed, no-show, or cancelled attempts to the next attempt.
- `exam_checklist_items`: session or attempt checklist items tied directly to a student and enrollment.
- `exam_activities`: activity timeline for admissions, sessions, attempts, retakes, and result changes.

The module reuses existing school data:

- Students use `student_profiles` through `Student`.
- Enrollments use `enrollments` through `StudentEnrollment`.
- Groups use `training_groups`.
- Programs use `training_programs`.
- Practical work can link to `driving_lessons`.
- Readiness can link to `student_documents` and `payments`.
- Retake fees can be linked later through existing finance records.

## Exam Types

- `internal_theory`
- `internal_practical`
- `official_theory_placeholder`
- `official_practical_placeholder`

Official exams are placeholders only. They can store references and payloads for manual tracking, but there is no government registry integration or automatic official sync in this block. Legacy `state_*` dictionary aliases are kept inactive only for older records.

## Workflow

1. Create or update an exam admission for a student enrollment.
2. Build the readiness checklist from verified documents, paid payments, and completed hours.
3. Mark the admission ready when all blocking checklist items pass or are waived.
4. Schedule an exam session for an internal or official-placeholder exam.
5. Record an attempt result against the admission and optional session.
6. Mark the admission passed or requiring a retake.
7. Schedule retakes as child attempts of the failed, no-show, or cancelled attempt.
8. Record exam activity entries for audit history.

## Actions

- `CreateOrUpdateExamAdmissionAction`
- `ScheduleExamSessionAction`
- `RecordExamAttemptResultAction`
- `CreateExamRetakeAction`
- `RecordExamActivityAction`

Screens and future controllers should call Actions instead of embedding exam business rules directly.

## Form Requests

- `ExamAdmissionRequest`
- `ExamSessionRequest`
- `RecordExamAttemptRequest`
- `CreateExamRetakeRequest`

Requests authorize through platform exam permissions and return translated validation errors.

## Rules

- `ValidExamTypeRule`
- `ExamAdmissionReadyRule`
- `ExamSessionCanAcceptAttemptRule`
- `ExamAttemptCanBeRetakenRule`

Rule failures use exam translation keys under `exams.validation.*`.

## Factories and Seeders

Factories:

- `ExamTypeFactory`
- `ExamStatusFactory`
- `ExamAttemptStatusFactory`
- `ExamResultStatusFactory`
- `ExamAdmissionRuleFactory`
- `ExamAdmissionFactory`
- `ExamAdmissionChecklistItemFactory`
- `ExamSessionFactory`
- `ExamParticipantFactory`
- `ExamAttemptFactory`
- `ExamResultFactory`
- `ExamRetakeFactory`
- `ExamChecklistItemFactory`
- `ExamActivityFactory`

Seeders:

- `ExamTypeSeeder`
- `ExamStatusSeeder`
- `ExamAttemptStatusSeeder`
- `ExamResultStatusSeeder`
- `ExamAdmissionRuleSeeder`
- `ExamDictionarySeeder`
- `ExamTranslationSeeder`
- `DemoExamSeeder`
- `ExamDemoSeeder`
- `ExamSeeder`

Dictionary records are created through factories and are idempotent. The wrapper keeps the default exam types, session statuses, attempt statuses, result statuses, admission rules, and translations aligned across Russian, English, Lithuanian, and Polish. Demo records are guarded for local, demo, and testing environments and reuse existing student, enrollment, group, payment, and document records when available.

## Permissions

- `platform.exams`
- `exams.view`
- `exams.manage_admissions`
- `exams.manage_sessions`
- `exams.record_results`
- `exams.schedule_retakes`
- `exams.view_activities`

Granular permissions are also registered for session viewing, creation, updates, and cancellation; admission checks, approvals, and blocks; attempt viewing, creation, start, completion, and cancellation; result viewing, recording, and updates; retake viewing, creation, and scheduling; dictionary management; and CSV export.

Superadmin receives the broad and granular exam permissions through the shared permission list. These permissions stay local to the driving school and do not introduce tenant, subscription, reseller, or platform-owner access layers.

## Localization

Exam navigation, screen titles, form fields, actions, type labels, session statuses, attempt statuses, result statuses, validation messages, and permission labels are seeded for Russian, English, Lithuanian, and Polish.

Validation messages use `exams.validation.*` keys. The catalog keeps older compatibility keys and adds the granular keys used by the exam UI and permission foundation.

## Orchid

The exam menu opens the exam sessions list. The list reads from normalized exam sessions, eager loads branch, program, group, instructor, and vehicle context, and shows attempt counts without per-row queries.

Current route:

- `platform.exams`

Future screens should keep admissions, sessions, attempts, results, and retakes behind the same local-driving-school permissions.

## Tests

`ExamBlockFoundationTest` verifies:

- Database foundation and explicit non-goals.
- Relationships to students, enrollments, groups, lessons, documents, and payments.
- Actions for readiness, session scheduling, result recording, retakes, and activity history.
- Custom rules, FormRequest classes, translated validation errors, and permissions.
- Seeders and the Orchid exam route.

`ExamDatabaseModelsTest` verifies:

- The normalized exam database tables and requested columns.
- Model creation and relationships for types, statuses, admission rules, sessions, participants, attempts, results, retakes, checklist items, and activities.
- Translated display helpers and query scopes.
- Dictionary seed records for local internal exams and official placeholders.

`ExamFactoriesSeedersTest` verifies:

- Required factory states for exam types, statuses, sessions, attempts, results, retakes, checklist items, and activities.
- Idempotent exam seeders and wrapper seeding.
- Default internal and official placeholder exam dictionaries.
- Seeded dictionary and interface translations for Russian, English, Lithuanian, and Polish.

`ExamLocalizationPermissionsTest` verifies:

- Requested exam menu, screen, field, action, status, validation, and permission labels are translated for all active locales.
- Granular exam permissions are registered in the local superadmin permission list and the Orchid permission provider.
- Existing broad exam permissions remain registered for compatibility with current requests and screens.

## TODOs

- Full admission edit screen.
- Session edit screen.
- Attempt/result entry screen.
- Retake action from the student and exam views.
- Exam analytics widgets.
- Optional manual state exam reference management.
