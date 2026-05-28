<?php

namespace App\Http\Requests\Analytics;

use App\Enums\KpiMetricGroup;
use App\Enums\KpiUnit;
use App\Http\Requests\Analytics\Concerns\UsesAnalyticsRequestValidation;
use App\Models\KpiMetric;
use App\Rules\AnalyticsCodeRule;
use App\Rules\AnalyticsPermissionRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class KpiMetricRequest extends FormRequest
{
    use UsesAnalyticsRequestValidation;

    public function authorize(): bool
    {
        return $this->analyticsAccess('analytics.kpis.manage');
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'metric.id' => ['nullable', 'integer', Rule::exists(KpiMetric::class, 'id')],
            'metric.code' => ['required', 'string', 'max:120', new AnalyticsCodeRule, new AnalyticsPermissionRule($this->user(), 'analytics.kpis.manage')],
            'metric.name_translations' => ['required', 'array'],
            'metric.description_translations' => ['nullable', 'array'],
            'metric.metric_group' => ['required_without:metric.category', Rule::in(KpiMetricGroup::values())],
            'metric.unit' => ['required_without:metric.value_type', Rule::in(KpiUnit::values())],
            'metric.calculation_type' => ['nullable', 'string', 'max:255'],
            'metric.category' => ['nullable', 'string', 'max:80'],
            'metric.value_type' => ['nullable', 'string', 'max:40'],
            'metric.calculation' => ['nullable', 'string', 'max:255'],
            'metric.source' => ['nullable', 'string', 'max:255'],
            'metric.settings' => ['nullable', 'array'],
            'metric.is_active' => ['nullable', 'boolean'],
            'metric.sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return $this->analyticsValidationMessages([
            'metric.code.required' => tkey('analytics.validation.code_required'),
            'metric.name_translations.required' => tkey('analytics.validation.name_required'),
            'metric.metric_group.in' => tkey('analytics.validation.invalid_filter'),
            'metric.unit.in' => tkey('analytics.validation.invalid_filter'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function metricData(): array
    {
        $data = $this->validated('metric');
        $data['is_active'] = (bool) ($data['is_active'] ?? true);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        $data['metric_group'] = $data['metric_group'] ?? ($data['category'] ?? null);
        $data['unit'] = $data['unit'] ?? ($data['value_type'] ?? null);
        $data['category'] = $data['category'] ?? $data['metric_group'];
        $data['value_type'] = $data['value_type'] ?? $data['unit'];
        $data['calculation'] = $data['calculation'] ?? ($data['calculation_type'] ?? null);

        return $data;
    }
}
