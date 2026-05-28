<?php

namespace App\Actions;

use App\Models\TrainingGroupSchedulePattern;
use App\Models\User;

class DeleteTrainingGroupSchedulePatternAction
{
    public function handle(TrainingGroupSchedulePattern $pattern, ?User $user = null, bool $softDelete = false): TrainingGroupSchedulePattern
    {
        $group = $pattern->group()->firstOrFail();

        if ($softDelete) {
            $pattern->delete();
        } else {
            $pattern->forceFill([
                'is_active' => false,
                'updated_by_id' => $user?->id ?? $pattern->updated_by_id,
            ])->save();
        }

        app(RecordTrainingGroupActivityAction::class)->handle($group, $user, 'schedule_pattern_deleted', tkey('education.activities.titles.schedule_pattern_deleted'), null, null, (string) $pattern->id);

        return $pattern->refresh();
    }
}
