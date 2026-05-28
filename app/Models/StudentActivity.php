<?php

namespace App\Models;

use Database\Factories\StudentActivityFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentActivity extends Model
{
    /** @use HasFactory<StudentActivityFactory> */
    use HasFactory;

    protected $fillable = [
        'student_id',
        'enrollment_id',
        'lead_id',
        'user_id',
        'type',
        'title',
        'body',
        'old_value',
        'new_value',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(StudentEnrollment::class, 'enrollment_id');
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class, 'lead_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
