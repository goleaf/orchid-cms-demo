<?php

namespace App\Models;

use App\Enums\ReviewStatus;
use Database\Factories\StudentReviewFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentReview extends Model
{
    /** @use HasFactory<StudentReviewFactory> */
    use HasFactory;

    protected $fillable = [
        'student_profile_id',
        'training_program_id',
        'training_group_id',
        'instructor_id',
        'author_name',
        'rating',
        'title',
        'body',
        'video_url',
        'admin_reply',
        'status',
        'published_at',
    ];

    protected $casts = [
        'rating' => 'integer',
        'status' => ReviewStatus::class,
        'published_at' => 'datetime',
    ];

    public function studentProfile(): BelongsTo
    {
        return $this->belongsTo(StudentProfile::class);
    }

    public function trainingProgram(): BelongsTo
    {
        return $this->belongsTo(TrainingProgram::class);
    }

    public function trainingGroup(): BelongsTo
    {
        return $this->belongsTo(TrainingGroup::class);
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', ReviewStatus::Published->value)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function scopeForPublicList(Builder $query): Builder
    {
        return $query->select([
            'id',
            'student_profile_id',
            'training_program_id',
            'training_group_id',
            'instructor_id',
            'author_name',
            'rating',
            'title',
            'body',
            'video_url',
            'admin_reply',
            'published_at',
        ]);
    }
}
