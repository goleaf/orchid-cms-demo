<?php

namespace App\Models;

use Database\Factories\StudentCommunicationFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentCommunication extends Model
{
    /** @use HasFactory<StudentCommunicationFactory> */
    use HasFactory;

    protected $fillable = [
        'student_profile_id',
        'student_enrollment_id',
        'marketing_lead_id',
        'user_id',
        'notification_channel_id',
        'communication_template_id',
        'communication_reminder_id',
        'channel',
        'direction',
        'subject',
        'body',
        'communicated_at',
        'client_replied_at',
        'callback_required_at',
        'metadata',
    ];

    protected $casts = [
        'communicated_at' => 'datetime',
        'client_replied_at' => 'datetime',
        'callback_required_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_profile_id');
    }

    public function studentProfile(): BelongsTo
    {
        return $this->belongsTo(StudentProfile::class);
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(StudentEnrollment::class, 'student_enrollment_id');
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class, 'marketing_lead_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function notificationChannel(): BelongsTo
    {
        return $this->belongsTo(NotificationChannel::class);
    }

    public function communicationTemplate(): BelongsTo
    {
        return $this->belongsTo(CommunicationTemplate::class);
    }

    public function communicationReminder(): BelongsTo
    {
        return $this->belongsTo(CommunicationReminder::class);
    }

    public function scopeForStudentHistory(Builder $query, Student|StudentProfile|int $student): Builder
    {
        $studentId = $student instanceof StudentProfile ? $student->getKey() : $student;

        return $query->where('student_profile_id', $studentId)
            ->latest('communicated_at');
    }

    public function channelLabel(): string
    {
        return $this->notificationChannel?->displayName()
            ?: tkey('communication.channels.'.$this->channel);
    }
}
