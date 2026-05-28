<?php

namespace App\Actions;

use App\Models\TrainingGroup;
use App\Models\TrainingGroupMembership;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TransferStudentBetweenGroupsAction
{
    public function handle(TrainingGroupMembership $membership, TrainingGroup|int $targetGroup, ?User $user = null, bool $allowOverbooking = false, ?string $reason = null): TrainingGroupMembership
    {
        return DB::transaction(function () use ($membership, $targetGroup, $user, $allowOverbooking, $reason): TrainingGroupMembership {
            if (! $membership->is_active || $membership->enrollment === null) {
                throw ValidationException::withMessages([
                    'membership_id' => tkey('education.groups.validation.membership_cannot_be_transferred'),
                ]);
            }

            $sourceGroup = $membership->group()->firstOrFail();
            $targetGroup = $targetGroup instanceof TrainingGroup ? $targetGroup : TrainingGroup::query()->findOrFail($targetGroup);

            if (! $allowOverbooking && (! $targetGroup->acceptsEnrollment() || $targetGroup->is_full)) {
                throw ValidationException::withMessages([
                    'target_group_id' => tkey('education.groups.validation.capacity_exceeded'),
                ]);
            }

            $membership->forceFill([
                'status' => 'transferred',
                'left_at' => now(),
                'transfer_to_group_id' => $targetGroup->id,
                'transfer_reason' => $reason,
                'left_reason' => $reason,
                'updated_by_id' => $user?->id ?? $membership->updated_by_id,
            ])->save();

            $newMembership = TrainingGroupMembership::query()->create([
                'training_group_id' => $targetGroup->id,
                'student_id' => $membership->student_profile_id,
                'student_profile_id' => $membership->student_profile_id,
                'student_enrollment_id' => $membership->enrollment_id,
                'enrollment_id' => $membership->enrollment_id,
                'status' => 'active',
                'joined_at' => now(),
                'transfer_from_group_id' => $sourceGroup->id,
                'transfer_reason' => $reason,
                'created_by_id' => $user?->id,
                'updated_by_id' => $user?->id,
            ]);

            $membership->enrollment->forceFill([
                'training_group_id' => $targetGroup->id,
                'updated_by_id' => $user?->id ?? $membership->enrollment->updated_by_id,
            ])->save();

            app(RecalculateTrainingGroupCapacityAction::class)->handle($sourceGroup, $user);
            app(RecalculateTrainingGroupCapacityAction::class)->handle($targetGroup, $user);

            app(RecordTrainingGroupActivityAction::class)->handle($sourceGroup->refresh(), $user, 'student_transferred_out', tkey('education.activities.titles.student_transferred_out'), null, (string) $membership->student_profile_id, (string) $targetGroup->id, ['reason' => $reason], $membership->enrollment, $membership);
            app(RecordTrainingGroupActivityAction::class)->handle($targetGroup->refresh(), $user, 'student_transferred_in', tkey('education.activities.titles.student_transferred_in'), null, (string) $sourceGroup->id, (string) $membership->student_profile_id, ['reason' => $reason], $membership->enrollment, $newMembership);

            app(RecordStudentActivityAction::class)->handle(
                $membership->enrollment->student,
                $user,
                'group_changed',
                tkey('students.activities.titles.group_changed'),
                null,
                (string) $sourceGroup->id,
                (string) $targetGroup->id,
                ['enrollment_id' => $membership->enrollment_id],
                $membership->enrollment->refresh(),
            );

            return $newMembership->refresh();
        });
    }
}
