# Payments and Finance Foundation

Project baseline: follow [`docs/project-specs.md`](project-specs.md) and [`AGENTS.md`](../AGENTS.md). Payment work is Laravel + Orchid + Blade, uses Eloquent only, and keeps all visible admin/public text translatable.

This module is for one local driving school. It does not add tenants, subscription billing, reseller logic, platform-owner dashboards, a payment provider, or multi-company isolation.

## Purpose

The payment foundation records local school payments for students and enrollments. It supports basic finance visibility, readiness checks, and dashboard totals.

Current committed behavior is a foundation:

- payment records linked to students and optional enrollments,
- payment status, method, amount, currency, paid date, reference, and notes,
- a payment list in Orchid,
- dashboard paid revenue totals,
- payment placeholders prepared during student onboarding,
- links from exam readiness checklist items and exam attempts.

Invoices, refunds workflow, external payment provider callbacks, cash register exports, and installment plans are future work.

## Storage

Payment storage uses:

- `payments`: local payment records.
- `student_profiles`: payer/student context.
- `enrollments`: optional program/enrollment context.
- `student_profiles.payment_summary`: onboarding placeholder summary.
- `exam_admission_checklist_items`: can reference a payment for readiness checks.
- `exam_attempts`: can reference payment context when exam fees are involved.

Payment statuses:

- `pending`
- `paid`
- `failed`
- `refunded`

## Admin Screen

Current Orchid route:

- `platform.finance.payments`

Current permission:

- `platform.finance.payments`

The payment list prepares paginated records with student and enrollment program context before rendering. Amount display is calculated on the model, and visible labels are translated.

## Data Flow

Lead-to-student conversion can prepare a payment placeholder so staff know that finance follow-up is required. The placeholder is not a real invoice or transaction.

Exam readiness can use payment-linked checklist items to show whether required fees are paid, waived, missing, or blocking readiness.

Dashboard reporting counts paid revenue from paid payment records.

## Query Notes

The current payment list uses:

- explicit list payload selection,
- eager loaded student and enrollment program relationships,
- pagination,
- translated method and status labels.

Do not calculate payment aggregates inside loops. Shared totals should be cached or precomputed at the Action/model layer.

## Tests

Relevant existing verification:

- `StudentOnboardingWorkflowTest`
- `StudentCrmIntegrationTest`
- `ExamBlockFoundationTest`
- `DrivingSchoolPlatformTest`

Future finance CRUD should add focused tests for permissions, validation, status transitions, dashboard totals, exam readiness links, and translated labels.

## TODOs

- Add create/edit payment screens and Form Requests.
- Add payment status transition rules.
- Add invoices, installment plans, refunds, and receipts.
- Add export/reporting workflows for local accounting.
- Add optional provider integration only after local workflows are stable.
