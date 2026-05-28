<?php

namespace App\Actions;

use App\Models\TrainingGroup;
use App\Models\User;

class AddTrainingGroupNoteAction
{
    public function handle(TrainingGroup $group, string $body, ?User $user = null): void
    {
        app(RecordTrainingGroupActivityAction::class)->handle(
            $group,
            $user,
            'note_added',
            tkey('education.activities.titles.note_added'),
            $body,
        );
    }
}
