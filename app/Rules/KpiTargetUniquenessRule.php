<?php

namespace App\Rules;

use App\Models\KpiTarget;
use App\Rules\Concerns\InteractsWithAnalyticsValidation;
use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;
use Throwable;

class KpiTargetUniquenessRule implements DataAwareRule, ValidationRule
{
    use InteractsWithAnalyticsValidation;

    /**
     * @var array<string, mixed>
     */
    private array $data = [];

    public function __construct(
        private readonly ?int $ignoreId = null,
        private readonly mixed $metricId = null,
        private readonly ?string $period = null,
        private readonly mixed $periodStart = null,
        private readonly mixed $periodEnd = null,
        private readonly mixed $branchId = null,
        private readonly mixed $userId = null,
    ) {}

    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! Schema::hasTable('kpi_targets')) {
            return;
        }

        $metricId = $this->metricId ?? $value ?? data_get($this->data, 'target.kpi_metric_id');
        $period = $this->period ?? data_get($this->data, 'target.period_type', data_get($this->data, 'target.period'));
        $periodStart = $this->date($this->periodStart ?? data_get($this->data, 'target.period_start', data_get($this->data, 'target.starts_on')));

        if (! filled($metricId) || ! filled($period) || $periodStart === null) {
            return;
        }

        $query = KpiTarget::query()
            ->withoutGlobalScopes()
            ->where('kpi_metric_id', $metricId)
            ->when($this->ignoreId !== null, fn (Builder $query): Builder => $query->whereKeyNot($this->ignoreId));

        $this->wherePeriod($query, (string) $period, $periodStart);
        $this->whereNullable($query, 'branch_id', $this->branchId ?? data_get($this->data, 'target.branch_id'));
        $this->whereNullable($query, $this->userColumn(), $this->userId ?? data_get($this->data, 'target.user_id', data_get($this->data, 'target.assigned_to_user_id')));
        $this->whereNullable($query, $this->periodEndColumn(), $this->date($this->periodEnd ?? data_get($this->data, 'target.period_end', data_get($this->data, 'target.ends_on'))));

        if (Schema::hasColumn('kpi_targets', 'deleted_at')) {
            $query->whereNull('deleted_at');
        }

        if ($query->exists()) {
            $fail(tkey('analytics.validation.duplicate_kpi_target'));
        }
    }

    private function wherePeriod(Builder $query, string $period, string $periodStart): void
    {
        $periodColumn = Schema::hasColumn('kpi_targets', 'period_type') ? 'period_type' : 'period';
        $startColumn = Schema::hasColumn('kpi_targets', 'period_start') ? 'period_start' : 'starts_on';

        $query
            ->where($periodColumn, $period)
            ->whereDate($startColumn, $periodStart);
    }

    private function whereNullable(Builder $query, ?string $column, mixed $value): void
    {
        if ($column === null || ! Schema::hasColumn('kpi_targets', $column)) {
            return;
        }

        if (filled($value)) {
            if (in_array($column, ['period_end', 'ends_on'], true)) {
                $query->whereDate($column, (string) $value);

                return;
            }

            $query->where($column, $value);

            return;
        }

        $query->whereNull($column);
    }

    private function userColumn(): ?string
    {
        if (Schema::hasColumn('kpi_targets', 'user_id')) {
            return 'user_id';
        }

        if (Schema::hasColumn('kpi_targets', 'assigned_to_user_id')) {
            return 'assigned_to_user_id';
        }

        return null;
    }

    private function periodEndColumn(): ?string
    {
        if (Schema::hasColumn('kpi_targets', 'period_end')) {
            return 'period_end';
        }

        if (Schema::hasColumn('kpi_targets', 'ends_on')) {
            return 'ends_on';
        }

        return null;
    }

    private function date(mixed $value): ?string
    {
        if (! filled($value)) {
            return null;
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (Throwable) {
            return null;
        }
    }
}
