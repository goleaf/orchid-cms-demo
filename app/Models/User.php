<?php

namespace App\Models;

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
}
