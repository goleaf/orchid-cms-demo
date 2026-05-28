<?php

namespace App\Rules;

use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;

class AnalyticsDateRangeRule implements DataAwareRule, ValidationRule
{
    /**
     * @var array<string, mixed>
     */
    private array $data = [];

    public function __construct(
        private readonly string $startKey = 'filters.period_start',
        private readonly string $endKey = 'filters.period_end',
        private readonly int $maxDays = 400,
    ) {}

    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $start = $this->date(data_get($this->data, $this->startKey));
        $end = $this->date(data_get($this->data, $this->endKey));

        if ($start === null || $end === null) {
            return;
        }

        if ($start->gt($end)) {
            $fail(tkey('analytics.validation.invalid_date_range'));

            return;
        }

        if ($start->diffInDays($end) > $this->maxDays) {
            $fail(tkey('analytics.validation.date_range_too_large'));
        }
    }

    private function date(mixed $value): ?Carbon
    {
        return filled($value) ? Carbon::parse($value) : null;
    }
}
