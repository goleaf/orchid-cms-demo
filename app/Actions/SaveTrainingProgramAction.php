<?php

namespace App\Actions;

use App\Models\TrainingProgram;

class SaveTrainingProgramAction
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(TrainingProgram $program, array $attributes): TrainingProgram
    {
        return app(CreateOrUpdateCourseAction::class)->handle($program, $attributes);
    }
}
