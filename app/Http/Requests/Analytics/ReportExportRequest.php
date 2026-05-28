<?php

namespace App\Http\Requests\Analytics;

use App\Enums\ReportExportFormat;
use App\Models\ReportDefinition;
use App\Models\ReportRun;
use App\Rules\ActiveReportDefinitionRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReportExportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAccess('analytics.reports.export') ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'report_definition_id' => ['required', 'integer', Rule::exists(ReportDefinition::class, 'id'), new ActiveReportDefinitionRule],
            'report_run_id' => ['nullable', 'integer', Rule::exists(ReportRun::class, 'id')],
            'format' => ['required', Rule::enum(ReportExportFormat::class)],
        ];
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

    public function format(): ReportExportFormat
    {
        return ReportExportFormat::from($this->validated('format'));
    }
}
