<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;

class SchedulePatternTimeRangeRule implements DataAwareRule, ValidationRule
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
        $start = data_get($this->data, 'pattern.start_time', data_get($this->data, 'pattern.starts_at'));
        $end = data_get($this->data, 'pattern.end_time', data_get($this->data, 'pattern.ends_at', $value));

        if (filled($start) && filled($end) && strcmp((string) $end, (string) $start) <= 0) {
            $fail(tkey('education.groups.validation.end_time_before_start_time'));
        }
    }
}
