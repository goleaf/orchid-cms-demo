<?php

namespace App\Rules;

use App\Enums\GroupStatus;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidTrainingGroupStatusRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! filled($value)) {
            return;
        }

        $valid = collect(GroupStatus::cases())
            ->contains(fn (GroupStatus $status): bool => $status->value === (string) $value);

        if (! $valid) {
            $fail(tkey('education.validation.invalid_group_status'));
        }
    }
}
