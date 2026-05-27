<?php

namespace App\Rules;

use App\Models\TrainingProgram;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ActivePublicTrainingProgram implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (filled($value) && TrainingProgram::query()->whereKey($value)->where('is_active', true)->exists()) {
            return;
        }

        $fail(tkey('website.validation.course_unavailable'));
    }
}
