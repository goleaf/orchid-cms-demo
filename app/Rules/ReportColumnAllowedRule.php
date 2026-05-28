<?php

namespace App\Rules;

use App\Models\ReportDefinition;
use App\Rules\Concerns\InteractsWithAnalyticsValidation;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ReportColumnAllowedRule implements ValidationRule
{
    use InteractsWithAnalyticsValidation;

    /**
     * @param  array<int, string>  $allowedColumns
     */
    public function __construct(
        private readonly ?ReportDefinition $definition = null,
        private readonly array $allowedColumns = [],
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $columns = is_array($value) ? $value : [$value];

        foreach ($columns as $column) {
            if (! is_string($column) || ! in_array($column, $this->allowedColumnKeys(), true)) {
                $fail(tkey('analytics.validation.column_not_allowed'));

                return;
            }
        }
    }

    /**
     * @return array<int, string>
     */
    private function allowedColumnKeys(): array
    {
        $keys = array_merge($this->allowedColumns, [
            'id',
            'code',
            'name',
            'status',
            'source',
            'period',
            'period_start',
            'period_end',
            'created_at',
            'updated_at',
            'row_count',
            'value',
            'target_value',
            'paid_revenue',
            'paid_revenue_cents',
            'open_leads',
            'converted_leads',
            'active_students',
            'active_enrollments',
        ]);

        if ($this->definition instanceof ReportDefinition) {
            $keys = array_merge(
                $keys,
                $this->schemaKeys($this->definition->getAttribute('columns_schema')),
                $this->schemaKeys($this->definition->getAttribute('column_config')),
                $this->associativeKeys($this->definition->getAttribute('column_config')),
            );
        }

        return array_values(array_unique(array_filter($keys)));
    }
}
