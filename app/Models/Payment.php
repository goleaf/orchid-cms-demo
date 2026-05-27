<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use Database\Factories\PaymentFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    /** @use HasFactory<PaymentFactory> */
    use HasFactory;

    protected $fillable = [
        'student_profile_id',
        'enrollment_id',
        'amount_cents',
        'currency',
        'method',
        'status',
        'paid_at',
        'reference',
        'notes',
    ];

    protected $casts = [
        'amount_cents' => 'integer',
        'status' => PaymentStatus::class,
        'paid_at' => 'datetime',
    ];

    public function studentProfile(): BelongsTo
    {
        return $this->belongsTo(StudentProfile::class);
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function amountForHumans(): string
    {
        return number_format($this->amount_cents / 100, 2).' '.$this->currency;
    }

    public function scopePaid(Builder $query): Builder
    {
        return $query->where('status', PaymentStatus::Paid->value);
    }

    public function scopeForPaymentList(Builder $query): Builder
    {
        return $query->select([
            'id',
            'student_profile_id',
            'enrollment_id',
            'amount_cents',
            'currency',
            'method',
            'status',
            'paid_at',
            'reference',
        ]);
    }
}
