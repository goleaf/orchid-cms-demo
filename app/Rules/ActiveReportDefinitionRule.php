<?php

namespace App\Rules;

use App\Models\ReportDefinition;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Schema;

class ActiveReportDefinitionRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! Schema::hasTable((new ReportDefinition)->getTable())) {
            $fail(tkey('analytics.validation.report_not_active'));

            return;
        }

        if ($value instanceof ReportDefinition && (bool) $value->getAttribute('is_active')) {
            return;
        }

        if (filled($value) && $this->activeReportExists($value)) {
            return;
        }

        $fail(tkey('analytics.validation.report_not_active'));
    }

    private function activeReportExists(mixed $value): bool
    {
        $query = ReportDefinition::query()
            ->withoutGlobalScopes()
            ->where('is_active', true);

        if (Schema::hasColumn((new ReportDefinition)->getTable(), 'deleted_at')) {
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
