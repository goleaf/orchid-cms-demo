<?php

namespace App\Rules;

use App\Models\TrainingGroupStatus;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ActiveTrainingGroupStatusRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! filled($value)) {
            return;
        }

        $isActive = TrainingGroupStatus::query()
            ->whereKey($value)
            ->where('is_active', true)
            ->exists();

        if (! $isActive) {
            $fail(tkey('education.validation.status_not_active'));
        }
    }
}
