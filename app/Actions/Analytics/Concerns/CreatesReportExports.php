<?php

namespace App\Actions\Analytics\Concerns;

use App\Enums\AnalyticsRunStatus;
use App\Enums\ReportExportFormat;
use App\Models\ReportExport;
use App\Models\ReportRun;
use App\Models\User;

trait CreatesReportExports
{
    use ReadsAnalyticsDataSafely;

    /**
     * @param  array<string, mixed>  $metadata
     * @return array{export: ReportExport, content: string, filename: string, mime_type: string}
     */
    private function createReportExportRecord(
        ReportRun $run,
        ReportExportFormat $format,
        string $content,
        string $mimeType,
        ?User $user,
        array $metadata = [],
    ): array {
        $this->authorizeAnalyticsAccess($user, 'analytics.reports.export');

        $run->loadMissing('definition');

        $filename = ($run->definition?->code ?: 'report').'-'.now()->format('Ymd-His').'.'.$format->extension();
        $export = ReportExport::query()->create($this->analyticsExistingAttributes(ReportExport::class, [
            'report_definition_id' => $run->report_definition_id,
            'report_run_id' => $run->id,
            'format' => $format,
            'status' => AnalyticsRunStatus::Completed,
            'file_name' => $filename,
            'disk' => 'local',
            'path' => null,
            'filename' => $filename,
            'mime_type' => $mimeType,
            'size_bytes' => strlen($content),
            'row_count' => $run->row_count ?? 0,
            'filters' => $run->filters ?? [],
            'exported_at' => now(),
            'expires_at' => now()->addDays(7),
            'created_by_id' => $user?->id,
            'exported_by_id' => $user?->id,
            'metadata' => $metadata,
        ]));

        return [
            'export' => $export,
            'content' => $content,
            'filename' => $filename,
            'mime_type' => $mimeType,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function reportPayload(ReportRun $run): array
    {
        $run->loadMissing('definition');

        return [
            'report' => [
                'id' => $run->report_definition_id,
                'code' => $run->definition?->code,
                'name' => $run->definition?->displayName(),
            ],
            'run' => [
                'id' => $run->id,
                'uuid' => $run->uuid,
                'status' => $this->analyticsEnumValue($run->status),
                'row_count' => $run->row_count,
                'started_at' => $run->started_at?->toISOString(),
                'finished_at' => $run->finished_at?->toISOString(),
            ],
            'filters' => $run->filters ?? [],
            'summary' => $run->summary ?? data_get($run->result_payload ?? [], 'summary', []),
        ];
    }
}
