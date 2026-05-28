<?php

namespace App\Models;

use App\Enums\KpiDirection;
use App\Enums\KpiPeriod;
use Database\Factories\KpiTargetFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class KpiTarget extends Model
{
    /** @use HasFactory<KpiTargetFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'kpi_metric_id',
        'branch_id',
        'user_id',
        'period_type',
        'period_start',
        'period_end',
        'target_value',
        'warning_threshold',
        'success_threshold',
        'period',
        'starts_on',
        'ends_on',
        'warning_value',
        'direction',
        'training_program_id',
        'training_group_id',
        'assigned_to_user_id',
        'metadata',
        'created_by_id',
        'updated_by_id',
    ];

    protected $casts = [
        'period_type' => KpiPeriod::class,
        'period_start' => 'date',
        'period_end' => 'date',
        'period' => KpiPeriod::class,
        'starts_on' => 'date',
        'ends_on' => 'date',
        'target_value' => 'decimal:4',
        'warning_threshold' => 'decimal:4',
        'success_threshold' => 'decimal:4',
        'warning_value' => 'decimal:4',
        'direction' => KpiDirection::class,
        'metadata' => 'array',
        'deleted_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $target): void {
            if (blank($target->uuid)) {
                $target->uuid = (string) Str::uuid();
            }
        });
    }

    public function metric(): BelongsTo
    {
        return $this->belongsTo(KpiMetric::class, 'kpi_metric_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function trainingProgram(): BelongsTo
    {
        return $this->belongsTo(TrainingProgram::class);
    }

    public function trainingGroup(): BelongsTo
    {
        return $this->belongsTo(TrainingGroup::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_id');
    }

    public function periodType(): ?KpiPeriod
    {
        return $this->period_type ?: $this->period;
    }

    public function periodStart(): mixed
    {
        return $this->period_start ?: $this->starts_on;
    }

    public function periodEnd(): mixed
    {
        return $this->period_end ?: $this->ends_on;
    }

    public function warningThreshold(): ?string
    {
        return $this->warning_threshold ?? $this->warning_value;
    }

    public function successThreshold(): ?string
    {
        return $this->success_threshold;
    }

    public function scopeForMetric(Builder $query, KpiMetric|int $metric): Builder
    {
        $metricId = $metric instanceof KpiMetric ? $metric->getKey() : $metric;

        return $query->where('kpi_metric_id', $metricId);
    }

    public function scopeForBranch(Builder $query, Branch|int $branch): Builder
    {
        $branchId = $branch instanceof Branch ? $branch->getKey() : $branch;

        return $query->where('branch_id', $branchId);
    }

    public function scopeForUser(Builder $query, User|int $user): Builder
    {
        $userId = $user instanceof User ? $user->getKey() : $user;

        return $query->where('user_id', $userId);
    }

    public function scopeForPeriodType(Builder $query, KpiPeriod|string $period): Builder
    {
        $value = $period instanceof KpiPeriod ? $period->value : $period;

        return $query->where('period_type', $value);
    }

    public function scopeCurrentForMetric(Builder $query, int $metricId): Builder
    {
        return $query
            ->where('kpi_metric_id', $metricId)
            ->where(function (Builder $query): void {
                $query->where('period_start', '<=', now()->toDateString())
                    ->orWhere('starts_on', '<=', now()->toDateString());
            })
            ->where(function (Builder $query): void {
                $query->whereNull('period_end')
                    ->orWhere('period_end', '>=', now()->toDateString())
                    ->orWhereNull('ends_on')
                    ->orWhere('ends_on', '>=', now()->toDateString());
            })
            ->orderByDesc('period_start')
            ->orderByDesc('starts_on');
    }
}
