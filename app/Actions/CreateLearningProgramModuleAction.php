<?php

namespace App\Actions;

use App\Models\LearningProgramModule;
use App\Models\User;

class CreateLearningProgramModuleAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data, ?User $user = null): LearningProgramModule
    {
        return app(CreateOrUpdateLearningProgramModuleAction::class)->handle(new LearningProgramModule, $data, $user);
    }
}
