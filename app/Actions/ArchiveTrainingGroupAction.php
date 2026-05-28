<?php

namespace App\Actions;

use App\Models\TrainingGroup;
use App\Models\TrainingGroupStatus;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class ArchiveTrainingGroupAction
{
    public function handle(TrainingGroup $group, ?User $user = null, bool $allowActiveMemberships = false): TrainingGroup
    {
        if (! $allowActiveMemberships && ! ($user?->hasAccess('education.groups.override_status_transition') ?? false) && $group->activeMemberships()->exists()) {
            throw ValidationException::withMessages([
                'group' => tkey('education.groups.validation.group_cannot_be_archived'),
            ]);
        }

        $archived = TrainingGroupStatus::query()->where('code', 'archived')->first();

        if ($archived !== null) {
            $group = app(ChangeTrainingGroupStatusAction::class)->handle($group, $archived, $user, true);
        }

        $group->forceFill([
            'is_visible_on_site' => false,
            'is_accepting_applications' => false,
            'updated_by_id' => $user?->id ?? $group->updated_by_id,
        ])->save();

        app(RecordTrainingGroupActivityAction::class)->handle($group->refresh(), $user, 'archived', tkey('education.activities.titles.archived'));

        return $group->refresh();
    }
}
