<?php

namespace App\Actions;

use App\Models\TrainingGroup;

class HideTrainingGroupFromSiteAction
{
    public function handle(TrainingGroup $group): TrainingGroup
    {
        $group->forceFill(['is_visible_on_site' => false])->save();

        return $group->refresh();
    }
}
