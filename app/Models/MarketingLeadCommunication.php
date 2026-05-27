<?php

namespace App\Models;

use Database\Factories\MarketingLeadCommunicationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketingLeadCommunication extends Model
{
    /** @use HasFactory<MarketingLeadCommunicationFactory> */
    use HasFactory;

    private const CALL_RESULTS = [
        'answered',
        'no_answer',
        'wrong_number',
        'callback_requested',
        'thinking',
        'ready_to_pay',
        'lost',
    ];

    protected $fillable = [
        'marketing_lead_id',
        'user_id',
        'marketing_message_template_id',
        'channel',
        'direction',
        'subject',
        'body',
        'communicated_at',
        'client_replied_at',
        'callback_required_at',
        'call_recording_url',
        'call_recording_reference',
        'call_result',
        'duration_seconds',
        'metadata',
    ];

    protected $casts = [
        'communicated_at' => 'datetime',
        'client_replied_at' => 'datetime',
        'callback_required_at' => 'datetime',
        'metadata' => 'array',
        'duration_seconds' => 'integer',
    ];

    public function marketingLead(): BelongsTo
    {
        return $this->belongsTo(MarketingLead::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function messageTemplate(): BelongsTo
    {
        return $this->belongsTo(MarketingMessageTemplate::class, 'marketing_message_template_id');
    }

    public function needsCallback(): bool
    {
        return $this->callback_required_at !== null;
    }

    public function hasClientReply(): bool
    {
        return $this->client_replied_at !== null;
    }

    /**
     * @return array<int, string>
     */
    public static function callResultValues(): array
    {
        return self::CALL_RESULTS;
    }

    /**
     * @return array<string, string>
     */
    public static function callResultOptions(): array
    {
        return collect(self::CALL_RESULTS)
            ->mapWithKeys(fn (string $result): array => [
                $result => tkey('crm.communications.call_results.'.$result),
            ])
            ->all();
    }
}
