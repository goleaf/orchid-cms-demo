<?php

namespace App\Models;

use Database\Factories\ExamResultFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamResult extends Model
{
    /** @use HasFactory<ExamResultFactory> */
    use HasFactory;

    protected $fillable = [
        'attempt_id',
        'result_status_id',
        'score',
        'max_score',
        'passed',
        'examiner_comment',
        'mistakes_summary',
        'decided_by_id',
        'decided_at',
    ];

    protected $casts = [
        'score' => 'decimal:2',
        'max_score' => 'decimal:2',
        'passed' => 'boolean',
        'decided_at' => 'datetime',
    ];

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(ExamAttempt::class, 'attempt_id');
    }

    public function resultStatus(): BelongsTo
    {
        return $this->belongsTo(ExamResultStatus::class, 'result_status_id');
    }

    public function decidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by_id');
    }

    public function scopePassed(Builder $query): Builder
    {
        return $query->where('passed', true);
    }

    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('passed', false)
            ->whereHas('resultStatus', fn (Builder $status): Builder => $status->whereIn('code', ['failed', 'needs_retake']));
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->whereHas('resultStatus', fn (Builder $status): Builder => $status->where('code', 'pending'));
    }

    public function displayStatus(?string $locale = null): string
    {
        return $this->resultStatus?->displayName($locale) ?: '-';
    }

    public function scorePercent(): ?float
    {
        if ($this->score === null || $this->max_score === null || (float) $this->max_score <= 0.0) {
            return null;
        }

        return round(((float) $this->score / (float) $this->max_score) * 100, 2);
    }
}
