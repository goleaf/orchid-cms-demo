# Exam Admissions

Exam admissions decide whether a student enrollment can join an exam session or create an exam attempt. The checks are local to the exam module and do not change global student, enrollment, document, payment, lesson, or finance behavior.

This product is for one local driving school. Admissions do not add tenants, subscriptions, platform owner workflows, official registry sync, government APIs, online theory testing, AI scoring, or payment providers.

## Admission Result

Admission checks return a structured result:

- `allowed`: whether the enrollment can proceed.
- `blocking_errors`: translation keys that block the admission.
- `warnings`: translation keys that were overridden by manual approval.
- `checklist`: stored checklist rows with key, required flag, passed flag, status, message key, check time, and checker.
- `admission`: the saved exam admission record.

All blocking messages use `exams.validation.*` keys. The checks store keys such as `exams.validation.documents_required` instead of hardcoded Russian or English text.

## Checklist Items

The admission checklist always uses these keys:

- `documents`: required accepted documents.
- `payments`: no unpaid blocking balance or incomplete required payment state.
- `theory_hours`: completed theory hours against the configured requirement.
- `practice_hours`: completed practice hours against the configured requirement.
- `internal_theory`: internal theory exam passed before a practical or official theory exam when configured.
- `internal_practical`: internal practical exam passed before an official practical exam when configured.
- `enrollment_status`: enrollment is active and not cancelled, expelled, archived, or deleted.
- `student_status`: student is active and not blocked, archived, inactive, or deleted.
- `manual_review`: manual approval or manual block by staff.

Each item records whether it is required, whether it passed, its status, a message key, the check timestamp, and the user who performed the check when available.

## Automatic Checks

Documents pass only when the required enrollment or student-level documents are verified. The default required documents are identity card, medical certificate, and training contract.

Payments pass when the enrollment has no blocking debt. A paid, completed, or settled enrollment payment state passes. A zero-price enrollment passes. Otherwise the paid payment total must cover the contracted price.

Theory and practice hours use the completed hour totals stored on the enrollment and the active exam admission rule for the exam type, course, and category. Practical exams use the configured practice-hour requirement.

Internal exam prerequisites are only exam admission checks. They do not globally prevent editing lessons, documents, payments, or enrollments. When configured, practical exams require a passed internal theory attempt, and official practical placeholders require a passed internal practical attempt.

## Manual Review

Manual approval creates a passed `manual_review` item. If automatic checks still fail, the admission result is allowed and the automatic failures move to warnings.

Manual block creates a failed `manual_review` item and blocks the admission until staff approve it again. The block reason is stored as a translation key when possible.

## Session Integration

Adding a student to an exam session runs the admission check for that session type. Passing admissions create admitted participants. Failed admissions create blocked participants and store the first blocking translation key as the block reason.

Session rechecks rebuild the checklist for every participant, update participant admitted or blocked state, and mirror admission checklist rows into the session checklist for staff review.

## Verification

Focused coverage exists for passing admissions, missing documents, payment debt, insufficient theory hours, insufficient practice hours, internal prerequisite exams, manual approval, manual block, and session participant rechecks.
