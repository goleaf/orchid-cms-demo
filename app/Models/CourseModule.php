<?php

namespace App\Models;

use Database\Factories\CourseModuleFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseModule extends Model
{
    /** @use HasFactory<CourseModuleFactory> */
    use HasFactory;

    protected $fillable = [
        'training_program_id',
        'title',
        'module_type',
        'sort_order',
        'duration_minutes',
        'is_required',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'duration_minutes' => 'integer',
        'is_required' => 'boolean',
    ];

    public function trainingProgram(): BelongsTo
    {
        return $this->belongsTo(TrainingProgram::class);
    }

    public function scopeForProgramOutline(Builder $query): Builder
    {
        return $query->select([
            'id',
            'training_program_id',
            'title',
            'module_type',
            'sort_order',
            'duration_minutes',
            'is_required',
        ])->orderBy('sort_order');
    }
}
