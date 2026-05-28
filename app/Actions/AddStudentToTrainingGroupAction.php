<?php

namespace App\Actions;

use App\Enums\GroupStatus;
use App\Models\StudentEnrollment;
use App\Models\TrainingGroup;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class AddStudentToTrainingGroupAction
{
    public function handle(StudentEnrollment $enrollment, TrainingGroup|int $group, ?User $user = null, bool $allowOverbooking = false): StudentEnrollment
    {
        $group = $group instanceof TrainingGroup
            ? $group
            : TrainingGroup::query()->findOrFail($group);

        if (! $allowOverbooking && (! $this->groupAcceptsEnrollment($group) || $group->is_full)) {
            throw ValidationException::withMessages([
                'training_group_id' => tkey('students.validation.enrollment_cannot_join_group'),
            ]);
        }

        if (
            $enrollment->training_program_id !== null
            && $group->training_program_id !== null
            && (int) $enrollment->training_program_id !== (int) $group->training_program_id
        ) {
            throw ValidationException::withMessages([
                'training_group_id' => tkey('students.validation.enrollment_cannot_join_group'),
            ]);
        }

        $oldGroupId = filled($enrollment->training_group_id)
            ? (int) $enrollment->training_group_id
            : null;

        if ($oldGroupId === (int) $group->id) {
            return $this->syncEnrollmentGroupFields($enrollment, $group, $user)->refresh();
        }

        if ($oldGroupId !== null) {
            $oldGroup = TrainingGroup::query()
                ->select(['id', 'places_taken'])
                ->whereKey($oldGroupId)
                ->first();

            if ($oldGroup !== null) {
                $oldGroup->forceFill([
                    'places_taken' => max(0, ((int) $oldGroup->places_taken) - 1),
                ])->save();
            }
        }

        $group->forceFill([
            'places_taken' => ((int) $group->places_taken) + 1,
        ])->save();

        $type = $oldGroupId === null ? 'group_assigned' : 'group_changed';
        $enrollment = $this->syncEnrollmentGroupFields($enrollment, $group, $user)->refresh();

        app(RecordStudentActivityAction::class)->handle(
            $enrollment->student,
            $user,
            $type,
            tkey('students.activities.titles.'.$type),
            null,
            $oldGroupId !== null ? (string) $oldGroupId : null,
            (string) $group->id,
            ['enrollment_id' => $enrollment->id],
            $enrollment,
        );

        return $enrollment->refresh();
    }

    private function syncEnrollmentGroupFields(StudentEnrollment $enrollment, TrainingGroup $group, ?User $user): StudentEnrollment
    {
        $enrollment->forceFill([
            'training_group_id' => $group->id,
            'training_program_id' => $enrollment->training_program_id ?: $group->training_program_id,
            'course_category_id' => $enrollment->course_category_id ?: $group->course_category_id,
            'branch_id' => $enrollment->branch_id ?: $group->branch_id,
            'instructor_id' => $enrollment->instructor_id ?: $group->instructor_id,
            'updated_by_id' => $user?->id ?? $enrollment->updated_by_id,
        ])->save();

        return $enrollment;
    }

    private function groupAcceptsEnrollment(TrainingGroup $group): bool
    {
        return in_array($group->status, [
            GroupStatus::Planned,
            GroupStatus::Recruiting,
            GroupStatus::Open,
            GroupStatus::AlmostFull,
            GroupStatus::Active,
        ], true);
    }
}
