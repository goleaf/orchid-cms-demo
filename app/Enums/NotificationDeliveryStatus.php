<?php

namespace App\Enums;

use App\Enums\Concerns\HasEnumValues;

enum NotificationDeliveryStatus: string
{
    use HasEnumValues;

    case Pending = 'pending';
    case Queued = 'queued';
    case Sent = 'sent';
    case Delivered = 'delivered';
    case Failed = 'failed';
    case Skipped = 'skipped';

    public function label(): string
    {
        return tkey('notifications.deliveries.statuses.'.$this->value);
    }
}
