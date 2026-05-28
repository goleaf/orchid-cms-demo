<?php

namespace App\Http\Requests\Analytics;

use App\Enums\KpiDirection;
use App\Http\Requests\Analytics\Concerns\UsesAnalyticsRequestValidation;
use App\Models\Branch;
use App\Models\KpiMetric;
use App\Models\KpiTarget;
use App\Models\TrainingGroup;
use App\Models\TrainingProgram;
use App\Models\User;
use App\Rules\ActiveKpiMetricRule;
use App\Rules\AnalyticsDateRangeRule;
use App\Rules\AnalyticsPermissionRule;
use App\Rules\KpiTargetUniquenessRule;
use App\Rules\KpiTargetValueRule;
use App\Rules\ValidKpiPeriodRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class KpiTargetRequest extends FormRequest
{
    use UsesAnalyticsRequestValidation;

    public function authorize(): bool
    {
        return $this->analyticsAccess('analytics.kpi_targets.manage');
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $targetId = $this->targetId();

        return [
            'target.id' => ['nullable', 'integer', Rule::exists(KpiTarget::class, 'id')],
            'target.kpi_metric_id' => [
                'required',
                'integer',
                Rule::exists(KpiMetric::class, 'id'),
                new ActiveKpiMetricRule,
                new KpiTargetUniquenessRule(ignoreId: $targetId),
                new AnalyticsPermissionRule($this->user(), 'analytics.kpi_targets.manage'),
            ],
            'target.period_type' => ['required_without:target.period', 'string', new ValidKpiPeriodRule],
            'target.period_start' => ['required_without:target.starts_on', 'date', new AnalyticsDateRangeRule('target.period_start', 'target.period_end')],
            'target.period_end' => ['nullable', 'date'],
            'target.user_id' => ['nullable', 'integer', Rule::exists(User::class, 'id')],
            'target.warning_threshold' => ['nullable', 'numeric'],
            'target.success_threshold' => ['nullable', 'numeric'],
            'target.period' => ['required_without:target.period_type', 'string', new ValidKpiPeriodRule],
            'target.starts_on' => ['required_without:target.period_start', 'date', new AnalyticsDateRangeRule('target.starts_on', 'target.ends_on')],
            'target.ends_on' => ['nullable', 'date'],
            'target.target_value' => ['required', 'numeric', new KpiTargetValueRule],
            'target.warning_value' => ['nullable', 'numeric', new KpiTargetValueRule],
            'target.direction' => ['nullable', Rule::enum(KpiDirection::class)],
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
        return $this->analyticsValidationMessages([
            'target.kpi_metric_id.required' => tkey('analytics.validation.metric_required'),
            'target.target_value.required' => tkey('analytics.validation.target_required'),
        ]);
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

    public function targetId(): ?int
    {
        $id = $this->input('target.id');

        return filled($id) ? (int) $id : null;
    }
}
