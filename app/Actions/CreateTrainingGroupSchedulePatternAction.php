<?php

namespace App\Actions;

use App\Models\TrainingGroupSchedulePattern;
use App\Models\User;

class CreateTrainingGroupSchedulePatternAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data, ?User $user = null): TrainingGroupSchedulePattern
    {
        $pattern = TrainingGroupSchedulePattern::query()->create([
            'created_by_id' => $data['created_by_id'] ?? $user?->id,
            'updated_by_id' => $data['updated_by_id'] ?? $user?->id,
            ...$data,
        ]);

        app(RecordTrainingGroupActivityAction::class)->handle($pattern->group()->firstOrFail(), $user, 'schedule_pattern_created', tkey('education.activities.titles.schedule_pattern_created'), null, null, (string) $pattern->id);

        return $pattern->refresh();
    }
}
