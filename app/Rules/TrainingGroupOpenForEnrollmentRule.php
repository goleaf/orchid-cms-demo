<?php

namespace App\Rules;

use App\Models\TrainingGroup;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class TrainingGroupOpenForEnrollmentRule implements ValidationRule
{
    public function __construct(private readonly bool $allowOverride = false) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $group = filled($value) ? TrainingGroup::query()->find($value) : null;

        if ($group !== null && ($this->allowOverride || ($group->acceptsEnrollment() && ! $group->is_full))) {
            return;
        }

        $fail(tkey('education.groups.validation.group_not_open_for_enrollment'));
    }
}
