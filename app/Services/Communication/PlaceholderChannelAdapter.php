<?php

namespace App\Services\Communication;

use App\Models\NotificationDeliveryLog;

class PlaceholderChannelAdapter
{
    public function markAsPlaceholder(NotificationDeliveryLog $log, ?string $note = null): NotificationDeliveryLog
    {
        $log->forceFill([
            'status' => NotificationDeliveryLog::STATUS_SKIPPED,
            'provider' => $log->provider ?: 'placeholder',
            'provider_status' => 'placeholder',
            'error_message' => $note ?: tkey('communication.delivery_logs.placeholder_note'),
            'failed_at' => null,
        ])->save();

        return $log->refresh();
    }
}
