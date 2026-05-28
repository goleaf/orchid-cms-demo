<?php

namespace App\Actions;

use App\Models\TrainingGroup;
use App\Models\User;

class SaveTrainingGroupAction
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(TrainingGroup $group, array $attributes, ?User $user = null): TrainingGroup
    {
        return app(CreateOrUpdateTrainingGroupAction::class)->handle($group, $attributes, $user);
    }
}
