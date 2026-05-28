# Exams, Admissions, and Results

Project baseline: follow [`docs/project-specs.md`](project-specs.md) and [`AGENTS.md`](../AGENTS.md). Exam work is Laravel + Orchid + Blade, uses Eloquent only, and keeps all visible admin/public text translatable.

Block 10 adds the exam foundation for one local driving school. It does not add tenants, subscription billing, reseller behavior, platform-owner dashboards, official registry integration, state API sync, an online theory question bank, a full LMS testing engine, AI scoring, or a payment provider.

## Purpose

The exam module connects student readiness, documents, payments, lessons, groups, and exam outcomes. It gives the school a structured place to track internal theory exams, internal practical exams, state exam placeholders, admissions, sessions, attempts, results, retakes, and activity history.

See [`docs/exam-admissions.md`](exam-admissions.md) for the detailed admission checklist rules and participant recheck behavior.

## Storage

- `exam_types`: translatable exam type dictionary for internal theory, internal practical, official theory placeholders, and official practical placeholders.
- `exam_statuses`: translatable session lifecycle dictionary for draft, scheduled, open, in-progress, completed, cancelled, and archived sessions.
- `exam_attempt_statuses`: translatable attempt lifecycle dictionary for planned, allowed, blocked, in-progress, passed, failed, no-show, cancelled, and archived attempts.
- `exam_result_statuses`: translatable result lifecycle dictionary for pending, passed, failed, needs-retake, and cancelled outcomes.
- `exam_admission_rules`: reusable readiness rules by exam type, course, and course category.
- `exam_admissions`: readiness record for an enrollment and exam type.
- `exam_admission_checklist_items`: readiness checklist items for documents, payments, hours, internal prerequisites, enrollment status, student status, and manual review.
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
2. Build the readiness checklist from verified documents, payment status, completed hours, internal prerequisites, enrollment status, student status, and manual review state.
3. Approve the admission when all blocking checklist items pass, or block it with a translated validation reason. Manual approval keeps failed automatic checks as warnings.
4. Create and update exam sessions with normalized exam type and session status records.
5. Add or remove students from sessions while respecting capacity and enrollment readiness.
6. Create, start, complete, cancel, or mark no-show attempt records.
7. Record results, mark pass or fail decisions, and preserve examiner comments and mistake summaries.
8. Create retake records and link them to new attempts when the next attempt is scheduled.
9. Record exam activity entries for audit history.

## Actions

- `CreateOrUpdateExamAdmissionAction`
- `ScheduleExamSessionAction`
- `RecordExamAttemptResultAction`
- `CreateExamRetakeAction`
- `RecordExamActivityAction`
- `GenerateExamNumberAction`
- `GenerateExamAttemptNumberAction`
- `CreateExamSessionAction`
- `UpdateExamSessionAction`
- `ChangeExamSessionStatusAction`
- `CancelExamSessionAction`
- `AddStudentToExamSessionAction`
- `RemoveStudentFromExamSessionAction`
- `CheckExamAdmissionAction`
- `BuildExamAdmissionChecklistAction`
- `ApproveExamAdmissionAction`
- `BlockExamAdmissionAction`
- `RecheckExamSessionAdmissionsAction`
- `CreateExamAttemptAction`
- `StartExamAttemptAction`
- `CompleteExamAttemptAction`
- `MarkExamAttemptNoShowAction`
- `CancelExamAttemptAction`
- `RecordExamResultAction`
- `MarkExamPassedAction`
- `MarkExamFailedAction`
- `ScheduleExamRetakeAction`
- `AddExamActivityAction`

Screens and future controllers should call Actions instead of embedding exam business rules directly.

## Form Requests

- `ExamAdmissionRequest`
- `ExamSessionRequest`
- `RecordExamAttemptRequest`
- `CreateExamRetakeRequest`
- `StoreExamSessionRequest`
- `UpdateExamSessionRequest`
- `ChangeExamSessionStatusRequest`
- `AddStudentToExamSessionRequest`
- `CheckExamAdmissionRequest`
- `CreateExamAttemptRequest`
- `CompleteExamAttemptRequest`
- `RecordExamResultRequest`

Requests authorize through platform exam permissions and return translated validation errors.

## Rules

- `ValidExamTypeRule`
- `ExamAdmissionReadyRule`
- `ExamSessionCanAcceptAttemptRule`
- `ExamAttemptCanBeRetakenRule`
- `ActiveExamTypeRule`
- `ActiveExamStatusRule`
- `ValidExamSessionStatusTransitionRule`
- `ExamSessionCapacityRule`
- `StudentCanJoinExamSessionRule`
- `EnrollmentCanTakeExamRule`
- `RequiredDocumentsAcceptedRule`
- `RequiredPaymentsCompletedRule`
- `RequiredTheoryHoursRule`
- `RequiredPracticeHoursRule`
- `InternalExamPassedRule`
- `EnrollmentActiveForExamRule`
- `StudentActiveForExamRule`
- `ExamAttemptCanStartRule`
- `ExamAttemptCanCompleteRule`
- `ExamResultScoreRule`
- `ExamRetakeAllowedRule`

Rule failures use exam translation keys under `exams.validation.*`.

Admission checks return `allowed`, `blocking_errors`, `warnings`, `checklist`, and `admission`. Session participant admission uses the same result so blocked students carry the first blocking translation key as the participant block reason.

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

The exam menu opens a full Block 10 workspace for local school staff:

- Sessions: list, filter, create, edit, cancel, add students, check admissions, and export session rows.
- Internal exams: filtered view of internal theory and practical sessions.
- Official placeholders: filtered view of manually tracked official theory and practical placeholder sessions.
- Admissions: readiness list with documents, payments, theory hours, practice hours, internal-exam checks, and manual approve or block actions.
- Attempts: attempt list and edit page for start, completion, no-show, cancellation, result entry, pass/fail decisions, and retake creation.
- Results: result list with filters by exam type, student, group, and pass/fail state.
- Retakes: planned retake list linked back to previous and new attempts.
- Settings: read-only dictionary lists for exam types, session statuses, attempt statuses, and result statuses.

The screens prepare data in `query()` with eager-loaded relationships, use Orchid tables and modal actions, and delegate write operations to Actions. They do not implement a government registry integration, official API sync, online testing engine, AI scoring, payment provider, tenant layer, subscription layer, or reseller/platform-owner workflow.

Current routes:

- `platform.exams`
- `platform.exams.sessions`
- `platform.exams.sessions.create`
- `platform.exams.sessions.edit`
- `platform.exams.admissions`
- `platform.exams.attempts`
- `platform.exams.attempts.edit`
- `platform.exams.results`
- `platform.exams.retakes`
- `platform.exams.types`
- `platform.exams.statuses`
- `platform.exams.attempt-statuses`
- `platform.exams.result-statuses`

Session, admission, attempt, result, retake, dictionary, and export screens use granular local exam permissions. Broad compatibility permissions remain registered for older code paths, but new screen access is controlled by the specific Block 10 permissions.

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

`ExamActionsValidationTest` verifies:

- Exam number generation, session lifecycle, admission checks, participant changes, attempt lifecycle, result decisions, retakes, and activity logging through Actions.
- Custom rules for active dictionaries, status transitions, capacity, enrollment readiness, documents, payments, hours, internal exams, attempts, scores, and retakes.
- Required exam FormRequests authorize through local exam permissions and map built-in validation failures to `exams.validation.*` translation keys.

`ExamAdmissionChecklistTest` verifies:

- Passing admissions with stored checklist rows.
- Missing documents, incomplete payments, theory-hour gaps, and practice-hour gaps as blocking errors.
- Configured internal theory and internal practical prerequisites.
- Manual approval converting automatic failures into warnings.
- Manual blocks and session participant rechecks.

`ExamFactoriesSeedersTest` verifies:

- Required factory states for exam types, statuses, sessions, attempts, results, retakes, checklist items, and activities.
- Idempotent exam seeders and wrapper seeding.
- Default internal and official placeholder exam dictionaries.
- Seeded dictionary and interface translations for Russian, English, Lithuanian, and Polish.

`ExamLocalizationPermissionsTest` verifies:

- Requested exam menu, screen, field, action, status, validation, and permission labels are translated for all active locales.
- Granular exam permissions are registered in the local superadmin permission list and the Orchid permission provider.
- Existing broad exam permissions remain registered for compatibility with current requests and screens.

`ExamOrchidScreensTest` verifies:

- Main exam session, admission, attempt, result, retake, and dictionary screens render with matching permissions.
- Session edit and attempt edit screens can open records with the required related data loaded.
- Granular permissions block unrelated exam screens when a user only has access to one exam area.

## TODOs

- Retake action from the student and exam views.
- Exam analytics widgets.
- Optional manual state exam reference management.
