<?php

namespace App\Actions;

use App\Models\LearningTopic;
use App\Models\User;

class CreateLearningTopicAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data, ?User $user = null): LearningTopic
    {
        return app(CreateOrUpdateLearningTopicAction::class)->handle(new LearningTopic, $data, $user);
    }
}
