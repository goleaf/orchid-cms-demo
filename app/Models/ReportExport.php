<?php

namespace App\Models;

use App\Enums\AnalyticsRunStatus;
use App\Enums\ReportExportFormat;
use Database\Factories\ReportExportFactory;
use Illuminate\Database\Eloquent\Builder;
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
        'filename',
        'mime_type',
        'size_bytes',
        'row_count',
        'filters',
        'exported_at',
        'expires_at',
        'error_message',
        'created_by_id',
        'exported_by_id',
        'metadata',
    ];

    protected $casts = [
        'format' => ReportExportFormat::class,
        'status' => AnalyticsRunStatus::class,
        'size_bytes' => 'integer',
        'row_count' => 'integer',
        'filters' => 'array',
        'exported_at' => 'datetime',
        'expires_at' => 'datetime',
        'metadata' => 'array',
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

    public function exportedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'exported_by_id');
    }

    public function displayFilename(): ?string
    {
        return $this->filename ?: $this->file_name ?: $this->path;
    }

    public function sizeForHumans(): ?string
    {
        if ($this->size_bytes === null) {
            return null;
        }

        if ($this->size_bytes < 1024) {
            return $this->size_bytes.' B';
        }

        if ($this->size_bytes < 1048576) {
            return round($this->size_bytes / 1024, 1).' KB';
        }

        return round($this->size_bytes / 1048576, 1).' MB';
    }

    public function scopeForRun(Builder $query, ReportRun|int $run): Builder
    {
        $runId = $run instanceof ReportRun ? $run->getKey() : $run;

        return $query->where('report_run_id', $runId);
    }

    public function scopeFormat(Builder $query, ReportExportFormat|string $format): Builder
    {
        $value = $format instanceof ReportExportFormat ? $format->value : $format;

        return $query->where('format', $value);
    }

    public function scopeRecent(Builder $query): Builder
    {
        return $query->orderByDesc('exported_at')->orderByDesc('id');
    }
}
