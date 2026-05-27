<?php

namespace App\Rules;

use App\Models\TrainingGroup;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PublicTrainingGroup implements ValidationRule
{
    public function __construct(
        private readonly mixed $trainingProgramId = null,
        private readonly mixed $branchId = null,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $group = TrainingGroup::query()
            ->operationalList()
            ->visibleOnSite()
            ->whereKey($value)
            ->when(filled($this->trainingProgramId), fn ($query) => $query->where('training_program_id', $this->trainingProgramId))
            ->when(filled($this->branchId), fn ($query) => $query->where('branch_id', $this->branchId))
            ->first();

        if ($group?->acceptsPublicApplications()) {
            return;
        }

        $fail(tkey('website.validation.group_unavailable'));
    }
}
