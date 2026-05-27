<?php

namespace App\Models;

use Database\Factories\SiteSettingFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    /** @use HasFactory<SiteSettingFactory> */
    use HasFactory;

    protected $fillable = [
        'key',
        'group',
        'value',
        'description',
        'is_public',
    ];

    protected $casts = [
        'value' => 'array',
        'is_public' => 'boolean',
    ];

    public function scopePublic(Builder $query): Builder
    {
        return $query->where('is_public', true);
    }
}
