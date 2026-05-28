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
        'user_id',
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
        'metadata',
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
        'metadata' => 'array',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function isFinished(): bool
    {
        return in_array($this->status, [
            AnalyticsRunStatus::Completed,
            AnalyticsRunStatus::Failed,
            AnalyticsRunStatus::Cancelled,
        ], true);
    }

    public function hasFailed(): bool
    {
        return $this->status === AnalyticsRunStatus::Failed;
    }

    public function durationInSeconds(): ?int
    {
        if (! $this->started_at || ! $this->finished_at) {
            return null;
        }

        return (int) $this->started_at->diffInSeconds($this->finished_at);
    }

    public function scopeForDefinition(Builder $query, ReportDefinition|int $definition): Builder
    {
        $definitionId = $definition instanceof ReportDefinition ? $definition->getKey() : $definition;

        return $query->where('report_definition_id', $definitionId);
    }

    public function scopeForUser(Builder $query, User|int $user): Builder
    {
        $userId = $user instanceof User ? $user->getKey() : $user;

        return $query->where('user_id', $userId);
    }

    public function scopeStatus(Builder $query, AnalyticsRunStatus|string $status): Builder
    {
        $value = $status instanceof AnalyticsRunStatus ? $status->value : $status;

        return $query->where('status', $value);
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
