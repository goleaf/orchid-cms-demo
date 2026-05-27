<?php

namespace App\Rules;

use App\Models\Branch;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ActivePublicBranch implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (filled($value) && Branch::query()->whereKey($value)->where('is_active', true)->exists()) {
            return;
        }

        $fail(tkey('website.validation.branch_unavailable'));
    }
}
