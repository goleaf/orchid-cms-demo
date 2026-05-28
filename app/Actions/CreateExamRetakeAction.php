<?php

namespace App\Actions;

use App\Enums\ExamAdmissionStatus;
use App\Enums\ExamAttemptStatus;
use App\Enums\ExamRetakeStatus;
use App\Models\ExamAttempt;
use App\Models\ExamAttemptStatus as ExamAttemptStatusModel;
use App\Models\ExamSession;
use App\Models\User;
use App\Services\Exams\ExamWorkflowService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateExamRetakeAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(ExamAttempt $attempt, array $data = [], ?User $user = null): ExamAttempt
    {
        return DB::transaction(function () use ($attempt, $data, $user): ExamAttempt {
            if (! $attempt->status->canBeRetaken()) {
                throw ValidationException::withMessages([
                    'attempt' => tkey('exams.validation.attempt_cannot_be_retaken'),
                ]);
            }

            $session = $this->session($data['exam_session_id'] ?? null);
            $nextAttemptNumber = $this->nextAttemptNumber($attempt);

            $retake = ExamAttempt::query()->create([
                'exam_admission_id' => $attempt->exam_admission_id,
                'exam_session_id' => $session?->id,
                'enrollment_id' => $attempt->enrollment_id,
                'student_id' => $attempt->student_id ?? $attempt->student_profile_id,
                'student_profile_id' => $attempt->student_profile_id,
                'training_group_id' => $attempt->training_group_id,
                'training_program_id' => $attempt->training_program_id,
                'instructor_id' => $data['instructor_id'] ?? $session?->instructor_id ?? $attempt->instructor_id,
                'driving_lesson_id' => $data['driving_lesson_id'] ?? null,
                'student_document_id' => $data['student_document_id'] ?? null,
                'payment_id' => $data['payment_id'] ?? null,
                'retake_of_attempt_id' => $attempt->id,
                'exam_type' => $attempt->exam_type,
                'provider' => $attempt->provider,
                'status' => ExamAttemptStatus::Scheduled,
                'status_id' => ExamAttemptStatusModel::query()->where('code', 'planned')->value('id'),
                'attempt_number' => $data['attempt_number'] ?? $nextAttemptNumber,
                'attempt_no' => $data['attempt_no'] ?? $data['attempt_number'] ?? $nextAttemptNumber,
                'next_eligible_at' => $data['next_eligible_at'] ?? now(),
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

            $admission = $attempt->admission;
            if ($admission !== null) {
                $admission->forceFill([
                    'status' => ExamAdmissionStatus::RetakeScheduled,
                    'updated_by_id' => $user?->id ?? $admission->updated_by_id,
                ])->save();
            }

            app(RecordExamActivityAction::class)->handle(
                $admission,
                $session,
                $retake,
                $user,
                'retake_scheduled',
                tkey('exams.activities.titles.retake_scheduled'),
                null,
                (string) $attempt->id,
                (string) $retake->id,
                ['retake_of_attempt_id' => $attempt->id],
            );

            app(ExamWorkflowService::class)->createRetakeRecord($attempt, [
                'new_attempt_id' => $retake->id,
                'planned_at' => $data['planned_at'] ?? $data['next_eligible_at'] ?? now(),
                'reason' => $data['reason'] ?? $data['notes'] ?? null,
                'status' => ExamRetakeStatus::Scheduled->value,
            ], $user);

            return $retake->refresh();
        });
    }

    private function session(mixed $sessionId): ?ExamSession
    {
        return filled($sessionId)
            ? ExamSession::query()->findOrFail($sessionId)
            : null;
    }

    private function nextAttemptNumber(ExamAttempt $attempt): int
    {
        return ((int) ExamAttempt::query()
            ->where('enrollment_id', $attempt->enrollment_id)
            ->where('exam_type', $attempt->exam_type->value)
            ->max('attempt_number')) + 1;
    }
}
