<?php

namespace App\Actions;

use App\Models\LearningProgram;
use App\Models\User;

class CreateOrUpdateLearningProgramAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(LearningProgram $program, array $data, ?User $user = null): LearningProgram
    {
        if (! $program->exists) {
            $data['created_by_id'] = $data['created_by_id'] ?? $user?->id;
        }

        $data['updated_by_id'] = $data['updated_by_id'] ?? $user?->id;

        return app(CreateOrUpdateCourseAction::class)->handle($program, $data);
    }
}
