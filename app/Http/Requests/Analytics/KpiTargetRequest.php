<?php

namespace App\Http\Requests\Analytics;

use App\Enums\KpiDirection;
use App\Enums\KpiPeriod;
use App\Models\Branch;
use App\Models\KpiMetric;
use App\Models\TrainingGroup;
use App\Models\TrainingProgram;
use App\Models\User;
use App\Rules\ActiveKpiMetricRule;
use App\Rules\AnalyticsDateRangeRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class KpiTargetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAccess('analytics.kpi_targets.manage') ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'target.kpi_metric_id' => ['required', 'integer', Rule::exists(KpiMetric::class, 'id'), new ActiveKpiMetricRule],
            'target.period' => ['required', Rule::enum(KpiPeriod::class)],
            'target.starts_on' => ['required', 'date', new AnalyticsDateRangeRule('target.starts_on', 'target.ends_on')],
            'target.ends_on' => ['nullable', 'date'],
            'target.target_value' => ['required', 'numeric'],
            'target.warning_value' => ['nullable', 'numeric'],
            'target.direction' => ['required', Rule::enum(KpiDirection::class)],
            'target.branch_id' => ['nullable', 'integer', Rule::exists(Branch::class, 'id')],
            'target.training_program_id' => ['nullable', 'integer', Rule::exists(TrainingProgram::class, 'id')],
            'target.training_group_id' => ['nullable', 'integer', Rule::exists(TrainingGroup::class, 'id')],
            'target.assigned_to_user_id' => ['nullable', 'integer', Rule::exists(User::class, 'id')],
            'target.metadata' => ['nullable', 'array'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'target.kpi_metric_id.required' => tkey('analytics.validation.metric_required'),
            'target.target_value.required' => tkey('analytics.validation.target_required'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function targetData(): array
    {
        return $this->validated('target');
    }
}
