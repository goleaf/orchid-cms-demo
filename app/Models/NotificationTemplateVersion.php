<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Database\Factories\NotificationTemplateVersionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationTemplateVersion extends Model
{
    /** @use HasFactory<NotificationTemplateVersionFactory> */
    use HasFactory;

    use HasTranslations;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_PUBLISHED = 'published';

    public const STATUS_ARCHIVED = 'archived';

    protected $fillable = [
        'template_id',
        'version',
        'status',
        'subject_translations',
        'body_translations',
        'variables_schema',
        'published_at',
        'published_by_id',
    ];

    protected $casts = [
        'version' => 'integer',
        'subject_translations' => 'array',
        'body_translations' => 'array',
        'variables_schema' => 'array',
        'published_at' => 'datetime',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(NotificationTemplate::class, 'template_id');
    }

    public function publishedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by_id');
    }

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    public function scopeArchived(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ARCHIVED);
    }

    public function subject(?string $locale = null): ?string
    {
        return $this->getTranslation('subject', $locale);
    }

    public function body(?string $locale = null): ?string
    {
        return $this->getTranslation('body', $locale);
    }
}
