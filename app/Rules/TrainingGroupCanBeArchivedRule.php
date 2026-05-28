<?php

namespace App\Rules;

use App\Models\TrainingGroup;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class TrainingGroupCanBeArchivedRule implements ValidationRule
{
    public function __construct(
        private readonly ?TrainingGroup $group = null,
        private readonly ?User $user = null,
        private readonly bool $allowOverride = false,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $this->group?->exists || $this->allowOverride || ($this->user?->hasAccess('education.groups.override_status_transition') ?? false)) {
            return;
        }

        if ($this->group->activeMemberships()->exists()) {
            $fail(tkey('education.groups.validation.group_cannot_be_archived'));
        }
    }
}
