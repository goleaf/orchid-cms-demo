# Documents

Project baseline: follow [`docs/project-specs.md`](project-specs.md) and [`AGENTS.md`](../AGENTS.md). Document work is Laravel + Orchid + Blade, uses Eloquent only, and keeps all visible admin/public text translatable.

This module is for one local driving school. It does not add tenants, subscription billing, reseller logic, platform-owner dashboards, or multi-company isolation.

## Purpose

The document foundation tracks student and CRM intake documents needed for enrollment, readiness checks, and exam admission.

Current committed behavior is a foundation:

- student document records with status, document type, title, dates, number, and file path metadata,
- lead document records for CRM intake attachments,
- document placeholders prepared during lead-to-student conversion,
- document links from exam readiness checklist items and attempts,
- an Orchid document list for administrators.

Full upload review, digital signing, document templates, and automated expiry reminders are future work.

## Storage

Document storage uses existing local school records:

- `student_documents`: documents tied to a student profile and optional enrollment.
- `marketing_lead_documents`: documents tied to CRM leads before conversion.
- `student_profiles.documents_summary`: placeholder summary prepared during onboarding.
- `exam_admission_checklist_items`: can reference student documents for readiness checks.
- `exam_attempts`: can reference document context for exam history.

Student documents can have these statuses:

- `missing`
- `submitted`
- `verified`
- `expired`

## Admin Screen

Current Orchid route:

- `platform.documents`

Current permission:

- `platform.documents`

The document list prepares paginated records with student, enrollment, and program context before rendering. Labels come from translation keys and localized document status/type labels.

## Data Flow

Student conversion can prepare document placeholder data through the student onboarding flow. The placeholder is intentionally not a full document module.

Exam readiness can point checklist items to a student document so the exam module can track whether required documents are verified, waived, missing, or blocking readiness.

CRM lead attachments stay under CRM lead document records until a later workflow explicitly maps them to student documents.

## Query Notes

The current document list uses:

- explicit list payload selection,
- eager loaded student and enrollment program relationships,
- pagination,
- translated enum labels.

Do not query document relationships from Blade or table render loops.

## Tests

Relevant existing verification:

- `StudentDatabaseFoundationTest`
- `StudentOnboardingWorkflowTest`
- `StudentCrmIntegrationTest`
- `ExamBlockFoundationTest`

Future document CRUD should add focused tests for permissions, validation, upload metadata, expiry filtering, exam readiness links, and translation keys.

## TODOs

- Add create/edit document screens and Form Requests.
- Add file upload and review workflow.
- Add expiry dashboards and reminder creation.
- Add document template support for contracts and certificates.
- Add safe migration from lead documents to student documents when a lead is converted.
