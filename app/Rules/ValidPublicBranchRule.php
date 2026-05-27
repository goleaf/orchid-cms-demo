<?php

namespace App\Rules;

use App\Models\Branch;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidPublicBranchRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (filled($value) && Branch::query()->active()->visibleOnSite()->whereKey($value)->exists()) {
            return;
        }

        $fail(tkey('website.validation.invalid_public_branch'));
    }
}
