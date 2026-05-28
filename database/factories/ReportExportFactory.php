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
            'row_count' => $this->faker->numberBetween(0, 500),
            'filters' => [],
            'exported_at' => now(),
            'expires_at' => now()->addDays(7),
            'error_message' => null,
            'created_by_id' => null,
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
            'file_name' => 'report-'.$this->faker->unique()->numerify('####').'.'.$format->value,
        ]);
    }

    public function createdBy(User $user): static
    {
        return $this->state(fn (): array => [
            'created_by_id' => $user->id,
        ]);
    }
}
