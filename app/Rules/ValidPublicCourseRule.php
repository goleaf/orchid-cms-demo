<?php

namespace App\Rules;

use App\Models\Course;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidPublicCourseRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (filled($value) && Course::query()->active()->visibleOnSite()->whereKey($value)->exists()) {
            return;
        }

        $fail(tkey('website.validation.invalid_public_course'));
    }
}
