<?php

namespace App\Actions;

use App\Models\LearningTopic;
use App\Models\User;

class UpdateLearningTopicAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(LearningTopic $topic, array $data, ?User $user = null): LearningTopic
    {
        return app(CreateOrUpdateLearningTopicAction::class)->handle($topic, $data, $user);
    }
}
