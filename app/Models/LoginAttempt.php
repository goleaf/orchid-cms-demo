<?php

namespace App\Models;

use Database\Factories\LoginAttemptFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoginAttempt extends Model
{
    /** @use HasFactory<LoginAttemptFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'identifier_hash',
        'successful',
        'ip_address',
        'user_agent_hash',
        'failure_reason',
        'occurred_at',
    ];

    protected $casts = [
        'successful' => 'boolean',
        'occurred_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $attempt): void {
            if (blank($attempt->occurred_at)) {
                $attempt->occurred_at = now();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
