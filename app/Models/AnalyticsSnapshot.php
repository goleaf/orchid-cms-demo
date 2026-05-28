<?php

namespace App\Models;

use App\Enums\AnalyticsSnapshotType;
use App\Enums\KpiPeriod;
use Database\Factories\AnalyticsSnapshotFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class AnalyticsSnapshot extends Model
{
    /** @use HasFactory<AnalyticsSnapshotFactory> */
    use HasFactory;

    protected $fillable = [
        'uuid',
        'snapshot_type',
        'period_type',
        'period_start',
        'period_end',
        'branch_id',
        'user_id',
        'data',
        'calculated_at',
        'metadata',
    ];

    protected $casts = [
        'snapshot_type' => AnalyticsSnapshotType::class,
        'period_type' => KpiPeriod::class,
        'period_start' => 'date',
        'period_end' => 'date',
        'data' => 'array',
        'calculated_at' => 'datetime',
        'metadata' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $snapshot): void {
            if (blank($snapshot->uuid)) {
                $snapshot->uuid = (string) Str::uuid();
            }
        });
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeOfType(Builder $query, AnalyticsSnapshotType|string $type): Builder
    {
        $value = $type instanceof AnalyticsSnapshotType ? $type->value : $type;

        return $query->where('snapshot_type', $value);
    }

    public function scopeForPeriodType(Builder $query, KpiPeriod|string $period): Builder
    {
        $value = $period instanceof KpiPeriod ? $period->value : $period;

        return $query->where('period_type', $value);
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

    public function scopeLatestSnapshots(Builder $query): Builder
    {
        return $query->orderByDesc('calculated_at')->orderByDesc('id');
    }

    public function typeValue(): string
    {
        return $this->snapshot_type instanceof AnalyticsSnapshotType
            ? $this->snapshot_type->value
            : (string) $this->snapshot_type;
    }
}
