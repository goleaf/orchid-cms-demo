<?php

namespace App\Actions;

use App\Enums\DocumentStatus;
use App\Enums\ExamAdmissionStatus;
use App\Enums\ExamChecklistItemStatus;
use App\Enums\ExamType;
use App\Models\ExamAdmission;
use App\Models\ExamAdmissionChecklistItem;
use App\Models\Payment;
use App\Models\StudentDocument;
use App\Models\StudentEnrollment;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CreateOrUpdateExamAdmissionAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(StudentEnrollment $enrollment, array $data, ?User $user = null): ExamAdmission
    {
        return DB::transaction(function () use ($enrollment, $data, $user): ExamAdmission {
            $type = $this->examType($data['admission_type'] ?? $data['exam_type'] ?? ExamType::InternalTheory->value);
            $student = $enrollment->student()->firstOrFail();

            $admission = ExamAdmission::query()->updateOrCreate(
                filled($data['id'] ?? null)
                    ? ['id' => (int) $data['id']]
                    : ['enrollment_id' => $enrollment->id, 'admission_type' => $type->value],
                [
                    'student_profile_id' => $student->id,
                    'training_group_id' => $data['training_group_id'] ?? $enrollment->training_group_id,
                    'training_program_id' => $data['training_program_id'] ?? $enrollment->training_program_id,
                    'branch_id' => $data['branch_id'] ?? $enrollment->branch_id,
                    'instructor_id' => $data['instructor_id'] ?? $enrollment->instructor_id,
                    'admission_type' => $type,
                    'status' => $data['status'] ?? ExamAdmissionStatus::Checking,
                    'required_theory_hours' => $data['required_theory_hours'] ?? $enrollment->total_theory_hours,
                    'completed_theory_hours' => $data['completed_theory_hours'] ?? $enrollment->completed_theory_hours,
                    'required_practice_hours' => $data['required_practice_hours'] ?? $enrollment->total_practice_hours,
                    'completed_practice_hours' => $data['completed_practice_hours'] ?? $enrollment->completed_practice_hours,
                    'documents_status' => $data['documents_status'] ?? 'pending',
                    'payment_status' => $data['payment_status'] ?? $enrollment->payment_status ?? 'pending',
                    'checklist_status' => $data['checklist_status'] ?? 'pending',
                    'admitted_at' => $data['admitted_at'] ?? null,
                    'rejected_at' => $data['rejected_at'] ?? null,
                    'expires_at' => $data['expires_at'] ?? null,
                    'notes' => $data['notes'] ?? null,
                    'internal_notes' => $data['internal_notes'] ?? null,
                    'meta' => $data['meta'] ?? null,
                    'created_by_id' => $data['created_by_id'] ?? $user?->id,
                    'updated_by_id' => $user?->id,
                ],
            );

            $items = $data['checklist_items'] ?? $this->defaultChecklist($admission->refresh(), $type);
            $this->syncChecklist($admission, $items, $user);
            $this->syncAdmissionStatus($admission->refresh(), array_key_exists('status', $data), $user);

            app(RecordExamActivityAction::class)->handle(
                $admission->refresh(),
                null,
                null,
                $user,
                'admission_saved',
                tkey('exams.activities.titles.admission_saved'),
                null,
                null,
                $admission->status->value,
                ['admission_type' => $type->value],
            );

            return $admission->refresh(['checklistItems']);
        });
    }

    private function examType(ExamType|string $type): ExamType
    {
        return $type instanceof ExamType ? $type : ExamType::from((string) $type);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function defaultChecklist(ExamAdmission $admission, ExamType $type): array
    {
        $documents = StudentDocument::query()
            ->where('student_profile_id', $admission->student_profile_id)
            ->where(function ($query) use ($admission): void {
                $query->where('enrollment_id', $admission->enrollment_id)
                    ->orWhereNull('enrollment_id');
            })
            ->get()
            ->keyBy('document_type');

        $payments = Payment::query()
            ->where('student_profile_id', $admission->student_profile_id)
            ->where(function ($query) use ($admission): void {
                $query->where('enrollment_id', $admission->enrollment_id)
                    ->orWhereNull('enrollment_id');
            })
            ->get();

        $items = [
            $this->documentItem('identity_document', $documents),
            $this->documentItem('medical_certificate', $documents),
            $this->documentItem('training_contract', $documents),
            $this->paymentItem($payments),
            $this->hoursItem('theory_hours', (float) $admission->completed_theory_hours, (float) $admission->required_theory_hours),
        ];

        if ($type->isPractical()) {
            $items[] = $this->hoursItem('practice_hours', (float) $admission->completed_practice_hours, (float) $admission->required_practice_hours);
        }

        return $items;
    }

    /**
     * @param  Collection<string, StudentDocument>  $documents
     * @return array<string, mixed>
     */
    private function documentItem(string $code, Collection $documents): array
    {
        $documentType = $code === 'training_contract' ? 'training_contract' : str_replace('identity_document', 'id_card', $code);
        $document = $documents->get($documentType);
        $passed = $document?->status === DocumentStatus::Verified;

        return [
            'code' => $code,
            'status' => $passed ? ExamChecklistItemStatus::Passed->value : ExamChecklistItemStatus::Pending->value,
            'student_document_id' => $document?->id,
            'source_type' => $document !== null ? StudentDocument::class : null,
            'source_id' => $document?->id,
        ];
    }

    /**
     * @param  Collection<int, Payment>  $payments
     * @return array<string, mixed>
     */
    private function paymentItem(Collection $payments): array
    {
        $payment = $payments->firstWhere('status.value', 'paid') ?? $payments->first();
        $passed = $payment !== null && (string) $payment->status->value === 'paid';

        return [
            'code' => 'payment_clearance',
            'status' => $passed ? ExamChecklistItemStatus::Passed->value : ExamChecklistItemStatus::Pending->value,
            'payment_id' => $payment?->id,
            'source_type' => $payment !== null ? Payment::class : null,
            'source_id' => $payment?->id,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function hoursItem(string $code, float $completed, float $required): array
    {
        $required = max(0.0, $required);

        return [
            'code' => $code,
            'status' => $required === 0.0 || $completed >= $required
                ? ExamChecklistItemStatus::Passed->value
                : ExamChecklistItemStatus::Pending->value,
            'meta' => [
                'completed' => $completed,
                'required' => $required,
            ],
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    private function syncChecklist(ExamAdmission $admission, array $items, ?User $user): void
    {
        foreach ($items as $item) {
            if (! filled($item['code'] ?? null)) {
                continue;
            }

            ExamAdmissionChecklistItem::query()->updateOrCreate(
                [
                    'exam_admission_id' => $admission->id,
                    'code' => (string) $item['code'],
                ],
                [
                    'title_translations' => $item['title_translations'] ?? null,
                    'status' => $item['status'] ?? ExamChecklistItemStatus::Pending,
                    'source_type' => $item['source_type'] ?? null,
                    'source_id' => $item['source_id'] ?? null,
                    'student_document_id' => $item['student_document_id'] ?? null,
                    'payment_id' => $item['payment_id'] ?? null,
                    'driving_lesson_id' => $item['driving_lesson_id'] ?? null,
                    'checked_at' => $item['checked_at'] ?? null,
                    'checked_by_id' => $item['checked_by_id'] ?? $user?->id,
                    'notes' => $item['notes'] ?? null,
                    'meta' => $item['meta'] ?? null,
                ],
            );
        }
    }

    private function syncAdmissionStatus(ExamAdmission $admission, bool $statusWasExplicit, ?User $user): void
    {
        $failedCount = $admission->checklistItems()
            ->where('status', ExamChecklistItemStatus::Failed->value)
            ->count();
        $openCount = $admission->checklistItems()
            ->whereNotIn('status', [
                ExamChecklistItemStatus::Passed->value,
                ExamChecklistItemStatus::Waived->value,
            ])
            ->count();

        $admission->forceFill([
            'checklist_status' => $failedCount > 0 ? 'failed' : ($openCount > 0 ? 'pending' : 'passed'),
            'status' => $statusWasExplicit
                ? $admission->status
                : ($openCount > 0 ? ExamAdmissionStatus::Checking : ExamAdmissionStatus::Ready),
            'admitted_at' => $openCount === 0 && $admission->admitted_at === null ? now() : $admission->admitted_at,
            'updated_by_id' => $user?->id ?? $admission->updated_by_id,
        ])->save();
    }
}
