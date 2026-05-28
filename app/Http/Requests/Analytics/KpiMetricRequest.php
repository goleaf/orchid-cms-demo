<?php

namespace App\Http\Requests\Analytics;

use App\Enums\KpiMetricGroup;
use App\Enums\KpiUnit;
use App\Models\KpiMetric;
use App\Rules\AnalyticsCodeRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class KpiMetricRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAccess('analytics.kpis.manage') ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'metric.id' => ['nullable', 'integer', Rule::exists(KpiMetric::class, 'id')],
            'metric.code' => ['required', 'string', 'max:120', new AnalyticsCodeRule],
            'metric.name_translations' => ['required', 'array'],
            'metric.description_translations' => ['nullable', 'array'],
            'metric.metric_group' => ['required_without:metric.category', Rule::enum(KpiMetricGroup::class)],
            'metric.unit' => ['required_without:metric.value_type', Rule::enum(KpiUnit::class)],
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
        return [
            'metric.code.required' => tkey('analytics.validation.code_required'),
            'metric.name_translations.required' => tkey('analytics.validation.name_required'),
        ];
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
