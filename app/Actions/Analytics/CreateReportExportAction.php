<?php

namespace App\Actions\Analytics;

use App\Enums\AnalyticsRunStatus;
use App\Enums\ReportExportFormat;
use App\Models\ReportDefinition;
use App\Models\ReportExport;
use App\Models\ReportRun;
use App\Models\User;

class CreateReportExportAction
{
    public function handle(
        ReportDefinition $definition,
        ReportExportFormat|string $format = ReportExportFormat::Csv,
        ?ReportRun $run = null,
        ?User $user = null,
    ): ReportExport {
        $format = $format instanceof ReportExportFormat ? $format : ReportExportFormat::from($format);
        $run ??= $definition->runs()->latestRuns()->first();
        $filename = $definition->code.'-'.now()->format('Ymd-His').'.'.$format->extension();

        return ReportExport::query()->create([
            'report_definition_id' => $definition->id,
            'report_run_id' => $run?->id,
            'format' => $format,
            'status' => AnalyticsRunStatus::Completed,
            'file_name' => $filename,
            'disk' => 'local',
            'path' => null,
            'filename' => $filename,
            'mime_type' => $this->mimeTypeFor($format),
            'size_bytes' => null,
            'row_count' => $run?->row_count ?? 0,
            'filters' => $run?->filters ?? [],
            'exported_at' => now(),
            'expires_at' => now()->addDays(7),
            'created_by_id' => $user?->id,
            'exported_by_id' => $user?->id,
            'metadata' => ['data_source' => $definition->dataSource()],
        ]);
    }

    private function mimeTypeFor(ReportExportFormat $format): string
    {
        return match ($format) {
            ReportExportFormat::Csv, ReportExportFormat::LegacyCsv => 'text/csv',
            ReportExportFormat::SpreadsheetPlaceholder => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ReportExportFormat::Json => 'application/json',
        };
    }
}
