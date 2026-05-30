<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Schema;
use Orchid\Filters\Types\Like;
use Orchid\Filters\Types\Where;
use Orchid\Filters\Types\WhereDateStartEnd;
use Orchid\Platform\Models\User as Authenticatable;
use Throwable;

class User extends Authenticatable
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'status_id',
        'preferred_locale',
        'timezone',
        'is_active',
        'security_locked_at',
        'security_lock_reason',
        'last_login_at',
        'last_seen_at',
        'password_changed_at',
        'must_change_password',
        'two_factor_placeholder_enabled',
    ];

    protected $attributes = [
        'is_active' => true,
        'must_change_password' => false,
        'two_factor_placeholder_enabled' => false,
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'permissions',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'permissions' => 'array',
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
        'security_locked_at' => 'datetime',
        'last_login_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'password_changed_at' => 'datetime',
        'must_change_password' => 'boolean',
        'two_factor_placeholder_enabled' => 'boolean',
    ];

    /**
     * The attributes for which you can use filters in url.
     *
     * @var array
     */
    protected $allowedFilters = [
        'id' => Where::class,
        'name' => Like::class,
        'email' => Like::class,
        'updated_at' => WhereDateStartEnd::class,
        'created_at' => WhereDateStartEnd::class,
    ];

    /**
     * The attributes for which can use sort in url.
     *
     * @var array
     */
    protected $allowedSorts = [
        'id',
        'name',
        'email',
        'updated_at',
        'created_at',
    ];

    public function notificationPreferences(): HasMany
    {
        return $this->hasMany(UserNotificationPreference::class);
    }

    public function notificationPreferenceRecords(): HasMany
    {
        return $this->hasMany(NotificationPreference::class);
    }

    public function createdNotificationMessages(): HasMany
    {
        return $this->hasMany(NotificationMessage::class, 'created_by_id');
    }

    public function notificationRecipients(): HasMany
    {
        return $this->hasMany(NotificationRecipient::class);
    }

    public function communicationMessages(): HasMany
    {
        return $this->hasMany(CommunicationMessage::class);
    }

    public function notificationActivities(): HasMany
    {
        return $this->hasMany(NotificationActivity::class);
    }

    public function dashboardPreferences(): HasMany
    {
        return $this->hasMany(UserDashboardPreference::class);
    }

    public function reportRuns(): HasMany
    {
        return $this->hasMany(ReportRun::class, 'created_by_id');
    }

    public function reportExports(): HasMany
    {
        return $this->hasMany(ReportExport::class, 'created_by_id');
    }

    public function communicationReminders(): HasMany
    {
        return $this->hasMany(CommunicationReminder::class, 'assigned_to_user_id');
    }

    public function notificationDeliveryLogs(): HasMany
    {
        return $this->hasMany(NotificationDeliveryLog::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function securityEvents(): HasMany
    {
        return $this->hasMany(SecurityEvent::class);
    }

    public function loginAttempts(): HasMany
    {
        return $this->hasMany(LoginAttempt::class);
    }

    public function securitySessions(): HasMany
    {
        return $this->hasMany(UserSecuritySession::class);
    }

    public function legacySessions(): HasMany
    {
        return $this->hasMany(UserSession::class);
    }

    public function activeSecuritySessions(): HasMany
    {
        return $this->securitySessions()->active();
    }

    public function latestSecuritySession(): HasOne
    {
        return $this->hasOne(UserSecuritySession::class)->latestOfMany('last_activity_at');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(UserStatus::class, 'status_id');
    }

    public function staffProfile(): HasOne
    {
        return $this->hasOne(StaffProfile::class);
    }

    public function branchAccessRecords(): HasMany
    {
        return $this->hasMany(UserBranchAccess::class);
    }

    public function accessibleBranches(): BelongsToMany
    {
        return $this->belongsToMany(Branch::class, 'user_branch_access')
            ->withPivot(['access_level', 'created_by_id', 'updated_by_id'])
            ->withTimestamps();
    }

    public function scopeActiveForLogin(Builder $query): Builder
    {
        return $query
            ->where(fn (Builder $query): Builder => $query
                ->where('is_active', true)
                ->orWhereNull('is_active'))
            ->whereNull('security_locked_at')
            ->when($this->statusColumnsAvailable(), fn (Builder $query): Builder => $query
                ->whereDoesntHave('status', fn (Builder $query): Builder => $query
                    ->where('is_blocked', true)
                    ->orWhere('is_archived', true)));
    }

    public function isLockedOut(): bool
    {
        return $this->is_active === false
            || $this->security_locked_at !== null
            || $this->isBlocked()
            || $this->isArchived();
    }

    public function hasBranchAccess(Branch|int|null $branch): bool
    {
        if ($branch === null) {
            return true;
        }

        $branchId = $branch instanceof Branch ? $branch->getKey() : $branch;

        if ($this->relationLoaded('accessibleBranches')) {
            return $this->accessibleBranches->contains('id', $branchId);
        }

        return $this->accessibleBranches()->whereKey($branchId)->exists();
    }

    public function isSuperadmin(): bool
    {
        if ($this->relationLoaded('roles')) {
            return $this->roles->contains('slug', 'superadmin');
        }

        return $this->roles()->where('slug', 'superadmin')->exists();
    }

    public function isBlocked(): bool
    {
        if ($this->relationLoaded('status')) {
            return (bool) $this->status?->is_blocked;
        }

        if (blank($this->status_id) || ! $this->statusColumnsAvailable()) {
            return false;
        }

        return $this->status()->where('is_blocked', true)->exists();
    }

    public function isArchived(): bool
    {
        if ($this->relationLoaded('status')) {
            return (bool) $this->status?->is_archived;
        }

        if (blank($this->status_id) || ! $this->statusColumnsAvailable()) {
            return false;
        }

        return $this->status()->where('is_archived', true)->exists();
    }

    public function getDisplayNameAttribute(): string
    {
        if ($this->relationLoaded('staffProfile') && $this->staffProfile !== null) {
            $profileName = $this->staffProfile->getTranslation('display_name');

            if (filled($profileName)) {
                return (string) $profileName;
            }
        }

        return (string) $this->name;
    }

    private function statusColumnsAvailable(): bool
    {
        try {
            return Schema::hasTable('user_statuses')
                && Schema::hasColumn($this->getTable(), 'status_id');
        } catch (Throwable) {
            return false;
        }
    }
}
