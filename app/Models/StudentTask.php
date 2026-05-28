<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Database\Factories\StudentTaskFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentTask extends Model
{
    /** @use HasFactory<StudentTaskFactory> */
    use HasFactory;

    use HasTranslations;
    use SoftDeletes;

    protected $fillable = [
        'student_id',
        'enrollment_id',
        'title_translations',
        'description_translations',
        'assigned_to_id',
        'created_by_id',
        'priority',
        'status',
        'due_at',
        'completed_at',
        'cancelled_at',
    ];

    protected $casts = [
        'title_translations' => 'array',
        'description_translations' => 'array',
        'due_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(StudentEnrollment::class, 'enrollment_id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereIn('status', ['open', 'in_progress']);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'done');
    }

    public function scopeCancelled(Builder $query): Builder
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query
            ->open()
            ->whereNotNull('due_at')
            ->where('due_at', '<', now());
    }

    public function scopeDueToday(Builder $query): Builder
    {
        return $query
            ->open()
            ->whereBetween('due_at', [now()->startOfDay(), now()->endOfDay()]);
    }

    public function scopeAssignedTo(Builder $query, int|string|null $userId): Builder
    {
        return filled($userId) ? $query->where('assigned_to_id', $userId) : $query;
    }

    public function getIsOverdueAttribute(): bool
    {
        return in_array($this->status, ['open', 'in_progress'], true)
            && $this->due_at !== null
            && $this->due_at->isPast();
    }

    public function getDisplayTitleAttribute(): string
    {
        return $this->getTranslation('title')
            ?: tkey('students.tasks.fallback.title');
    }
}
