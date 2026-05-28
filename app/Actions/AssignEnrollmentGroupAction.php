<?php

namespace App\Actions;

use App\Models\StudentEnrollment;
use App\Models\TrainingGroup;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class AssignEnrollmentGroupAction
{
    public function handle(StudentEnrollment $enrollment, TrainingGroup|int $group, ?User $user = null, bool $allowOverbooking = false): StudentEnrollment
    {
        $group = $group instanceof TrainingGroup
            ? $group
            : TrainingGroup::query()->findOrFail($group);

        if (! $allowOverbooking && $group->is_full) {
            throw ValidationException::withMessages([
                'training_group_id' => tkey('students.validation.enrollment_cannot_join_group'),
            ]);
        }

        $oldGroupId = $enrollment->training_group_id;
        $type = $oldGroupId === null ? 'group_assigned' : 'group_changed';

        $enrollment->forceFill([
            'training_group_id' => $group->id,
            'training_program_id' => $enrollment->training_program_id ?: $group->training_program_id,
            'course_category_id' => $enrollment->course_category_id ?: $group->course_category_id,
            'branch_id' => $enrollment->branch_id ?: $group->branch_id,
            'instructor_id' => $enrollment->instructor_id ?: $group->instructor_id,
            'updated_by_id' => $user?->id ?? $enrollment->updated_by_id,
        ])->save();

        app(RecordStudentActivityAction::class)->handle(
            $enrollment->student,
            $user,
            $type,
            tkey('students.activities.titles.'.$type),
            null,
            filled($oldGroupId) ? (string) $oldGroupId : null,
            (string) $group->id,
            ['enrollment_id' => $enrollment->id],
            $enrollment->refresh(),
        );

        return $enrollment->refresh();
    }
}
