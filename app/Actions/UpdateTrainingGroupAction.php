<?php

namespace App\Actions;

use App\Models\TrainingGroup;
use App\Models\User;

class UpdateTrainingGroupAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(TrainingGroup $group, array $data, ?User $user = null): TrainingGroup
    {
        $before = $group->only([
            'capacity',
            'capacity_total',
            'capacity_reserved',
            'capacity_taken',
            'capacity_waitlist',
            'learning_program_id',
            'teacher_id',
            'manager_id',
        ]);

        $data['updated_by_id'] = $data['updated_by_id'] ?? $user?->id;

        $group->fill($data);
        $group->save();
        $group = $group->refresh();

        app(RecordTrainingGroupActivityAction::class)->handle($group, $user, 'updated', tkey('education.activities.titles.updated'));

        $this->recordIfChanged($group, $before, 'capacity_total', 'capacity_changed', $user);
        $this->recordIfChanged($group, $before, 'learning_program_id', 'learning_program_assigned', $user);
        $this->recordIfChanged($group, $before, 'teacher_id', 'teacher_assigned', $user);
        $this->recordIfChanged($group, $before, 'manager_id', 'manager_assigned', $user);

        return $group->refresh();
    }

    /**
     * @param  array<string, mixed>  $before
     */
    private function recordIfChanged(TrainingGroup $group, array $before, string $field, string $type, ?User $user): void
    {
        if ((string) ($before[$field] ?? '') === (string) ($group->{$field} ?? '')) {
            return;
        }

        app(RecordTrainingGroupActivityAction::class)->handle(
            $group,
            $user,
            $type,
            tkey('education.activities.titles.'.$type),
            null,
            filled($before[$field] ?? null) ? (string) $before[$field] : null,
            filled($group->{$field}) ? (string) $group->{$field} : null,
        );
    }
}
