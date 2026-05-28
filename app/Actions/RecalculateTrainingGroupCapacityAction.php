<?php

namespace App\Actions;

use App\Enums\GroupStatus;
use App\Models\TrainingGroup;
use App\Models\TrainingGroupStatus;
use App\Models\User;

class RecalculateTrainingGroupCapacityAction
{
    public function handle(TrainingGroup $group, ?User $user = null, bool $updateStatus = true): TrainingGroup
    {
        $group = TrainingGroup::query()->whereKey($group->getKey())->firstOrFail();

        $old = [
            'capacity_taken' => (int) $group->capacity_taken,
            'capacity_waitlist' => (int) $group->capacity_waitlist,
            'places_taken' => (int) $group->places_taken,
        ];

        $active = $group->memberships()->active()->count();
        $waitlisted = $group->memberships()->waitlisted()->count();

        $payload = [
            'places_taken' => $active,
            'capacity_taken' => $active,
            'capacity_waitlist' => $waitlisted,
            'updated_by_id' => $user?->id ?? $group->updated_by_id,
        ];

        if ($updateStatus) {
            $status = $this->statusForCapacity($group, $active);

            if ($status !== null) {
                $payload['status_id'] = $status->id;
                $payload['status'] = $this->legacyStatusFor($status->code)->value;
            }
        }

        $group->forceFill($payload)->save();
        $group = $group->refresh();

        if ($old['capacity_taken'] !== $active || $old['places_taken'] !== $active || $old['capacity_waitlist'] !== $waitlisted) {
            app(RecordTrainingGroupActivityAction::class)->handle(
                $group,
                $user,
                'capacity_changed',
                tkey('education.activities.titles.capacity_changed'),
                null,
                json_encode($old, JSON_THROW_ON_ERROR),
                json_encode(['capacity_taken' => $active, 'capacity_waitlist' => $waitlisted, 'places_taken' => $active], JSON_THROW_ON_ERROR),
            );
        }

        return $group;
    }

    private function statusForCapacity(TrainingGroup $group, int $active): ?TrainingGroupStatus
    {
        $current = $group->statusRecord;

        if ($current === null || ! ($current->is_open_for_enrollment || $current->accepts_enrollments || in_array($current->code, ['full', 'almost_full'], true))) {
            return null;
        }

        $capacity = max(1, (int) ($group->capacity_total ?? $group->capacity));
        $code = match (true) {
            $active >= $capacity => 'full',
            ($active / $capacity) >= 0.8 => 'almost_full',
            default => 'recruiting',
        };

        return TrainingGroupStatus::query()->where('code', $code)->first();
    }

    private function legacyStatusFor(string $code): GroupStatus
    {
        return match ($code) {
            'recruiting' => GroupStatus::Recruiting,
            'almost_full', 'full' => GroupStatus::AlmostFull,
            default => GroupStatus::Planned,
        };
    }
}
