<?php

namespace Database\Factories;

use App\Enums\AnalyticsRunStatus;
use App\Models\ReportDefinition;
use App\Models\ReportRun;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ReportRun>
 */
class ReportRunFactory extends Factory
{
    protected $model = ReportRun::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startedAt = now()->subMinutes($this->faker->numberBetween(1, 60));

        return [
            'uuid' => (string) Str::uuid(),
            'report_definition_id' => ReportDefinition::factory(),
            'status' => AnalyticsRunStatus::Completed,
            'period_start' => now()->startOfMonth()->toDateString(),
            'period_end' => now()->endOfMonth()->toDateString(),
            'started_at' => $startedAt,
            'finished_at' => $startedAt->copy()->addSeconds($this->faker->numberBetween(1, 20)),
            'row_count' => $this->faker->numberBetween(0, 500),
            'filters' => [],
            'summary' => ['records' => 0],
            'result_payload' => [],
            'error_message' => null,
            'created_by_id' => null,
        ];
    }

    public function forDefinition(ReportDefinition $definition): static
    {
        return $this->state(fn (): array => [
            'report_definition_id' => $definition->id,
        ]);
    }

    public function createdBy(User $user): static
    {
        return $this->state(fn (): array => [
            'created_by_id' => $user->id,
        ]);
    }
}
