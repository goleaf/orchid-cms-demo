<?php

namespace App\Models;

use App\Enums\KpiDirection;
use App\Enums\KpiPeriod;
use Database\Factories\KpiTargetFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KpiTarget extends Model
{
    /** @use HasFactory<KpiTargetFactory> */
    use HasFactory;

    protected $fillable = [
        'kpi_metric_id',
        'period',
        'starts_on',
        'ends_on',
        'target_value',
        'warning_value',
        'direction',
        'branch_id',
        'training_program_id',
        'training_group_id',
        'assigned_to_user_id',
        'metadata',
        'created_by_id',
        'updated_by_id',
    ];

    protected $casts = [
        'period' => KpiPeriod::class,
        'starts_on' => 'date',
        'ends_on' => 'date',
        'target_value' => 'decimal:4',
        'warning_value' => 'decimal:4',
        'direction' => KpiDirection::class,
        'metadata' => 'array',
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

    public function scopeCurrentForMetric(Builder $query, int $metricId): Builder
    {
        return $query
            ->where('kpi_metric_id', $metricId)
            ->where('starts_on', '<=', now()->toDateString())
            ->where(function (Builder $query): void {
                $query->whereNull('ends_on')
                    ->orWhere('ends_on', '>=', now()->toDateString());
            })
            ->orderByDesc('starts_on');
    }
}
