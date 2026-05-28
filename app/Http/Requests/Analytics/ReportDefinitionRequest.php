<?php

namespace App\Http\Requests\Analytics;

use App\Enums\AnalyticsReportType;
use App\Models\ReportDefinition;
use App\Rules\AnalyticsCodeRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReportDefinitionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAccess('analytics.reports.manage') ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'report.id' => ['nullable', 'integer', Rule::exists(ReportDefinition::class, 'id')],
            'report.code' => ['required', 'string', 'max:120', new AnalyticsCodeRule],
            'report.name_translations' => ['required', 'array'],
            'report.description_translations' => ['nullable', 'array'],
            'report.report_type' => ['required', Rule::enum(AnalyticsReportType::class)],
            'report.source_model' => ['nullable', 'string', 'max:255'],
            'report.default_filters' => ['nullable', 'array'],
            'report.column_config' => ['nullable', 'array'],
            'report.schedule' => ['nullable', 'array'],
            'report.is_active' => ['nullable', 'boolean'],
            'report.sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'report.code.required' => tkey('analytics.validation.code_required'),
            'report.name_translations.required' => tkey('analytics.validation.name_required'),
            'report.report_type.required' => tkey('analytics.validation.report_type_required'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function reportData(): array
    {
        $data = $this->validated('report');
        $data['is_active'] = (bool) ($data['is_active'] ?? true);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        return $data;
    }
}
