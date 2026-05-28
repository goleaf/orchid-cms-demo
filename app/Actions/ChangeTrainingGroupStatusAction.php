<?php

namespace App\Actions;

use App\Enums\GroupStatus;
use App\Models\TrainingGroup;
use App\Models\TrainingGroupStatus;
use App\Models\User;
use App\Rules\ValidTrainingGroupStatusTransitionRule;
use Illuminate\Support\Facades\Validator;

class ChangeTrainingGroupStatusAction
{
    public function handle(TrainingGroup $group, TrainingGroupStatus|int|string $status, ?User $user = null, bool $allowOverride = false, ?string $comment = null): TrainingGroup
    {
        $target = $this->status($status);

        Validator::make(
            ['status_id' => $target->id],
            ['status_id' => [new ValidTrainingGroupStatusTransitionRule($group, $user, $allowOverride)]],
        )->validate();

        $old = $group->statusRecord?->code ?? $group->status?->value;

        $payload = [
            'status_id' => $target->id,
            'status' => $this->legacyStatusFor($target->code)->value,
            'updated_by_id' => $user?->id ?? $group->updated_by_id,
        ];

        if ($target->is_final || $target->is_success || $target->is_cancelled) {
            $payload['actual_end_date'] = $group->actual_end_date ?? now()->toDateString();
        }

        if ($target->is_cancelled || $target->is_archived) {
            $payload['is_visible_on_site'] = false;
            $payload['is_accepting_applications'] = false;
        }

        $group->forceFill($payload)->save();

        app(RecordTrainingGroupActivityAction::class)->handle(
            $group->refresh(),
            $user,
            'status_changed',
            tkey('education.activities.titles.status_changed'),
            $comment,
            $old,
            $target->code,
        );

        return $group->refresh();
    }

    private function status(TrainingGroupStatus|int|string $status): TrainingGroupStatus
    {
        return $status instanceof TrainingGroupStatus
            ? $status
            : TrainingGroupStatus::query()
                ->when(is_numeric($status), fn ($query) => $query->whereKey($status), fn ($query) => $query->where('code', $status))
                ->firstOrFail();
    }

    private function legacyStatusFor(string $code): GroupStatus
    {
        return match ($code) {
            'recruiting' => GroupStatus::Recruiting,
            'almost_full', 'full' => GroupStatus::AlmostFull,
            'closed', 'archived' => GroupStatus::Closed,
            'active', 'paused' => GroupStatus::Active,
            'completed' => GroupStatus::Completed,
            'cancelled' => GroupStatus::Cancelled,
            default => GroupStatus::Planned,
        };
    }
}
