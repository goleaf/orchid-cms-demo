<?php

namespace App\Actions;

use App\Models\LearningProgramModule;
use App\Models\LearningTopic;
use App\Models\User;

class CreateLearningTopicAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data, ?User $user = null): LearningTopic
    {
        if (! isset($data['training_program_id']) && isset($data['learning_program_module_id'])) {
            $data['training_program_id'] = LearningProgramModule::query()
                ->whereKey($data['learning_program_module_id'])
                ->with('program:id,course_id')
                ->first()
                ?->program
                ?->course_id;
        }

        $data['title_translations'] = $data['title_translations'] ?? $data['name_translations'] ?? null;

        return app(CreateOrUpdateLearningTopicAction::class)->handle(new LearningTopic, $data, $user);
    }
}
