<?php

namespace App\Actions;

use App\Models\TrainingGroup;
use App\Models\User;
use App\Rules\GroupCanBePublishedRule;
use Illuminate\Support\Facades\Validator;

class PublishTrainingGroupOnSiteAction
{
    public function handle(TrainingGroup $group, ?User $user = null): TrainingGroup
    {
        Validator::make(['group' => $group], ['group' => [new GroupCanBePublishedRule]])->validate();

        $group->forceFill([
            'is_visible_on_site' => true,
            'is_accepting_applications' => $group->acceptsEnrollment() && ! $group->is_full,
            'updated_by_id' => $user?->id ?? $group->updated_by_id,
        ])->save();

        app(RecordTrainingGroupActivityAction::class)->handle($group->refresh(), $user, 'published_on_site', tkey('education.activities.titles.published_on_site'));

        return $group->refresh();
    }
}
