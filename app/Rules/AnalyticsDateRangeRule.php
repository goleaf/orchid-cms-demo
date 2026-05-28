<?php

namespace App\Rules;

use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Throwable;

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
        $startValue = data_get($this->data, $this->startKey);
        $endValue = data_get($this->data, $this->endKey);

        if ($startValue === null && $endValue === null && is_array($value)) {
            $startValue = $value['period_start'] ?? $value['starts_on'] ?? $value['start'] ?? null;
            $endValue = $value['period_end'] ?? $value['ends_on'] ?? $value['end'] ?? null;
        }

        $start = $this->date($startValue);
        $end = $this->date($endValue);

        if ((filled($startValue) && $start === null) || (filled($endValue) && $end === null)) {
            $fail(tkey('analytics.validation.invalid_date_range'));

            return;
        }

        if ($start === null || $end === null) {
            return;
        }

        if ($start->gt($end)) {
            $fail(tkey('analytics.validation.invalid_date_range'));

            return;
        }

        if ($start->diffInDays($end) > $this->maxDays) {
            $fail(tkey('analytics.validation.invalid_date_range'));
        }
    }

    private function date(mixed $value): ?Carbon
    {
        if (! filled($value)) {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (Throwable) {
            return null;
        }
    }
}
