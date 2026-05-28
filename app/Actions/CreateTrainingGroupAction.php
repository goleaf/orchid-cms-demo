<?php

namespace App\Actions;

use App\Enums\GroupStatus;
use App\Models\TrainingGroup;
use App\Models\TrainingGroupStatus;
use App\Models\User;

class CreateTrainingGroupAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data, ?User $user = null): TrainingGroup
    {
        $status = $this->status($data['status_id'] ?? null, $data['status'] ?? null);
        $groupNumber = $data['group_number'] ?? app(GenerateTrainingGroupNumberAction::class)->handle();
        $legacyStatus = $this->legacyStatusValue($data['status'] ?? null, $status?->code);

        $data = [
            'uuid' => $data['uuid'] ?? null,
            'group_number' => $groupNumber,
            'code' => $data['code'] ?? $groupNumber,
            'status_id' => $status?->id,
            'status' => $legacyStatus,
            'capacity' => (int) ($data['capacity'] ?? $data['capacity_total'] ?? 12),
            'capacity_total' => (int) ($data['capacity_total'] ?? $data['capacity'] ?? 12),
            'capacity_reserved' => (int) ($data['capacity_reserved'] ?? 0),
            'capacity_taken' => (int) ($data['capacity_taken'] ?? $data['places_taken'] ?? 0),
            'capacity_waitlist' => (int) ($data['capacity_waitlist'] ?? 0),
            'places_taken' => (int) ($data['places_taken'] ?? $data['capacity_taken'] ?? 0),
            'is_visible_on_site' => (bool) ($data['is_visible_on_site'] ?? false),
            'is_featured' => (bool) ($data['is_featured'] ?? false),
            'is_accepting_applications' => (bool) ($data['is_accepting_applications'] ?? false),
            'created_by_id' => $data['created_by_id'] ?? $user?->id,
            'updated_by_id' => $data['updated_by_id'] ?? $user?->id,
            ...$data,
        ];

        $data['status'] = $legacyStatus;
        $data['name'] = $data['name'] ?? $this->fallbackName($data, $groupNumber);

        $group = TrainingGroup::query()->create($data);

        app(RecordTrainingGroupActivityAction::class)->handle(
            $group,
            $user,
            'created',
            tkey('education.activities.titles.created'),
        );

        return $group->refresh();
    }

    private function status(mixed $statusId, mixed $statusCode): ?TrainingGroupStatus
    {
        if (filled($statusId)) {
            return TrainingGroupStatus::query()->find($statusId);
        }

        if (filled($statusCode)) {
            return TrainingGroupStatus::query()->where('code', $statusCode)->first();
        }

        return TrainingGroupStatus::query()->where('is_default', true)->first()
            ?? TrainingGroupStatus::query()->where('code', 'draft')->first();
    }

    private function legacyStatusFor(?string $code): GroupStatus
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

    private function legacyStatusValue(mixed $value, ?string $dictionaryCode): string
    {
        if ($value instanceof GroupStatus) {
            return $value->value;
        }

        if (is_string($value) && in_array($value, array_column(GroupStatus::cases(), 'value'), true)) {
            return $value;
        }

        return $this->legacyStatusFor($dictionaryCode)->value;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function fallbackName(array $data, string $groupNumber): string
    {
        $translations = $data['name_translations'] ?? [];

        return is_array($translations) && filled($translations['en'] ?? null)
            ? (string) $translations['en']
            : $groupNumber;
    }
}
