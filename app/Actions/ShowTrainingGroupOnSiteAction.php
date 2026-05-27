<?php

namespace App\Actions;

use App\Models\TrainingGroup;

class ShowTrainingGroupOnSiteAction
{
    public function handle(TrainingGroup $group): TrainingGroup
    {
        $group->forceFill(['is_visible_on_site' => true])->save();

        return $group->refresh();
    }
}
