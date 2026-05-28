<?php

namespace App\Actions;

use App\Models\TrainingGroup;

class ShowTrainingGroupOnSiteAction
{
    public function handle(TrainingGroup $group): TrainingGroup
    {
        return app(PublishTrainingGroupOnSiteAction::class)->handle($group);
    }
}
