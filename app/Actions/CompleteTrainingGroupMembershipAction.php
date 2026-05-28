<?php

namespace App\Actions;

use App\Models\TrainingGroupMembership;
use App\Models\User;

class CompleteTrainingGroupMembershipAction
{
    public function handle(TrainingGroupMembership $membership, ?User $user = null): TrainingGroupMembership
    {
        if (! $membership->is_active) {
            return $membership->refresh();
        }

        $group = $membership->group()->firstOrFail();

        $membership->forceFill([
            'status' => 'completed',
            'left_at' => now(),
            'updated_by_id' => $user?->id ?? $membership->updated_by_id,
        ])->save();

        if ($membership->enrollment?->training_group_id === $group->id) {
            $membership->enrollment->forceFill([
                'training_group_id' => null,
                'updated_by_id' => $user?->id ?? $membership->enrollment->updated_by_id,
            ])->save();
        }

        app(RecalculateTrainingGroupCapacityAction::class)->handle($group, $user);
        app(RecordTrainingGroupActivityAction::class)->handle(
            $group->refresh(),
            $user,
            'membership_completed',
            tkey('education.activities.titles.membership_completed'),
            null,
            null,
            (string) $membership->student_profile_id,
            ['enrollment_id' => $membership->enrollment_id],
            $membership->enrollment,
            $membership,
        );

        return $membership->refresh();
    }
}
