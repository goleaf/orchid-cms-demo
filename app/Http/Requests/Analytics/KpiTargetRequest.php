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
            'target.period_type' => ['required_without:target.period', Rule::enum(KpiPeriod::class)],
            'target.period_start' => ['required_without:target.starts_on', 'date', new AnalyticsDateRangeRule('target.period_start', 'target.period_end')],
            'target.period_end' => ['nullable', 'date'],
            'target.user_id' => ['nullable', 'integer', Rule::exists(User::class, 'id')],
            'target.warning_threshold' => ['nullable', 'numeric'],
            'target.success_threshold' => ['nullable', 'numeric'],
            'target.period' => ['required_without:target.period_type', Rule::enum(KpiPeriod::class)],
            'target.starts_on' => ['required_without:target.period_start', 'date', new AnalyticsDateRangeRule('target.starts_on', 'target.ends_on')],
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
        $data = $this->validated('target');
        $data['period_type'] = $data['period_type'] ?? $data['period'];
        $data['period_start'] = $data['period_start'] ?? $data['starts_on'];
        $data['period_end'] = $data['period_end'] ?? ($data['ends_on'] ?? null);
        $data['period'] = $data['period'] ?? $data['period_type'];
        $data['starts_on'] = $data['starts_on'] ?? $data['period_start'];
        $data['ends_on'] = $data['ends_on'] ?? ($data['period_end'] ?? null);
        $data['warning_value'] = $data['warning_value'] ?? ($data['warning_threshold'] ?? null);
        $data['assigned_to_user_id'] = $data['assigned_to_user_id'] ?? ($data['user_id'] ?? null);

        return $data;
    }
}
