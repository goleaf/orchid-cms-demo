<?php

namespace App\Models;

use App\Enums\AnalyticsRunStatus;
use App\Enums\ReportExportFormat;
use Database\Factories\ReportExportFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ReportExport extends Model
{
    /** @use HasFactory<ReportExportFactory> */
    use HasFactory;

    protected $fillable = [
        'uuid',
        'report_definition_id',
        'report_run_id',
        'format',
        'status',
        'file_name',
        'disk',
        'path',
        'row_count',
        'filters',
        'exported_at',
        'expires_at',
        'error_message',
        'created_by_id',
    ];

    protected $casts = [
        'format' => ReportExportFormat::class,
        'status' => AnalyticsRunStatus::class,
        'row_count' => 'integer',
        'filters' => 'array',
        'exported_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $export): void {
            if (blank($export->uuid)) {
                $export->uuid = (string) Str::uuid();
            }
        });
    }

    public function definition(): BelongsTo
    {
        return $this->belongsTo(ReportDefinition::class, 'report_definition_id');
    }

    public function run(): BelongsTo
    {
        return $this->belongsTo(ReportRun::class, 'report_run_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }
}
