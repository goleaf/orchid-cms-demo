# Exams, Admissions, and Results

Project baseline: follow [`docs/project-specs.md`](project-specs.md) and [`AGENTS.md`](../AGENTS.md). Exam work is Laravel + Orchid + Blade, uses Eloquent only, and keeps all visible admin/public text translatable.

Block 10 adds the exam foundation for one local driving school. It does not add tenants, subscription billing, reseller behavior, platform-owner dashboards, official registry integration, state API sync, an online theory question bank, a full LMS testing engine, AI scoring, or a payment provider.

## Purpose

The exam module connects student readiness, documents, payments, lessons, groups, and exam outcomes. It gives the school a structured place to track internal theory exams, internal practical exams, state exam placeholders, admissions, sessions, attempts, results, retakes, and activity history.

## Storage

- `exam_admissions`: readiness record for an enrollment and exam type.
- `exam_admission_checklist_items`: readiness checklist items for documents, payment, and training hours.
- `exam_sessions`: planned or completed internal sessions and official/state placeholders.
- `exam_attempts`: scheduled attempts, results, failures, passes, no-shows, cancellations, and retakes.
- `exam_activities`: activity timeline for admissions, sessions, attempts, and retakes.

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
- `state_theory`
- `state_practical`

State exams are placeholders only. They can store references and payloads for manual tracking, but there is no government registry integration or automatic official sync in this block.

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

- `ExamAdmissionFactory`
- `ExamAdmissionChecklistItemFactory`
- `ExamSessionFactory`
- `ExamAttemptFactory`
- `ExamActivityFactory`

Seeders:

- `ExamTranslationSeeder`
- `ExamDemoSeeder`
- `ExamSeeder`

Demo records are created through factories and reuse existing student, enrollment, group, payment, and document records when available.

## Permissions

- `platform.exams`
- `exams.view`
- `exams.manage_admissions`
- `exams.manage_sessions`
- `exams.record_results`
- `exams.schedule_retakes`
- `exams.view_activities`

Superadmin receives these permissions through the shared permission list.

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

## TODOs

- Full admission edit screen.
- Session edit screen.
- Attempt/result entry screen.
- Retake action from the student and exam views.
- Exam analytics widgets.
- Optional manual state exam reference management.
