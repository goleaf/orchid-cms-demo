<?php

namespace App\Actions;

use App\Models\TrainingGroup;

class SaveTrainingGroupAction
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(TrainingGroup $group, array $attributes): TrainingGroup
    {
        $group->fill($attributes);
        $group->save();

        return $group;
    }
}
