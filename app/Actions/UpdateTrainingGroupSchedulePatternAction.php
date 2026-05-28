<?php

namespace App\Actions;

use App\Models\TrainingGroupSchedulePattern;
use App\Models\User;

class UpdateTrainingGroupSchedulePatternAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(TrainingGroupSchedulePattern $pattern, array $data, ?User $user = null): TrainingGroupSchedulePattern
    {
        $pattern->fill([
            'updated_by_id' => $data['updated_by_id'] ?? $user?->id,
            ...$data,
        ]);
        $pattern->save();

        app(RecordTrainingGroupActivityAction::class)->handle($pattern->group()->firstOrFail(), $user, 'schedule_pattern_updated', tkey('education.activities.titles.schedule_pattern_updated'), null, null, (string) $pattern->id);

        return $pattern->refresh();
    }
}
