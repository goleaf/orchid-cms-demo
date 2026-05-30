<?php

namespace App\Models;

use Database\Factories\LoginAttemptFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class LoginAttempt extends Model
{
    /** @use HasFactory<LoginAttemptFactory> */
    use HasFactory;

    protected $fillable = [
        'uuid',
        'user_id',
        'email',
        'guard',
        'identifier_hash',
        'successful',
        'ip_address',
        'user_agent',
        'user_agent_hash',
        'failure_reason',
        'attempted_at',
        'occurred_at',
        'metadata',
    ];

    protected $casts = [
        'successful' => 'boolean',
        'attempted_at' => 'datetime',
        'occurred_at' => 'datetime',
        'metadata' => 'array',
    ];

    public const FAILURE_INVALID_CREDENTIALS = 'invalid_credentials';

    public const FAILURE_USER_BLOCKED = 'user_blocked';

    public const FAILURE_USER_INACTIVE = 'user_inactive';

    public const FAILURE_USER_ARCHIVED = 'user_archived';

    public const FAILURE_TOO_MANY_ATTEMPTS = 'too_many_attempts';

    public const FAILURE_PASSWORD_EXPIRED = 'password_expired';

    public const FAILURE_MUST_CHANGE_PASSWORD = 'must_change_password';

    public const FAILURE_UNKNOWN = 'unknown';

    public const FAILURE_REASONS = [
        self::FAILURE_INVALID_CREDENTIALS,
        self::FAILURE_USER_BLOCKED,
        self::FAILURE_USER_INACTIVE,
        self::FAILURE_USER_ARCHIVED,
        self::FAILURE_TOO_MANY_ATTEMPTS,
        self::FAILURE_PASSWORD_EXPIRED,
        self::FAILURE_MUST_CHANGE_PASSWORD,
        self::FAILURE_UNKNOWN,
    ];

    protected static function booted(): void
    {
        static::creating(function (self $attempt): void {
            if (blank($attempt->uuid)) {
                $attempt->uuid = (string) Str::uuid();
            }

            if (blank($attempt->attempted_at)) {
                $attempt->attempted_at = now();
            }

            if (blank($attempt->occurred_at)) {
                $attempt->occurred_at = $attempt->attempted_at ?? now();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeSuccessful(Builder $query): Builder
    {
        return $query->where('successful', true);
    }

    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('successful', false);
    }

    public function scopeByUser(Builder $query, User|int|null $user): Builder
    {
        $userId = $user instanceof User ? $user->getKey() : $user;

        return filled($userId) ? $query->where('user_id', $userId) : $query;
    }

    public function scopeByEmail(Builder $query, ?string $email): Builder
    {
        return filled($email) ? $query->where('email', Str::lower(trim($email))) : $query;
    }

    public function scopeByIpAddress(Builder $query, ?string $ipAddress): Builder
    {
        return filled($ipAddress) ? $query->where('ip_address', $ipAddress) : $query;
    }

    public function scopeByGuard(Builder $query, ?string $guard): Builder
    {
        return filled($guard) ? $query->where('guard', $guard) : $query;
    }

    public function scopeBetweenDates(Builder $query, mixed $from = null, mixed $to = null): Builder
    {
        return $query
            ->when($from, fn (Builder $query): Builder => $query->where('attempted_at', '>=', $from))
            ->when($to, fn (Builder $query): Builder => $query->where('attempted_at', '<=', $to));
    }

    public function scopeLatestFirst(Builder $query): Builder
    {
        return $query->orderByDesc('attempted_at')->orderByDesc('id');
    }

    public function getDisplayFailureReasonAttribute(): string
    {
        if (blank($this->failure_reason)) {
            return '';
        }

        return tkey('security.login_attempts.failure_reasons.'.$this->failure_reason);
    }

    public function getIsSuccessfulAttribute(): bool
    {
        return (bool) $this->successful;
    }

    public function getIsFailedAttribute(): bool
    {
        return ! $this->is_successful;
    }
}
