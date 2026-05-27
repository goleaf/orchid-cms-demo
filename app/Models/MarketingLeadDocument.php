<?php

namespace App\Models;

use Database\Factories\MarketingLeadDocumentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketingLeadDocument extends Model
{
    /** @use HasFactory<MarketingLeadDocumentFactory> */
    use HasFactory;

    protected $fillable = [
        'marketing_lead_id',
        'original_name',
        'path',
        'mime_type',
        'size',
    ];

    protected $casts = [
        'size' => 'integer',
    ];

    public function marketingLead(): BelongsTo
    {
        return $this->belongsTo(MarketingLead::class);
    }
}
