<?php

namespace App\Models;

use App\Enums\ArticleStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KnowledgeArticle extends Model
{
    /** @use HasFactory<\Database\Factories\KnowledgeArticleFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'category',
        'excerpt',
        'body',
        'status',
        'published_at',
        'seo_title',
        'meta_description',
        'canonical_url',
        'open_graph_image',
        'structured_data',
    ];

    protected $casts = [
        'status' => ArticleStatus::class,
        'published_at' => 'datetime',
        'structured_data' => 'array',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', ArticleStatus::Published->value)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function scopeForPublicList(Builder $query): Builder
    {
        return $query->select([
            'id',
            'title',
            'slug',
            'category',
            'excerpt',
            'published_at',
            'seo_title',
            'meta_description',
        ]);
    }

    public function scopeForPublicDetail(Builder $query): Builder
    {
        return $query->select([
            'id',
            'title',
            'slug',
            'category',
            'excerpt',
            'body',
            'published_at',
            'seo_title',
            'meta_description',
            'canonical_url',
            'open_graph_image',
            'structured_data',
        ]);
    }
}
