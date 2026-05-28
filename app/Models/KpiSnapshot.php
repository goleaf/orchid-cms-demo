<?php

namespace App\Models;

use App\Enums\KpiPeriod;
use App\Enums\KpiSnapshotStatus;
use Database\Factories\KpiSnapshotFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KpiSnapshot extends Model
{
    /** @use HasFactory<KpiSnapshotFactory> */
    use HasFactory;

    protected $fillable = [
        'kpi_metric_id',
        'period',
        'snapshot_date',
        'value',
        'target_value',
        'status',
        'branch_id',
        'training_program_id',
        'training_group_id',
        'source_payload',
        'calculated_at',
    ];

    protected $casts = [
        'period' => KpiPeriod::class,
        'snapshot_date' => 'date',
        'value' => 'decimal:4',
        'target_value' => 'decimal:4',
        'status' => KpiSnapshotStatus::class,
        'source_payload' => 'array',
        'calculated_at' => 'datetime',
    ];

    public function metric(): BelongsTo
    {
        return $this->belongsTo(KpiMetric::class, 'kpi_metric_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function trainingProgram(): BelongsTo
    {
        return $this->belongsTo(TrainingProgram::class);
    }

    public function trainingGroup(): BelongsTo
    {
        return $this->belongsTo(TrainingGroup::class);
    }

    public function scopeLatestSnapshots(Builder $query): Builder
    {
        return $query->orderByDesc('snapshot_date')->orderByDesc('id');
    }
}
