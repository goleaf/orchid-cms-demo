<?php

namespace App\Actions;

use App\Models\LearningProgram;
use App\Models\TrainingGroup;
use App\Models\User;
use App\Rules\LearningProgramIsActiveRule;
use Illuminate\Support\Facades\Validator;

class AssignLearningProgramToGroupAction
{
    public function handle(TrainingGroup $group, LearningProgram|int $program, ?User $user = null): TrainingGroup
    {
        $program = $program instanceof LearningProgram ? $program : LearningProgram::query()->findOrFail($program);

        Validator::make(['learning_program_id' => $program->id], ['learning_program_id' => [new LearningProgramIsActiveRule]])->validate();

        $old = $group->learning_program_id;

        $group->forceFill([
            'learning_program_id' => $program->id,
            'updated_by_id' => $user?->id ?? $group->updated_by_id,
        ])->save();

        if ((int) $old !== (int) $program->id) {
            app(RecordTrainingGroupActivityAction::class)->handle(
                $group->refresh(),
                $user,
                'learning_program_assigned',
                tkey('education.activities.titles.learning_program_assigned'),
                null,
                filled($old) ? (string) $old : null,
                (string) $program->id,
            );
        }

        return $group->refresh();
    }
}
