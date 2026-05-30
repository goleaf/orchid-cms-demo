<?php

namespace App\Rules;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UserCanBeUnblockedRule implements ValidationRule
{
    public function __construct(private readonly ?User $target) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $this->target?->exists) {
            return;
        }

        if ($this->target->isArchived()) {
            $fail(tkey('security.validation.user_cannot_be_unblocked'));
        }
    }
}
