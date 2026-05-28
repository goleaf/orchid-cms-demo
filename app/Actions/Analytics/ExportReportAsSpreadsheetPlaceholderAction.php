<?php

namespace App\Actions\Analytics;

use App\Actions\Analytics\Concerns\CreatesReportExports;
use App\Enums\ReportExportFormat;
use App\Models\ReportExport;
use App\Models\ReportRun;
use App\Models\User;

class ExportReportAsSpreadsheetPlaceholderAction
{
    use CreatesReportExports;

    /**
     * @return array{export: ReportExport, content: string, filename: string, mime_type: string}
     */
    public function handle(ReportRun $run, ?User $user = null): array
    {
        $content = json_encode([
            'format' => ReportExportFormat::SpreadsheetPlaceholder->value,
            'payload' => $this->reportPayload($run),
        ], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);

        return $this->createReportExportRecord(
            $run,
            ReportExportFormat::SpreadsheetPlaceholder,
            $content,
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            $user,
            ['exporter' => static::class, 'placeholder' => true],
        );
    }
}
