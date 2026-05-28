<?php

namespace App\Http\Requests\Analytics;

use App\Http\Requests\Analytics\Concerns\UsesAnalyticsRequestValidation;
use App\Models\Branch;
use App\Models\ReportDefinition;
use App\Models\TrainingGroup;
use App\Models\TrainingProgram;
use App\Models\User;
use App\Rules\ActiveReportDefinitionRule;
use App\Rules\AnalyticsDateRangeRule;
use App\Rules\AnalyticsPermissionRule;
use App\Rules\ReportColumnAllowedRule;
use App\Rules\ReportFilterValueAllowedRule;
use App\Rules\ValidKpiPeriodRule;
use App\Rules\ValidReportFilterRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RunReportRequest extends FormRequest
{
    use UsesAnalyticsRequestValidation;

    public function authorize(): bool
    {
        return $this->analyticsAccess('analytics.reports.run');
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $definition = $this->reportDefinitionForValidation();

        return [
            'report_definition_id' => [
                'required',
                'integer',
                Rule::exists(ReportDefinition::class, 'id'),
                new ActiveReportDefinitionRule,
                new AnalyticsPermissionRule($this->user(), 'analytics.reports.run'),
            ],
            'filters' => ['nullable', 'array', new ValidReportFilterRule($definition)],
            'filters.period_type' => ['nullable', 'string', new ValidKpiPeriodRule],
            'filters.period_start' => ['nullable', 'date', new AnalyticsDateRangeRule],
            'filters.period_end' => ['nullable', 'date'],
            'filters.start_date' => ['nullable', 'date', new AnalyticsDateRangeRule('filters.start_date', 'filters.end_date')],
            'filters.end_date' => ['nullable', 'date'],
            'filters.branch_id' => ['nullable', 'integer', Rule::exists(Branch::class, 'id')],
            'filters.training_program_id' => ['nullable', 'integer', Rule::exists(TrainingProgram::class, 'id')],
            'filters.training_group_id' => ['nullable', 'integer', Rule::exists(TrainingGroup::class, 'id')],
            'filters.user_id' => ['nullable', 'integer', Rule::exists(User::class, 'id')],
            'filters.instructor_id' => ['nullable', 'integer', Rule::exists(User::class, 'id')],
            'filters.manager_id' => ['nullable', 'integer', Rule::exists(User::class, 'id')],
            'filters.status' => ['nullable', new ReportFilterValueAllowedRule($definition, 'status')],
            'filters.source' => ['nullable', new ReportFilterValueAllowedRule($definition, 'source')],
            'filters.columns' => ['nullable', new ReportColumnAllowedRule($definition)],
            'columns' => ['nullable', new ReportColumnAllowedRule($definition)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return $this->analyticsValidationMessages([
            'report_definition_id.required' => tkey('analytics.validation.report_required'),
            'report_definition_id.exists' => tkey('analytics.validation.report_not_active'),
        ]);
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

    private function reportDefinitionForValidation(): ?ReportDefinition
    {
        $id = $this->input('report_definition_id');

        if (! filled($id)) {
            return null;
        }

        return ReportDefinition::query()->withoutGlobalScopes()->find($id);
    }
}
