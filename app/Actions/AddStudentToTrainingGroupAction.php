<?php

namespace App\Actions;

use App\Enums\GroupStatus;
use App\Models\StudentEnrollment;
use App\Models\TrainingGroup;
use App\Models\TrainingGroupMembership;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AddStudentToTrainingGroupAction
{
    public function handle(StudentEnrollment $enrollment, TrainingGroup|int $group, ?User $user = null, bool $allowOverbooking = false): TrainingGroupMembership
    {
        return DB::transaction(function () use ($enrollment, $group, $user, $allowOverbooking): TrainingGroupMembership {
            $group = $group instanceof TrainingGroup
                ? $group
                : TrainingGroup::query()->findOrFail($group);

            if (! $allowOverbooking && (! $this->groupAcceptsEnrollment($group) || $group->is_full)) {
                throw ValidationException::withMessages([
                    'training_group_id' => tkey('education.groups.validation.enrollment_cannot_join_group'),
                ]);
            }

            if (
                $enrollment->training_program_id !== null
                && $group->training_program_id !== null
                && (int) $enrollment->training_program_id !== (int) $group->training_program_id
            ) {
                throw ValidationException::withMessages([
                    'training_group_id' => tkey('education.groups.validation.enrollment_cannot_join_group'),
                ]);
            }

            $oldGroupId = filled($enrollment->training_group_id)
                ? (int) $enrollment->training_group_id
                : null;

            if ($oldGroupId === (int) $group->id) {
                $membership = $this->syncMembership($enrollment, $group, $user);

                $this->syncEnrollmentGroupFields($enrollment, $group, $user);

                return $membership->refresh();
            }

            if ($oldGroupId !== null) {
                $oldGroup = TrainingGroup::query()
                    ->select(['id', 'places_taken', 'capacity_taken', 'capacity_waitlist', 'capacity_total', 'capacity'])
                    ->whereKey($oldGroupId)
                    ->first();

                $oldMembership = TrainingGroupMembership::query()
                    ->active()
                    ->where('enrollment_id', $enrollment->id)
                    ->where('training_group_id', $oldGroupId)
                    ->first();

                if ($oldMembership !== null) {
                    $oldMembership->forceFill([
                        'status' => 'transferred',
                        'left_at' => now(),
                        'transfer_to_group_id' => $group->id,
                        'transfer_reason' => 'group_changed',
                        'left_reason' => 'group_changed',
                        'updated_by_id' => $user?->id ?? $oldMembership->updated_by_id,
                    ])->save();
                }

                if ($oldGroup !== null) {
                    app(RecalculateTrainingGroupCapacityAction::class)->handle($oldGroup, $user);
                }
            }

            $membership = $this->syncMembership($enrollment, $group, $user);
            app(RecalculateTrainingGroupCapacityAction::class)->handle($group, $user);

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

            app(RecordTrainingGroupActivityAction::class)->handle(
                $group->refresh(),
                $user,
                'student_added',
                tkey('education.activities.titles.student_added'),
                null,
                $oldGroupId !== null ? (string) $oldGroupId : null,
                (string) $enrollment->student_profile_id,
                ['enrollment_id' => $enrollment->id],
                $enrollment,
                $membership,
            );

            return $membership->refresh();
        });
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

    private function syncMembership(StudentEnrollment $enrollment, TrainingGroup $group, ?User $user): TrainingGroupMembership
    {
        return TrainingGroupMembership::query()->updateOrCreate(
            [
                'training_group_id' => $group->id,
                'enrollment_id' => $enrollment->id,
            ],
            [
                'student_id' => $enrollment->student_profile_id,
                'student_profile_id' => $enrollment->student_profile_id,
                'student_enrollment_id' => $enrollment->id,
                'status' => 'active',
                'joined_at' => now(),
                'left_at' => null,
                'left_reason' => null,
                'created_by_id' => $user?->id,
                'updated_by_id' => $user?->id,
            ],
        );
    }

    private function groupAcceptsEnrollment(TrainingGroup $group): bool
    {
        return $group->acceptsEnrollment()
            || in_array($group->status, [
                GroupStatus::Planned,
                GroupStatus::Recruiting,
                GroupStatus::Open,
                GroupStatus::AlmostFull,
                GroupStatus::Active,
            ], true);
    }
}
