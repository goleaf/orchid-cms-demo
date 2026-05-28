<?php

namespace App\Rules;

use App\Models\TrainingGroupMembership;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class StudentEnrollmentNotAlreadyInActiveGroupRule implements ValidationRule
{
    public function __construct(private readonly ?int $ignoreMembershipId = null) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! filled($value)) {
            return;
        }

        $exists = TrainingGroupMembership::query()
            ->active()
            ->where('enrollment_id', $value)
            ->when($this->ignoreMembershipId !== null, fn ($query) => $query->whereKeyNot($this->ignoreMembershipId))
            ->exists();

        if ($exists) {
            $fail(tkey('education.groups.validation.enrollment_already_in_active_group'));
        }
    }
}
