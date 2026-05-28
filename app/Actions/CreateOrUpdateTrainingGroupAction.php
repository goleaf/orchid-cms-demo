<?php

namespace App\Actions;

use App\Models\TrainingGroup;
use App\Models\TrainingGroupStatus;
use App\Models\User;

class CreateOrUpdateTrainingGroupAction
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(TrainingGroup $group, array $attributes, ?User $user = null): TrainingGroup
    {
        if (array_key_exists('status', $attributes) && ! array_key_exists('status_id', $attributes)) {
            $attributes['status_id'] = TrainingGroupStatus::query()
                ->where('code', $attributes['status'])
                ->value('id');
        }

        if (! $group->exists) {
            $attributes['created_by_id'] = $attributes['created_by_id'] ?? $user?->id;
        }

        $attributes['updated_by_id'] = $attributes['updated_by_id'] ?? $user?->id;

        $beforeStatus = $group->exists ? (string) $group->status->value : null;

        $group->fill($attributes);
        $group->save();

        if ($beforeStatus !== null && array_key_exists('status', $attributes) && $beforeStatus !== (string) $group->status->value) {
            app(RecordTrainingGroupActivityAction::class)->handle(
                $group->refresh(),
                $user,
                'status_changed',
                tkey('education.activities.titles.status_changed'),
                null,
                $beforeStatus,
                (string) $group->status->value,
            );
        }

        return $group->refresh();
    }
}
