<?php

namespace App\Actions;

use App\Enums\TrainingGroupMembershipStatus;
use App\Models\StudentEnrollment;
use App\Models\TrainingGroup;
use App\Models\TrainingGroupMembership;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WaitlistStudentForTrainingGroupAction
{
    public function handle(StudentEnrollment $enrollment, TrainingGroup|int $group, ?User $user = null, ?string $notes = null): TrainingGroupMembership
    {
        return DB::transaction(function () use ($enrollment, $group, $user, $notes): TrainingGroupMembership {
            $group = $group instanceof TrainingGroup ? $group : TrainingGroup::query()->findOrFail($group);

            if (! $group->is_full) {
                throw ValidationException::withMessages([
                    'training_group_id' => tkey('education.groups.validation.capacity_exceeded'),
                ]);
            }

            $membership = TrainingGroupMembership::query()->updateOrCreate(
                [
                    'training_group_id' => $group->id,
                    'enrollment_id' => $enrollment->id,
                ],
                [
                    'student_id' => $enrollment->student_profile_id,
                    'student_profile_id' => $enrollment->student_profile_id,
                    'student_enrollment_id' => $enrollment->id,
                    'status' => TrainingGroupMembershipStatus::Waitlisted->value,
                    'joined_at' => now(),
                    'left_at' => null,
                    'notes' => $notes,
                    'created_by_id' => $user?->id,
                    'updated_by_id' => $user?->id,
                ],
            );

            app(RecalculateTrainingGroupCapacityAction::class)->handle($group, $user, false);
            app(RecordTrainingGroupActivityAction::class)->handle(
                $group->refresh(),
                $user,
                'student_waitlisted',
                tkey('education.activities.titles.student_waitlisted'),
                null,
                null,
                (string) $enrollment->student_profile_id,
                ['enrollment_id' => $enrollment->id],
                $enrollment,
                $membership,
            );

            return $membership->refresh();
        });
    }
}
