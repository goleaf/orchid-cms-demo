<?php

namespace App\Rules;

use App\Models\TrainingGroup;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class TrainingGroupCapacityRule implements ValidationRule
{
    public function __construct(
        private readonly ?TrainingGroup $group = null,
        private readonly bool $allowOverbooking = false,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $group = $this->group ?? (filled($value) ? TrainingGroup::query()->find($value) : null);

        if ($group === null || $this->allowOverbooking || ! $group->is_full) {
            return;
        }

        $fail(tkey('education.groups.validation.capacity_exceeded'));
    }
}
