<?php

namespace App\Actions;

use App\Models\LearningProgramModule;
use App\Models\User;

class CreateOrUpdateLearningProgramModuleAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(LearningProgramModule $module, array $data, ?User $user = null): LearningProgramModule
    {
        if (! $module->exists) {
            $data['created_by_id'] = $data['created_by_id'] ?? $user?->id;
        }

        $data['updated_by_id'] = $data['updated_by_id'] ?? $user?->id;

        $module->fill($data);
        $module->save();

        return $module->refresh();
    }
}
