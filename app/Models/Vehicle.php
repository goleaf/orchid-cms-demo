<?php

namespace App\Models;

use App\Enums\VehicleStatus;
use Database\Factories\VehicleFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    /** @use HasFactory<VehicleFactory> */
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'instructor_id',
        'photo_path',
        'registration_number',
        'make',
        'model',
        'year',
        'license_category',
        'transmission',
        'odometer_km',
        'status',
        'availability_summary',
        'description',
        'features',
        'next_service_at',
        'next_inspection_at',
    ];

    protected $casts = [
        'year' => 'integer',
        'odometer_km' => 'integer',
        'features' => 'array',
        'status' => VehicleStatus::class,
        'next_service_at' => 'date',
        'next_inspection_at' => 'date',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class);
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(DrivingLesson::class);
    }

    public function examSessions(): HasMany
    {
        return $this->hasMany(ExamSession::class);
    }

    public function label(): string
    {
        return "{$this->make} {$this->model} {$this->registration_number}";
    }

    public function scopeForFleetList(Builder $query): Builder
    {
        return $query->select([
            'id',
            'branch_id',
            'instructor_id',
            'photo_path',
            'registration_number',
            'make',
            'model',
            'year',
            'license_category',
            'transmission',
            'odometer_km',
            'status',
            'availability_summary',
            'description',
            'features',
            'next_service_at',
            'next_inspection_at',
        ]);
    }
}
