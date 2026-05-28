<?php

namespace App\Actions\Analytics;

use App\Actions\Analytics\Concerns\CreatesReportExports;
use App\Enums\ReportExportFormat;
use App\Models\ReportExport;
use App\Models\ReportRun;
use App\Models\User;

class ExportReportAsJsonAction
{
    use CreatesReportExports;

    /**
     * @return array{export: ReportExport, content: string, filename: string, mime_type: string}
     */
    public function handle(ReportRun $run, ?User $user = null): array
    {
        $content = json_encode($this->reportPayload($run), JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);

        return $this->createReportExportRecord(
            $run,
            ReportExportFormat::Json,
            $content,
            'application/json',
            $user,
            ['exporter' => static::class],
        );
    }
}
