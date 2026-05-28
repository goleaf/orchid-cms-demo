<?php

namespace App\Models;

use App\Enums\AnalyticsRunStatus;
use Database\Factories\ReportRunFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ReportRun extends Model
{
    /** @use HasFactory<ReportRunFactory> */
    use HasFactory;

    protected $fillable = [
        'uuid',
        'report_definition_id',
        'status',
        'period_start',
        'period_end',
        'started_at',
        'finished_at',
        'row_count',
        'filters',
        'summary',
        'result_payload',
        'error_message',
        'created_by_id',
    ];

    protected $casts = [
        'status' => AnalyticsRunStatus::class,
        'period_start' => 'date',
        'period_end' => 'date',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'row_count' => 'integer',
        'filters' => 'array',
        'summary' => 'array',
        'result_payload' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $run): void {
            if (blank($run->uuid)) {
                $run->uuid = (string) Str::uuid();
            }
        });
    }

    public function definition(): BelongsTo
    {
        return $this->belongsTo(ReportDefinition::class, 'report_definition_id');
    }

    public function exports(): HasMany
    {
        return $this->hasMany(ReportExport::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', AnalyticsRunStatus::Completed->value);
    }

    public function scopeLatestRuns(Builder $query): Builder
    {
        return $query->orderByDesc('started_at')->orderByDesc('id');
    }
}
