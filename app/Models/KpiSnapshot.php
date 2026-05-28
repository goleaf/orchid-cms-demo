<?php

namespace App\Models;

use App\Enums\KpiPeriod;
use App\Enums\KpiSnapshotStatus;
use Database\Factories\KpiSnapshotFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class KpiSnapshot extends Model
{
    /** @use HasFactory<KpiSnapshotFactory> */
    use HasFactory;

    protected $fillable = [
        'uuid',
        'kpi_metric_id',
        'branch_id',
        'user_id',
        'period_type',
        'period_start',
        'period_end',
        'value',
        'target_value',
        'status',
        'calculated_at',
        'metadata',
        'period',
        'snapshot_date',
        'training_program_id',
        'training_group_id',
        'source_payload',
    ];

    protected $casts = [
        'period_type' => KpiPeriod::class,
        'period_start' => 'date',
        'period_end' => 'date',
        'period' => KpiPeriod::class,
        'snapshot_date' => 'date',
        'value' => 'decimal:4',
        'target_value' => 'decimal:4',
        'status' => KpiSnapshotStatus::class,
        'metadata' => 'array',
        'source_payload' => 'array',
        'calculated_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $snapshot): void {
            if (blank($snapshot->uuid)) {
                $snapshot->uuid = (string) Str::uuid();
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

    public function periodType(): ?KpiPeriod
    {
        return $this->period_type ?: $this->period;
    }

    public function periodStart(): mixed
    {
        return $this->period_start ?: $this->snapshot_date;
    }

    public function isSuccessful(): bool
    {
        return in_array($this->status, [
            KpiSnapshotStatus::OnTrack,
            KpiSnapshotStatus::Achieved,
            KpiSnapshotStatus::Exceeded,
        ], true);
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

    public function scopeLatestSnapshots(Builder $query): Builder
    {
        return $query->orderByDesc('period_start')->orderByDesc('snapshot_date')->orderByDesc('id');
    }
}
