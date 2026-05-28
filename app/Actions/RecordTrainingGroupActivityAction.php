<?php

namespace App\Actions;

use App\Models\StudentEnrollment;
use App\Models\TrainingGroup;
use App\Models\TrainingGroupActivity;
use App\Models\TrainingGroupMembership;
use App\Models\User;

class RecordTrainingGroupActivityAction
{
    /**
     * @param  array<string, mixed>|null  $meta
     */
    public function handle(
        TrainingGroup $group,
        ?User $user,
        string $type,
        ?string $title = null,
        ?string $body = null,
        ?string $oldValue = null,
        ?string $newValue = null,
        ?array $meta = null,
        ?StudentEnrollment $enrollment = null,
        ?TrainingGroupMembership $membership = null,
    ): TrainingGroupActivity {
        return TrainingGroupActivity::query()->create([
            'training_group_id' => $group->id,
            'student_id' => $enrollment?->student_profile_id ?? $membership?->student_profile_id,
            'student_enrollment_id' => $enrollment?->id ?? $membership?->enrollment_id,
            'enrollment_id' => $enrollment?->id,
            'membership_id' => $membership?->id,
            'student_profile_id' => $enrollment?->student_profile_id ?? $membership?->student_profile_id,
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
