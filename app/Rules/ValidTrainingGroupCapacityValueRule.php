<?php

namespace App\Rules;

use App\Models\TrainingGroup;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidTrainingGroupCapacityValueRule implements ValidationRule
{
    public function __construct(
        private readonly ?TrainingGroup $group = null,
        private readonly bool $allowLowerThanMemberships = false,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_numeric($value) || (int) $value < 1) {
            $fail(tkey('education.groups.validation.invalid_capacity'));

            return;
        }

        if ($this->group?->exists && ! $this->allowLowerThanMemberships) {
            $activeCount = $this->group->activeMemberships()->count();

            if ((int) $value < $activeCount) {
                $fail(tkey('education.groups.validation.capacity_lower_than_memberships'));
            }
        }
    }
}
