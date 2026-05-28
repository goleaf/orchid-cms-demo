<?php

namespace App\Rules;

use App\Models\TrainingGroup;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class TrainingGroupCanBeUpdatedRule implements ValidationRule
{
    public function __construct(
        private readonly ?TrainingGroup $group = null,
        private readonly ?User $user = null,
        private readonly bool $allowOverride = false,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $this->group?->exists || $this->canOverride()) {
            return;
        }

        $status = $this->group->statusRecord;

        if ($status?->is_final || $status?->is_cancelled || $status?->is_archived) {
            $fail(tkey('education.groups.validation.group_cannot_be_updated'));
        }
    }

    private function canOverride(): bool
    {
        return $this->allowOverride
            || ($this->user?->hasAccess('education.groups.override_status_transition') ?? false);
    }
}
