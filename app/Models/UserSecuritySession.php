<?php

namespace App\Models;

use Database\Factories\UserSecuritySessionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class UserSecuritySession extends Model
{
    /** @use HasFactory<UserSecuritySessionFactory> */
    use HasFactory;

    protected $fillable = [
        'uuid',
        'user_id',
        'session_id_hash',
        'guard',
        'ip_address',
        'user_agent',
        'device_name',
        'browser_name',
        'platform_name',
        'country',
        'city',
        'logged_in_at',
        'last_activity_at',
        'logged_out_at',
        'revoked_at',
        'revoked_by_id',
        'is_current',
        'metadata',
    ];

    protected $hidden = [
        'session_id_hash',
    ];

    protected $casts = [
        'logged_in_at' => 'datetime',
        'last_activity_at' => 'datetime',
        'logged_out_at' => 'datetime',
        'revoked_at' => 'datetime',
        'is_current' => 'boolean',
        'metadata' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $session): void {
            if (blank($session->uuid)) {
                $session->uuid = (string) Str::uuid();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function revokedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revoked_by_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('logged_out_at')->whereNull('revoked_at');
    }

    public function scopeRevoked(Builder $query): Builder
    {
        return $query->whereNotNull('revoked_at');
    }

    public function scopeLoggedOut(Builder $query): Builder
    {
        return $query->whereNotNull('logged_out_at');
    }

    public function scopeCurrent(Builder $query): Builder
    {
        return $query->where('is_current', true);
    }

    public function scopeByUser(Builder $query, User|int|null $user): Builder
    {
        $userId = $user instanceof User ? $user->getKey() : $user;

        return filled($userId) ? $query->where('user_id', $userId) : $query;
    }

    public function scopeByGuard(Builder $query, ?string $guard): Builder
    {
        return filled($guard) ? $query->where('guard', $guard) : $query;
    }

    public function scopeByIpAddress(Builder $query, ?string $ipAddress): Builder
    {
        return filled($ipAddress) ? $query->where('ip_address', $ipAddress) : $query;
    }

    public function scopeRecentlyActive(Builder $query, int $minutes = 30): Builder
    {
        return $query->where('last_activity_at', '>=', now()->subMinutes($minutes));
    }

    public function scopeLatestFirst(Builder $query): Builder
    {
        return $query->orderByDesc('last_activity_at')->orderByDesc('logged_in_at')->orderByDesc('id');
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->logged_out_at === null && $this->revoked_at === null;
    }

    public function getIsRevokedAttribute(): bool
    {
        return $this->revoked_at !== null;
    }

    public function getIsLoggedOutAttribute(): bool
    {
        return $this->logged_out_at !== null;
    }

    public function getDisplayDeviceAttribute(): string
    {
        return collect([$this->device_name, $this->browser_name, $this->platform_name])
            ->filter()
            ->implode(' / ') ?: tkey('security.sessions.empty.unknown_device');
    }

    public function getDisplayLocationAttribute(): string
    {
        return collect([$this->city, $this->country])
            ->filter()
            ->implode(', ') ?: (string) ($this->ip_address ?: tkey('security.sessions.empty.unknown_location'));
    }

    public function getCanBeRevokedAttribute(): bool
    {
        return $this->is_active;
    }
}
