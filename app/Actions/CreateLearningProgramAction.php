<?php

namespace App\Actions;

use App\Models\LearningProgram;
use App\Models\User;

class CreateLearningProgramAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data, ?User $user = null): LearningProgram
    {
        $program = app(CreateOrUpdateLearningProgramAction::class)->handle(new LearningProgram, $data, $user);

        if ($program->is_default) {
            LearningProgram::query()->whereKeyNot($program->id)->update(['is_default' => false]);
        }

        return $program->refresh();
    }
}
