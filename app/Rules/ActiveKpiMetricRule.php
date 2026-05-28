<?php

namespace App\Rules;

use App\Models\KpiMetric;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Schema;

class ActiveKpiMetricRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! Schema::hasTable((new KpiMetric)->getTable())) {
            $fail(tkey('analytics.validation.kpi_not_active'));

            return;
        }

        if ($value instanceof KpiMetric && (bool) $value->getAttribute('is_active')) {
            return;
        }

        if (filled($value) && $this->activeMetricExists($value)) {
            return;
        }

        $fail(tkey('analytics.validation.kpi_not_active'));
    }

    private function activeMetricExists(mixed $value): bool
    {
        $query = KpiMetric::query()
            ->withoutGlobalScopes()
            ->where('is_active', true);

        if (Schema::hasColumn((new KpiMetric)->getTable(), 'deleted_at')) {
            $query->whereNull('deleted_at');
        }

        if (is_int($value) || (is_string($value) && ctype_digit($value))) {
            return (clone $query)->whereKey((int) $value)->exists();
        }

        if (is_string($value)) {
            return $query->where('code', $value)->exists();
        }

        return false;
    }
}
