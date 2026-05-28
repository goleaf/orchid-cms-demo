<?php

namespace App\Actions;

use App\Models\LearningTopic;
use App\Models\User;

class CreateOrUpdateLearningTopicAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(LearningTopic $topic, array $data, ?User $user = null): LearningTopic
    {
        if (! $topic->exists) {
            $data['created_by_id'] = $data['created_by_id'] ?? $user?->id;
        }

        $data['updated_by_id'] = $data['updated_by_id'] ?? $user?->id;

        $topic->fill($data);
        $topic->save();

        return $topic->refresh();
    }
}
