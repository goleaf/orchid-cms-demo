<?php

namespace App\Rules;

use App\Models\TrainingGroup;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class TrainingGroupCanAcceptApplicationsRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $group = $value instanceof TrainingGroup
            ? $value
            : (filled($value) ? TrainingGroup::query()->find($value) : null);

        if ($group !== null && $group->is_visible_on_site && $group->acceptsEnrollment() && ! $group->is_full) {
            return;
        }

        $fail(tkey('education.groups.validation.group_cannot_accept_applications'));
    }
}
