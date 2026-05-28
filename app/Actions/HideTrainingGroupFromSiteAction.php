<?php

namespace App\Actions;

use App\Models\TrainingGroup;
use App\Models\User;

class HideTrainingGroupFromSiteAction
{
    public function handle(TrainingGroup $group, ?User $user = null): TrainingGroup
    {
        $group->forceFill([
            'is_visible_on_site' => false,
            'is_accepting_applications' => false,
            'updated_by_id' => $user?->id ?? $group->updated_by_id,
        ])->save();

        app(RecordTrainingGroupActivityAction::class)->handle($group->refresh(), $user, 'hidden_from_site', tkey('education.activities.titles.hidden_from_site'));

        return $group->refresh();
    }
}
