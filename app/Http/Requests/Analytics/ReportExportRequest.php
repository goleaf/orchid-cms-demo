<?php

namespace App\Http\Requests\Analytics;

use App\Enums\ReportExportFormat;
use App\Http\Requests\Analytics\Concerns\UsesAnalyticsRequestValidation;
use App\Models\ReportDefinition;
use App\Models\ReportRun;
use App\Rules\ActiveReportDefinitionRule;
use App\Rules\AnalyticsPermissionRule;
use App\Rules\ReportExportAllowedRule;
use App\Rules\ValidReportFormatRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReportExportRequest extends FormRequest
{
    use UsesAnalyticsRequestValidation;

    public function authorize(): bool
    {
        return $this->analyticsAccess('analytics.reports.export');
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $run = $this->reportRunForValidation();
        $definition = $this->reportDefinitionForValidation($run);

        return [
            'report_definition_id' => [
                'required',
                'integer',
                Rule::exists(ReportDefinition::class, 'id'),
                new ActiveReportDefinitionRule,
                new AnalyticsPermissionRule($this->user(), 'analytics.reports.export'),
            ],
            'report_run_id' => ['nullable', 'integer', Rule::exists(ReportRun::class, 'id')],
            'format' => [
                'bail',
                'required',
                'string',
                new ValidReportFormatRule,
                new ReportExportAllowedRule($run, $definition, $this->user()),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return $this->analyticsValidationMessages();
    }

    public function reportDefinitionId(): int
    {
        return (int) $this->validated('report_definition_id');
    }

    public function reportRunId(): ?int
    {
        $id = $this->validated('report_run_id', null);

        return filled($id) ? (int) $id : null;
    }

    public function exportFormat(): ReportExportFormat
    {
        return ReportExportFormat::from($this->validated('format'));
    }

    private function reportRunForValidation(): ?ReportRun
    {
        $id = $this->input('report_run_id');

        if (! filled($id)) {
            return null;
        }

        return ReportRun::query()->with('definition')->find($id);
    }

    private function reportDefinitionForValidation(?ReportRun $run): ?ReportDefinition
    {
        if ($run instanceof ReportRun && $run->definition instanceof ReportDefinition) {
            return $run->definition;
        }

        $id = $this->input('report_definition_id');

        if (! filled($id)) {
            return null;
        }

        return ReportDefinition::query()->withoutGlobalScopes()->find($id);
    }
}
