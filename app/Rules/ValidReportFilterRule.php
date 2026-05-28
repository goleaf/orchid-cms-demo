<?php

namespace App\Rules;

use App\Models\ReportDefinition;
use App\Rules\Concerns\InteractsWithAnalyticsValidation;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidReportFilterRule implements ValidationRule
{
    use InteractsWithAnalyticsValidation;

    /**
     * @param  array<int, string>  $allowedFilters
     */
    public function __construct(
        private readonly ?ReportDefinition $definition = null,
        private readonly array $allowedFilters = [],
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === []) {
            return;
        }

        if (! is_array($value) || $this->hasForbiddenAnalyticsScope($value)) {
            $fail(tkey('analytics.validation.invalid_filter'));

            return;
        }

        $allowed = $this->allowedFilterKeys();

        foreach (array_keys($value) as $key) {
            if (! is_string($key) || preg_match('/^[a-z][a-z0-9_.-]*$/', $key) !== 1 || ! in_array($key, $allowed, true)) {
                $fail(tkey('analytics.validation.invalid_filter'));

                return;
            }
        }
    }

    /**
     * @return array<int, string>
     */
    private function allowedFilterKeys(): array
    {
        $keys = array_merge($this->allowedFilters, [
            'period_type',
            'period_start',
            'period_end',
            'start_date',
            'end_date',
            'branch_id',
            'user_id',
            'training_program_id',
            'training_group_id',
            'instructor_id',
            'manager_id',
            'responsible_manager_id',
            'student_id',
            'lead_id',
            'status',
            'source',
            'report_group',
            'widget_type',
        ]);

        if ($this->definition instanceof ReportDefinition) {
            $keys = array_merge(
                $keys,
                $this->schemaKeys($this->definition->getAttribute('filters_schema')),
                $this->associativeKeys($this->definition->getAttribute('default_filters')),
            );
        }

        return array_values(array_unique(array_filter($keys)));
    }
}
