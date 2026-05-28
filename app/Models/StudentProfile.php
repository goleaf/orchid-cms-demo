<?php

namespace App\Models;

use App\Enums\EnrollmentStatus;
use App\Enums\StudentStatus;
use App\Support\Crm\PhoneNormalizer;
use Database\Factories\StudentProfileFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class StudentProfile extends Model
{
    /** @use HasFactory<StudentProfileFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'student_number',
        'user_id',
        'branch_id',
        'full_name',
        'first_name',
        'last_name',
        'middle_name',
        'email',
        'phone',
        'normalized_phone',
        'date_of_birth',
        'national_id',
        'personal_code',
        'gender',
        'preferred_messenger',
        'telegram_username',
        'whatsapp_phone',
        'emergency_contact_name',
        'emergency_contact_phone',
        'address',
        'city',
        'locale',
        'source',
        'status',
        'status_id',
        'manager_id',
        'administrator_id',
        'source_lead_id',
        'source_id',
        'source_label',
        'consent_accepted',
        'consent_accepted_at',
        'consent_text_version',
        'notes',
        'comment',
        'internal_comment',
        'portal_access_created_at',
        'documents_summary',
        'payment_summary',
        'created_by_id',
        'updated_by_id',
        'registered_at',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'registered_at' => 'datetime',
        'status' => StudentStatus::class,
        'consent_accepted' => 'boolean',
        'consent_accepted_at' => 'datetime',
        'portal_access_created_at' => 'datetime',
        'documents_summary' => 'array',
        'payment_summary' => 'array',
        'deleted_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $student): void {
            if (blank($student->uuid)) {
                $student->uuid = (string) Str::uuid();
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

    public function status(): BelongsTo
    {
        return $this->belongsTo(\App\Models\StudentStatus::class, 'status_id');
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function administrator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'administrator_id');
    }

    public function sourceLead(): BelongsTo
    {
        return $this->belongsTo(Lead::class, 'source_lead_id');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(StudentEnrollment::class, 'student_profile_id');
    }

    public function groupMemberships(): HasMany
    {
        return $this->hasMany(TrainingGroupMembership::class, 'student_profile_id');
    }

    public function trainingGroups(): BelongsToMany
    {
        return $this->belongsToMany(TrainingGroup::class, 'training_group_memberships', 'student_profile_id', 'training_group_id')
            ->withPivot(['enrollment_id', 'student_enrollment_id', 'status', 'joined_at', 'left_at'])
            ->withTimestamps();
    }

    public function currentEnrollment(): HasOne
    {
        return $this->hasOne(StudentEnrollment::class, 'student_profile_id')
            ->whereIn('enrollments.status', [
                EnrollmentStatus::Pending->value,
                EnrollmentStatus::WaitingDocuments->value,
                EnrollmentStatus::WaitingPayment->value,
                EnrollmentStatus::WaitingStart->value,
                EnrollmentStatus::Active->value,
                EnrollmentStatus::Theory->value,
                EnrollmentStatus::Practice->value,
                EnrollmentStatus::ReadyInternalExam->value,
                EnrollmentStatus::ReadyStateExam->value,
                EnrollmentStatus::Paused->value,
            ])
            ->latestOfMany();
    }

    public function activeEnrollments(): HasMany
    {
        return $this->hasMany(StudentEnrollment::class, 'student_profile_id')
            ->whereIn('status', [
                EnrollmentStatus::Pending->value,
                EnrollmentStatus::WaitingDocuments->value,
                EnrollmentStatus::WaitingPayment->value,
                EnrollmentStatus::WaitingStart->value,
                EnrollmentStatus::Active->value,
                EnrollmentStatus::Theory->value,
                EnrollmentStatus::Practice->value,
                EnrollmentStatus::ReadyInternalExam->value,
                EnrollmentStatus::ReadyStateExam->value,
                EnrollmentStatus::Paused->value,
            ]);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(StudentActivity::class, 'student_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(StudentTask::class, 'student_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(StudentDocument::class);
    }

    public function examAdmissions(): HasMany
    {
        return $this->hasMany(ExamAdmission::class);
    }

    public function examAttempts(): HasMany
    {
        return $this->hasMany(ExamAttempt::class);
    }

    public function fullName(): string
    {
        $name = collect([
            $this->first_name,
            $this->middle_name,
            $this->last_name,
        ])
            ->filter(fn (?string $part): bool => filled($part))
            ->implode(' ');

        return $name ?: ((string) ($this->attributes['full_name'] ?? ''));
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->fullName() ?: tkey('students.fallback.student');
    }

    public function getDisplayContactAttribute(): string
    {
        return collect([$this->phone, $this->email])
            ->filter(fn (?string $value): bool => filled($value))
            ->implode(' / ') ?: '-';
    }

    public function getIsArchivedAttribute(): bool
    {
        return $this->status === StudentStatus::Archived;
    }

    public function getIsBlockedAttribute(): bool
    {
        return $this->status === StudentStatus::Blocked;
    }

    public function getHasPortalAccessAttribute(): bool
    {
        return $this->portal_access_created_at !== null;
    }

    public function getCurrentEnrollmentAttribute(): ?StudentEnrollment
    {
        if ($this->relationLoaded('currentEnrollment')) {
            return $this->relations['currentEnrollment'];
        }

        return $this->currentEnrollment()->first();
    }

    public function getPersonalCodeAttribute(?string $value): ?string
    {
        return $value ?: ($this->attributes['national_id'] ?? null);
    }

    public function setPersonalCodeAttribute(?string $value): void
    {
        $this->attributes['personal_code'] = $value;

        if (blank($this->attributes['national_id'] ?? null)) {
            $this->attributes['national_id'] = $value;
        }
    }

    public function setPhoneAttribute(?string $value): void
    {
        $this->attributes['phone'] = $value;
        $this->attributes['normalized_phone'] = PhoneNormalizer::normalize($value);
    }

    public function scopeForCrmList(Builder $query): Builder
    {
        return $query->select([
            'id',
            'uuid',
            'student_number',
            'branch_id',
            'full_name',
            'first_name',
            'middle_name',
            'last_name',
            'email',
            'phone',
            'normalized_phone',
            'status',
            'status_id',
            'manager_id',
            'source_lead_id',
            'source',
            'registered_at',
            'created_at',
            'updated_at',
        ]);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', [
            StudentStatus::Active->value,
            StudentStatus::Lead->value,
            StudentStatus::Enrolled->value,
        ]);
    }

    public function scopeArchived(Builder $query): Builder
    {
        return $query->where('status', StudentStatus::Archived->value);
    }

    public function scopeBlocked(Builder $query): Builder
    {
        return $query->where('status', StudentStatus::Blocked->value);
    }

    public function scopeByStatus(Builder $query, StudentStatus|string|null $status): Builder
    {
        return filled($status)
            ? $query->where('status', $status instanceof StudentStatus ? $status->value : $status)
            : $query;
    }

    public function scopeByManager(Builder $query, int|string|null $managerId): Builder
    {
        return filled($managerId) ? $query->where('manager_id', $managerId) : $query;
    }

    public function scopeWithActiveEnrollment(Builder $query): Builder
    {
        return $query->whereHas('activeEnrollments');
    }

    public function scopeWithoutActiveEnrollment(Builder $query): Builder
    {
        return $query->whereDoesntHave('activeEnrollments');
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (blank($search)) {
            return $query;
        }

        $search = (string) $search;
        $phoneToken = PhoneNormalizer::searchToken($search);

        return $query->where(function (Builder $query) use ($search, $phoneToken): void {
            $query
                ->where('uuid', 'like', '%'.$search.'%')
                ->orWhere('student_number', 'like', '%'.$search.'%')
                ->orWhere('full_name', 'like', '%'.$search.'%')
                ->orWhere('first_name', 'like', '%'.$search.'%')
                ->orWhere('middle_name', 'like', '%'.$search.'%')
                ->orWhere('last_name', 'like', '%'.$search.'%')
                ->orWhere('phone', 'like', '%'.$search.'%')
                ->orWhere('email', 'like', '%'.$search.'%')
                ->orWhere('personal_code', 'like', '%'.$search.'%')
                ->orWhere('national_id', 'like', '%'.$search.'%');

            if (is_numeric($search)) {
                $query->orWhere('id', (int) $search);
            }

            if (filled($phoneToken)) {
                $query->orWhere('normalized_phone', 'like', '%'.$phoneToken.'%');
            }
        });
    }
}
