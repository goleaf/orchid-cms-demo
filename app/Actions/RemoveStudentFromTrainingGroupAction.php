<?php

namespace App\Actions;

use App\Models\TrainingGroup;
use App\Models\TrainingGroupMembership;
use App\Models\User;

class RemoveStudentFromTrainingGroupAction
{
    public function handle(TrainingGroupMembership|int|string $membership, ?User $user = null, ?string $reason = null): TrainingGroupMembership
    {
        $model = $membership instanceof TrainingGroupMembership
            ? $membership
            : TrainingGroupMembership::query()->findOrFail($membership);

        if (! $model->is_active) {
            return $model->refresh();
        }

        $group = $model->group()->firstOrFail();

        $model->forceFill([
            'status' => 'left',
            'left_at' => now(),
            'left_reason' => $reason,
            'updated_by_id' => $user?->id ?? $model->updated_by_id,
        ])->save();

        if ($model->enrollment !== null) {
            $model->enrollment->forceFill([
                'training_group_id' => null,
                'updated_by_id' => $user?->id ?? $model->enrollment->updated_by_id,
            ])->save();
        }

        $this->decrementCapacity($group);

        app(RecordTrainingGroupActivityAction::class)->handle(
            $group->refresh(),
            $user,
            'student_removed',
            tkey('education.activities.titles.student_removed'),
            null,
            (string) $model->student_profile_id,
            null,
            ['reason' => $reason],
            $model->enrollment,
            $model,
        );

        return $model->refresh();
    }

    private function decrementCapacity(TrainingGroup $group): void
    {
        $group->forceFill([
            'places_taken' => max(0, ((int) $group->places_taken) - 1),
        ])->save();
    }
}
