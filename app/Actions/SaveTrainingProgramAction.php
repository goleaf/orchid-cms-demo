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
        $program->fill($attributes);
        $program->save();

        return $program;
    }
}
