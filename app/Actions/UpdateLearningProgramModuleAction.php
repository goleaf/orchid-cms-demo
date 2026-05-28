<?php

namespace App\Actions;

use App\Models\LearningProgramModule;
use App\Models\User;

class UpdateLearningProgramModuleAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(LearningProgramModule $module, array $data, ?User $user = null): LearningProgramModule
    {
        return app(CreateOrUpdateLearningProgramModuleAction::class)->handle($module, $data, $user);
    }
}
