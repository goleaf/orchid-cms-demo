<?php

namespace App\Actions\Analytics;

use App\Actions\Analytics\Concerns\CreatesReportExports;
use App\Enums\ReportExportFormat;
use App\Models\ReportExport;
use App\Models\ReportRun;
use App\Models\User;
use Illuminate\Support\Arr;

class ExportReportAsCommaSeparatedValuesAction
{
    use CreatesReportExports;

    /**
     * @return array{export: ReportExport, content: string, filename: string, mime_type: string}
     */
    public function handle(ReportRun $run, ?User $user = null): array
    {
        $payload = $this->reportPayload($run);
        $content = $this->buildCsv($payload);

        return $this->createReportExportRecord(
            $run,
            ReportExportFormat::Csv,
            $content,
            'text/csv',
            $user,
            ['exporter' => static::class],
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function buildCsv(array $payload): string
    {
        $handle = fopen('php://temp', 'r+');

        fputcsv($handle, ['section', 'key', 'value']);

        foreach (Arr::dot($payload) as $key => $value) {
            fputcsv($handle, [
                str_contains($key, '.') ? str($key)->before('.')->toString() : 'report',
                $key,
                is_scalar($value) || $value === null ? $value : json_encode($value, JSON_THROW_ON_ERROR),
            ]);
        }

        rewind($handle);
        $content = stream_get_contents($handle) ?: '';
        fclose($handle);

        return $content;
    }
}
