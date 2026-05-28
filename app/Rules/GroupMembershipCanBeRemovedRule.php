<?php

namespace App\Rules;

use App\Models\TrainingGroupMembership;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class GroupMembershipCanBeRemovedRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $membership = filled($value) ? TrainingGroupMembership::query()->find($value) : null;

        if ($membership?->is_active || $membership?->is_waitlisted) {
            return;
        }

        $fail(tkey('education.groups.validation.membership_cannot_be_removed'));
    }
}
