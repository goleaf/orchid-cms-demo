<?php

namespace App\Http\Requests\Analytics;

use App\Models\Branch;
use App\Models\ReportDefinition;
use App\Models\TrainingProgram;
use App\Rules\ActiveReportDefinitionRule;
use App\Rules\AnalyticsDateRangeRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RunReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAccess('analytics.reports.run') ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'report_definition_id' => ['required', 'integer', Rule::exists(ReportDefinition::class, 'id'), new ActiveReportDefinitionRule],
            'filters' => ['nullable', 'array'],
            'filters.period_start' => ['nullable', 'date', new AnalyticsDateRangeRule],
            'filters.period_end' => ['nullable', 'date'],
            'filters.branch_id' => ['nullable', 'integer', Rule::exists(Branch::class, 'id')],
            'filters.training_program_id' => ['nullable', 'integer', Rule::exists(TrainingProgram::class, 'id')],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'report_definition_id.required' => tkey('analytics.validation.report_required'),
            'report_definition_id.exists' => tkey('analytics.validation.report_not_active'),
        ];
    }

    public function reportDefinitionId(): int
    {
        return (int) $this->validated('report_definition_id');
    }

    /**
     * @return array<string, mixed>
     */
    public function filters(): array
    {
        return $this->validated('filters', []);
    }
}
