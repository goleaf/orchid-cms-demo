<?php

namespace App\Rules;

use App\Models\TrainingGroup;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class TrainingGroupCanAcceptEnrollmentRule implements ValidationRule
{
    public function __construct(private readonly bool $allowOverbooking = false) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! filled($value)) {
            return;
        }

        $group = TrainingGroup::query()->find($value);

        if ($group === null || (! $this->allowOverbooking && (! $group->acceptsEnrollment() || $group->is_full))) {
            $fail(tkey('education.validation.group_cannot_accept_enrollment'));
        }
    }
}
