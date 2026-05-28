<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Orchid\Filters\Types\Like;
use Orchid\Filters\Types\Where;
use Orchid\Filters\Types\WhereDateStartEnd;
use Orchid\Platform\Models\User as Authenticatable;

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
        'preferred_locale',
        'is_active',
        'security_locked_at',
        'security_lock_reason',
        'password_changed_at',
        'two_factor_placeholder_enabled',
    ];

    protected $attributes = [
        'is_active' => true,
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
        'password_changed_at' => 'datetime',
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
        return $this->hasMany(UserSession::class);
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
            ->whereNull('security_locked_at');
    }

    public function isLockedOut(): bool
    {
        return $this->is_active === false || $this->security_locked_at !== null;
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
}
