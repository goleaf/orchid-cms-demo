<?php

namespace App\Actions;

use App\Models\LearningProgram;
use App\Models\User;

class UpdateLearningProgramAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(LearningProgram $program, array $data, ?User $user = null): LearningProgram
    {
        $program = app(CreateOrUpdateLearningProgramAction::class)->handle($program, $data, $user);

        if ($program->is_default) {
            LearningProgram::query()->whereKeyNot($program->id)->update(['is_default' => false]);
        }

        return $program->refresh();
    }
}
