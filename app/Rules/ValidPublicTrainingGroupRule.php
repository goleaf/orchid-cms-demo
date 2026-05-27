<?php

namespace App\Rules;

use App\Models\TrainingGroup;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidPublicTrainingGroupRule implements DataAwareRule, ValidationRule
{
    /**
     * @var array<string, mixed>
     */
    private array $data = [];

    public function __construct(private readonly bool $allowOverbooking = false) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! filled($value)) {
            return;
        }

        $group = TrainingGroup::query()
            ->operationalList()
            ->whereKey($value)
            ->first();

        if ($group === null || ! $group->acceptsPublicApplications()) {
            $fail(tkey('website.validation.invalid_public_group'));

            return;
        }

        $overbookingAllowed = $this->allowOverbooking
            || filter_var(data_get($this->data, 'allow_overbooking'), FILTER_VALIDATE_BOOLEAN);

        if (! $overbookingAllowed && $group->is_full) {
            $fail(tkey('website.validation.group_is_full'));
        }
    }
}
