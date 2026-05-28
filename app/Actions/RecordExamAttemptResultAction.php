<?php

namespace App\Actions;

use App\Enums\ExamAdmissionStatus;
use App\Enums\ExamAttemptStatus;
use App\Enums\ExamType;
use App\Models\ExamAdmission;
use App\Models\ExamAttempt;
use App\Models\ExamSession;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RecordExamAttemptResultAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(ExamAdmission $admission, ?ExamSession $session, array $data, ?User $user = null): ExamAttempt
    {
        return DB::transaction(function () use ($admission, $session, $data, $user): ExamAttempt {
            $admission = $admission->refresh(['checklistItems']);

            if (! (bool) ($data['allow_unready_admission'] ?? false) && ! $admission->isReady()) {
                throw ValidationException::withMessages([
                    'exam_admission_id' => tkey('exams.validation.admission_not_ready'),
                ]);
            }

            if ($session !== null && ! (bool) ($data['allow_full_session'] ?? false) && ! $session->acceptsAttempt()) {
                throw ValidationException::withMessages([
                    'exam_session_id' => tkey('exams.validation.session_full_or_closed'),
                ]);
            }

            $type = $this->examType($data['exam_type'] ?? $admission->admission_type);
            $status = $this->attemptStatus($data['status'] ?? null, (bool) ($data['passed'] ?? false));
            $passed = $status === ExamAttemptStatus::Passed || (bool) ($data['passed'] ?? false);
            $attemptNumber = $data['attempt_number'] ?? $this->nextAttemptNumber($admission, $type);

            $attempt = ExamAttempt::query()->create([
                'exam_admission_id' => $admission->id,
                'exam_session_id' => $session?->id,
                'enrollment_id' => $admission->enrollment_id,
                'student_profile_id' => $admission->student_profile_id,
                'training_group_id' => $admission->training_group_id,
                'training_program_id' => $admission->training_program_id,
                'instructor_id' => $data['instructor_id'] ?? $session?->instructor_id ?? $admission->instructor_id,
                'driving_lesson_id' => $data['driving_lesson_id'] ?? null,
                'student_document_id' => $data['student_document_id'] ?? null,
                'payment_id' => $data['payment_id'] ?? null,
                'retake_of_attempt_id' => $data['retake_of_attempt_id'] ?? null,
                'exam_type' => $type,
                'provider' => $data['provider'] ?? $type->provider(),
                'status' => $status,
                'attempt_number' => $attemptNumber,
                'score' => $data['score'] ?? null,
                'max_score' => $data['max_score'] ?? null,
                'passed' => $passed,
                'result_payload' => $data['result_payload'] ?? null,
                'started_at' => $data['started_at'] ?? null,
                'finished_at' => $data['finished_at'] ?? now(),
                'next_eligible_at' => $data['next_eligible_at'] ?? null,
                'official_reference' => $data['official_reference'] ?? null,
                'official_payload' => $data['official_payload'] ?? null,
                'notes' => $data['notes'] ?? null,
                'internal_notes' => $data['internal_notes'] ?? null,
                'created_by_id' => $user?->id,
                'updated_by_id' => $user?->id,
            ]);

            if ($session !== null) {
                $session->forceFill([
                    'seats_taken' => min($session->capacity, $session->seats_taken + 1),
                ])->save();
            }

            $admission->forceFill([
                'status' => $passed ? ExamAdmissionStatus::Passed : ExamAdmissionStatus::RetakeRequired,
                'updated_by_id' => $user?->id ?? $admission->updated_by_id,
            ])->save();

            app(RecordExamActivityAction::class)->handle(
                $admission,
                $session,
                $attempt,
                $user,
                'attempt_recorded',
                tkey('exams.activities.titles.attempt_recorded'),
                null,
                null,
                $status->value,
                ['passed' => $passed],
            );

            return $attempt->refresh();
        });
    }

    private function examType(ExamType|string $type): ExamType
    {
        return $type instanceof ExamType ? $type : ExamType::from((string) $type);
    }

    private function attemptStatus(mixed $status, bool $passed): ExamAttemptStatus
    {
        if (filled($status)) {
            return $status instanceof ExamAttemptStatus ? $status : ExamAttemptStatus::from((string) $status);
        }

        return $passed ? ExamAttemptStatus::Passed : ExamAttemptStatus::Failed;
    }

    private function nextAttemptNumber(ExamAdmission $admission, ExamType $type): int
    {
        return ((int) ExamAttempt::query()
            ->where('enrollment_id', $admission->enrollment_id)
            ->where('exam_type', $type->value)
            ->max('attempt_number')) + 1;
    }
}
