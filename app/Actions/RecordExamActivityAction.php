<?php

namespace App\Actions;

use App\Models\ExamActivity;
use App\Models\ExamAdmission;
use App\Models\ExamAttempt;
use App\Models\ExamSession;
use App\Models\User;

class RecordExamActivityAction
{
    /**
     * @param  array<string, mixed>|null  $meta
     */
    public function handle(
        ?ExamAdmission $admission,
        ?ExamSession $session,
        ?ExamAttempt $attempt,
        ?User $user,
        string $type,
        ?string $title = null,
        ?string $body = null,
        ?string $oldValue = null,
        ?string $newValue = null,
        ?array $meta = null,
    ): ExamActivity {
        return ExamActivity::query()->create([
            'exam_admission_id' => $admission?->id ?? $attempt?->exam_admission_id,
            'exam_session_id' => $session?->id ?? $attempt?->exam_session_id,
            'exam_attempt_id' => $attempt?->id,
            'enrollment_id' => $attempt?->enrollment_id ?? $admission?->enrollment_id,
            'student_profile_id' => $attempt?->student_profile_id ?? $admission?->student_profile_id,
            'training_group_id' => $attempt?->training_group_id ?? $admission?->training_group_id ?? $session?->training_group_id,
            'user_id' => $user?->id,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'meta' => $meta,
        ]);
    }
}
