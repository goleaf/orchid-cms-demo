<?php

namespace App\Rules;

use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;

class TrainingGroupDateRangeRule implements DataAwareRule, ValidationRule
{
    /**
     * @var array<string, mixed>
     */
    private array $data = [];

    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $start = $this->date(data_get($this->data, 'group.start_date', data_get($this->data, 'group.starts_on')));
        $plannedEnd = $this->date(data_get($this->data, 'group.planned_end_date', data_get($this->data, 'group.ends_on')));
        $actualEnd = $this->date(data_get($this->data, 'group.actual_end_date'));

        if ($start !== null && $plannedEnd !== null && $start->gt($plannedEnd)) {
            $fail(tkey('education.groups.validation.start_date_after_end_date'));

            return;
        }

        if ($start !== null && $actualEnd !== null && $actualEnd->lt($start)) {
            $fail(tkey('education.groups.validation.actual_end_before_start_date'));
        }
    }

    private function date(mixed $value): ?Carbon
    {
        return filled($value) ? Carbon::parse($value) : null;
    }
}
