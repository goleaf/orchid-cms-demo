<?php

namespace Database\Factories;

use App\Enums\AnalyticsRunStatus;
use App\Enums\ReportExportFormat;
use App\Models\ReportDefinition;
use App\Models\ReportExport;
use App\Models\ReportRun;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ReportExport>
 */
class ReportExportFactory extends Factory
{
    protected $model = ReportExport::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'report_definition_id' => ReportDefinition::factory(),
            'report_run_id' => null,
            'format' => ReportExportFormat::Csv,
            'status' => AnalyticsRunStatus::Completed,
            'file_name' => 'report-'.$this->faker->unique()->numerify('####').'.csv',
            'disk' => 'local',
            'path' => null,
            'filename' => 'report-'.$this->faker->unique()->numerify('####').'.csv',
            'mime_type' => 'text/csv',
            'size_bytes' => $this->faker->numberBetween(1000, 100000),
            'row_count' => $this->faker->numberBetween(0, 500),
            'filters' => [],
            'exported_at' => now(),
            'expires_at' => now()->addDays(7),
            'error_message' => null,
            'created_by_id' => null,
            'exported_by_id' => User::factory(),
            'metadata' => [],
        ];
    }

    public function forRun(ReportRun $run): static
    {
        return $this->state(fn (): array => [
            'report_definition_id' => $run->report_definition_id,
            'report_run_id' => $run->id,
            'row_count' => $run->row_count,
            'filters' => $run->filters ?? [],
        ]);
    }

    public function format(ReportExportFormat $format): static
    {
        return $this->state(fn (): array => [
            'format' => $format,
            'file_name' => 'report-'.$this->faker->unique()->numerify('####').'.'.$format->extension(),
            'filename' => 'report-'.$this->faker->unique()->numerify('####').'.'.$format->extension(),
            'mime_type' => $this->mimeTypeFor($format),
        ]);
    }

    public function createdBy(User $user): static
    {
        return $this->state(fn (): array => [
            'created_by_id' => $user->id,
            'exported_by_id' => $user->id,
        ]);
    }

    public function exportedBy(User $user): static
    {
        return $this->state(fn (): array => [
            'exported_by_id' => $user->id,
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
