<?php

namespace App\Actions;

use App\Models\TrainingGroupSchedulePattern;
use App\Models\User;

class CreateOrUpdateTrainingGroupSchedulePatternAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(TrainingGroupSchedulePattern $pattern, array $data, ?User $user = null): TrainingGroupSchedulePattern
    {
        if (! $pattern->exists) {
            $data['created_by_id'] = $data['created_by_id'] ?? $user?->id;
        }

        $data['updated_by_id'] = $data['updated_by_id'] ?? $user?->id;

        $pattern->fill($data);
        $pattern->save();

        app(RecordTrainingGroupActivityAction::class)->handle(
            $pattern->group()->firstOrFail(),
            $user,
            'schedule_pattern_saved',
            tkey('education.activities.titles.schedule_pattern_saved'),
            null,
            null,
            (string) $pattern->id,
        );

        return $pattern->refresh();
    }
}
