<?php

namespace App\Rules;

use App\Enums\GroupStatus;
use App\Models\StudentEnrollment;
use App\Models\TrainingGroup;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class EnrollmentCanJoinGroupRule implements ValidationRule
{
    public function __construct(
        private readonly ?StudentEnrollment $enrollment = null,
        private readonly bool $allowOverbooking = false,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! filled($value)) {
            return;
        }

        $group = TrainingGroup::query()->find($value);

        if ($group === null) {
            $fail(tkey('students.validation.enrollment_cannot_join_group'));

            return;
        }

        if (! $this->allowOverbooking && (! $this->groupAcceptsEnrollment($group) || $group->is_full)) {
            $fail(tkey('students.validation.enrollment_cannot_join_group'));

            return;
        }

        if ($this->enrollment !== null && $group->training_program_id !== null && (int) $this->enrollment->training_program_id !== (int) $group->training_program_id) {
            $fail(tkey('students.validation.enrollment_cannot_join_group'));
        }
    }

    private function groupAcceptsEnrollment(TrainingGroup $group): bool
    {
        return in_array($group->status, [
            GroupStatus::Planned,
            GroupStatus::Recruiting,
            GroupStatus::Open,
            GroupStatus::AlmostFull,
            GroupStatus::Active,
        ], true);
    }
}
