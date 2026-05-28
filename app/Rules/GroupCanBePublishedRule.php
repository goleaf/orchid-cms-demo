<?php

namespace App\Rules;

use App\Models\TrainingGroup;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class GroupCanBePublishedRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $group = $value instanceof TrainingGroup
            ? $value
            : (filled($value) ? TrainingGroup::query()->find($value) : null);

        if (
            $group !== null
            && filled($group->training_program_id)
            && filled($group->branch_id)
            && filled($group->display_name)
            && (int) ($group->capacity_total ?? $group->capacity) > 0
            && $group->acceptsEnrollment()
            && ! $group->is_full
        ) {
            return;
        }

        $fail(tkey('education.groups.validation.group_cannot_be_published'));
    }
}
